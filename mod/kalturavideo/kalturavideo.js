YUI().use("swf","node", "overlay","io","json-parse","event", function(Y) {
    var wiz = Y.one(".kalturaContributionWizard");
    if (wiz != undefined) {
        wiz.setStyles({width:800, height: 600, display: 'none'});
        Y.one('input[name='+window.kaltura.buttonname+']').on('click',function(e) {
            e.preventDefault();
            Y.one(".kalturaContributionWizard .yui3-widget-bd").setStyles({width:800, height: 600});

            var select = Y.one("select[name="+window.kaltura.mediaselectorname+"]");
            var mix = false;
            if (select != undefined) {
                mix = select.get('value')==2? 1 : 0; // This is as set in KalturaClient.php...
            } else mix =0;
            var datastr = '';
            if (window.kaltura.cmid != 0) {
                datastr += 'id='+window.kaltura.cmid+'&';
            }
            datastr += 'fields=url&uploader=1&mix='+mix;
alert(datastr);
            Y.io(M.cfg.wwwroot+'/mod/kalturavideo/ajax.php',
                {
                    data: datastr,
                    on: {
                        complete: function(i,o,a) {
                        var response = Y.JSON.parse(o.responseText);
                            var swf = new Y.SWF(".kalturaContributionWizard .yui3-widget-bd", response.url,
                                    {  version: "9.0.115",
                                   fixedAttributes: {wmode: "opaque",
                                                 allowScriptAccess:"always",
                                                     allowNetworking:"all",
                                                     allowFullScreen: "TRUE"},
                                   flashVars: {partnerId: response.params.partnerid,
                                               userId: response.params.userid,
                                               sessionId: response.params.sessionid,
                                               uiConfId: response.params.uiId,
                                               afterAddEntry: "onContributionWizardAfterAddEntry",
                                               close: "onContributionWizardClose",
                                               kShowId: -2,
                                               terms_of_use: "http://corp.kaltura.com/tandc"
                                   }
                                }
                            );
                        }
                    }
                }
            );
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

    var player = Y.one('.kalturaPlayer');
    if (player != undefined) {
        Y.on("domready",function() { initialisevideo(''); });
    }
});

function onContributionWizardAfterAddEntry(param) {
    YUI.use('node', function(Y) {
        var entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
        Y.one('input[name='+window.kaltura.inputname+']').set('value',entryId);
        initialisevideo(entryId);
    });
}

function onContributionWizardClose(modified) {
    YUI().use('node', function(Y) {
        Y.one('.kalturaContributionWizard').setStyles({display: 'none'});
    });
}

function initialisevideo(entry_id) {
    YUI().use("swf","node","io","json-parse", function(Y) {
        Y.one('.kalturaPlayer').setStyles({width:400,height:290});

        var datastr = '';
        if (window.kaltura.cmid != 0) {
            datastr += 'id='+window.kaltura.cmid+'&';
        }
        datastr += 'fields=url';
        if (entry_id) {
            datastr += '&entryid='+entry_id;
        }

        Y.io(M.cfg.wwwroot+'/mod/kalturavideo/ajax.php',
            {
                data: datastr,
                on: {
                    complete: function(i, o, a) {
                        var data = Y.JSON.parse(o.responseText);
                        var kaltura_player = new Y.SWF('.kalturaPlayer', data['url'],
                            {
                                fixedAttributes: {
                                    wmode: "opaque",
                                    allowScriptAccess: "always",
                                    allowFullScreen: true,
                                    allowNetworking: "all"
                                },
                                flashVars: {
                                    externalInterfaceDisabled: 0
                                }
                            }
                        );
                    }
                }
            }
        );
    });
}
