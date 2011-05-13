YUI().use("node","io","json-parse","event", function(Y) {
    replaceVideoButton('input#id_replacevideo');

    Y.on("domready",function() {
        obj = {};
        obj.playerselector = '.kalturaPlayerEdit';
        obj.entryid = Y.one('input[name=kalturavideo]').get('value');
        initialisevideo(obj);
    });
});

function replaceButton(buttonselector, overlayclass, datastr) {
    YUI().use('node', function(Y) {
        var replace_button = Y.one(buttonselector);
        if (replace_button != undefined) {
            replace_button.on('click',function(e) {
                e.preventDefault();
                overlaySWF(overlayclass, datastr);
                return false;
            });
        }
    });
}

function overlaySWF(overlayclass, datastr) {
    YUI().use('node','io','json-parse','overlay','swf', function(Y) {
        var div = Y.Node.create('<div class="kaltura'+overlayclass+'">'
                                    +'<div class="yui3-widget-hd"></div>'
                                    +'<div class="yui3-widget-bd"></div>'
                                    +'<div class="yui3-widget-ft"></div>'
                                +'</div>');
        Y.one('body').appendChild(div);

        Y.one(".kaltura"+overlayclass+" .yui3-widget-bd").setStyles({width:800, height: 600});
        console.log(datastr);
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: datastr,
                on: {
                    complete: function(i,o,a) {
                        var response = Y.JSON.parse(o.responseText);
                        console.log(response);
                        response.params.terms_of_use = "http://corp.kaltura.com/tandc";
                        response.params.afterAddEntry = "on"+overlayclass+"AfterAddEntry";
                        response.params.close = "on"+overlayclass+"Close";
                        response.params.kShowId = -2;

                        var swf = new Y.SWF(".kaltura"+overlayclass+" .yui3-widget-bd", response.url,
                            {
                                version: "9.0.115",
                                fixedAttributes: {
                                    wmode: "opaque",
                                    allowScriptAccess:"always",
                                    allowNetworking:"all",
                                    allowFullScreen: "TRUE"
                                },
                                flashVars: response.params
                            }
                        );
                    }
                }
            }
        );
        var overlay = new Y.Overlay({
            srcNode: ".kaltura"+overlayclass,
            centered: true
        });
        Y.one('.kaltura'+overlayclass).setStyles({display: 'block'});
        overlay.set("centered", true);
        overlay.render(document.body);
    });
}

function replaceVideoButton(buttonselector) {
    YUI().use('node', function(Y) {
        replaceButton(buttonselector, 'ContributionWizard', 'action=cwurl');
    });
}

function onContributionWizardAfterAddEntry(param) {
    YUI().use('node','io','json-parse', function(Y) {
        var entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
        Y.one('input[name=kalturavideo]').set('value',entryId);
        initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
    });
}

function onContributionWizardClose(modified) {
    YUI().use('node', function(Y) {
        Y.one('.kalturaContributionWizard').setStyles({display: 'none'});
        Y.one('.kalturaContributionWizard').remove(true);
        if (typeof check_inputs == 'function') {
            check_inputs();
        }
    });
}
