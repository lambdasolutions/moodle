        YUI().use("swf","node", "overlay", function(Y) {
            var wiz = Y.one(".kalturaContributionWizard");
            if (wiz != undefined) {
            wiz.setStyles({width:800, height: 600, display: 'none'});
            Y.one('input[name='+window.kaltura.buttonname+']').on('click',function(e) {
                e.preventDefault();
                Y.one(".kalturaContributionWizard .yui3-widget-bd").setStyles({width:800, height: 600});
                var swf = new Y.SWF(".kalturaContributionWizard .yui3-widget-bd", window.kaltura.CWurl,
                                {  version: "9.0.115",
                                   fixedAttributes: {wmode: "opaque",
                                                     allowScriptAccess:"always",
                                                     allowNetworking:"all",
                                                     allowFullScreen: "TRUE"},
                                   flashVars: {partnerId: window.kaltura.partnerid,
                                               userId: window.kaltura.userid,
                                               sessionId: window.kaltura.sessionid,
                                               uiConfId: window.kaltura.uiId,
                                               afterAddEntry: "onContributionWizardAfterAddEntry",
                                               close: "onContributionWizardClose",
                                               kShowId: -2,
                                               terms_of_use: "http://corp.kaltura.com/tandc"
                                   }
                                });
                var overlay = new Y.Overlay({
                    srcNode: ".kalturaContributionWizard",
                    centered: true
                });
                Y.one('.kalturaContributionWizard').setStyles({display: 'block'});
                overlay.set("centered", true);
                overlay.render(document.body);
                return false;
            });
            }
         });

        function onContributionWizardAfterAddEntry(param) {
            var entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
            YUI().use('node', function(Y) {
                Y.one('input[name='+window.kaltura.inputname+']').set('value',entryId);
                initialisevideo("");
            });
        }

        function onContributionWizardClose(modified) {
                YUI().use('node', function(Y){
                    Y.one('.kalturaContributionWizard').setStyles({display: 'none'});
                });
        }

    function initialisevideo(entry_id) {
        YUI().use('swf', 'node',function(Y){
            Y.one('.kalturaPlayer').setStyles({width:400,height:290});

            var url = window.kaltura.playerurl;

            if (entry_id === '' || entry_id === undefined) {
                var fragment = Y.one('input[name='+window.kaltura.inputname+']').get('value');
                if (fragment !== '' && fragment !== undefined) {
                    url += fragment;
                }
            } else {
                url += entry_id;
            }

            var kaltura_player = new Y.SWF('.kalturaPlayer', url, {
                fixedAttributes: {
                    wmode: "opaque",
                    allowScriptAccess: "always",
                    allowFullScreen: true,
                    allowNetworking: "all"
                },
                flashVars: {
                    externalInterfaceDisabled: 0
                }
            });

        });
    }
