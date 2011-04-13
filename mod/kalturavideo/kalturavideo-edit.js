YUI().use("node","io","json-parse","event", function(Y) {
    replaceVideoButton('input#id_buttons_replacevideo',KalturaEntryType_Media);
    replaceVideoButton('input#id_buttons_replaceeditvideo',KalturaEntryType_Mix);

    Y.on("domready",function() {
                                initialisevideo({playerselector:'.kalturaPlayerEdit',
                                videotype: Y.one('input[name=videotype]').get('value')});
    });
});

function replaceVideoButton(buttonselector, videotype) {
    YUI().use('node','io','json-parse','overlay','swf', function(Y) {

        var replace_button = Y.one(buttonselector);
        if (replace_button != undefined) {
            replace_button.on('click',function(e) {
                e.preventDefault();
                var div = Y.Node.create('<div class="kalturaContributionWizard">'
                                            +'<div class="yui3-widget-hd"></div>'
                                            +'<div class="yui3-widget-bd"></div>'
                                            +'<div class="yui3-widget-ft"></div>'
                                        +'</div>');
                Y.one('body').appendChild(div);

                Y.one(".kalturaContributionWizard .yui3-widget-bd").setStyles({width:800, height: 600});

                Y.one("input[name=videotype]").set('value',videotype);

                var datastr = '';
                datastr += 'actions=cwurl&videotype='+videotype;

                Y.io(M.cfg.wwwroot+'/mod/kalturavideo/ajax.php',
                    {
                        data: datastr,
                        on: {
                            complete: function(i,o,a) {
                            var response = Y.JSON.parse(o.responseText);
                                var swf = new Y.SWF(".kalturaContributionWizard .yui3-widget-bd", response.cwurl.url,
                                        {  version: "9.0.115",
                                       fixedAttributes: {wmode: "opaque",
                                                     allowScriptAccess:"always",
                                                         allowNetworking:"all",
                                                         allowFullScreen: "TRUE"},
                                       flashVars: {
                                                    partnerId: response.cwurl.params.partnerid,
                                                    userId: response.cwurl.params.userid,
                                                    sessionId: response.cwurl.params.sessionid,
                                                    uiConfId: response.cwurl.params.uiId,
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
    });
}

function onContributionWizardAfterAddEntry(param) {
    YUI.use('node','io','json-parse', function(Y) {
        var videoType = Y.one('input[name=videotype]').get('value');

        if (videoType == KalturaEntryType_Media) {
            var entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
            Y.one('input[name=kalturaentry]').set('value',entryId);
            initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId, videotype: videoType});
        }
        else if (videoType == KalturaEntryType_Mix) {
            var entries = "";

            for (i = 0; i < param.length; i++) {
                entryId = (param[i].uniqueID == null ? param[i].entryId: param[i].uniqueID);
                entries += entryId;
                if (i != param.length-1) {
                    entries+=",";
                }
            }

            Y.io(M.cfg.wwwroot + "/mod/kalturavideo/ajax.php", {
                data: 'actions=mixaddentries&mixentries='+entries,
                on: {
                    complete: function(id, o, args) {
                        try {
                            var response = Y.JSON.parse(o.responseText);
                            var id = response.mixaddentries.entryid;
                            Y.one('input[name=kalturaentry]').set('value',id);
                            initialisevideo({
                                entryid: id,
                                videotype: videoType
                            })
                        } catch(ex) {}
                    }
                }
            });
        }
    });
}

function onContributionWizardClose(modified) {
    YUI().use('node', function(Y) {
        Y.one('.kalturaContributionWizard').setStyles({display: 'none'});
        Y.one('.kalturaContributionWizard').remove(true);
    });
}

function gotoEditorWindow(param) {
    YUI().use('node','io','swf','overlay','json-parse', function(Y) {
        if (param != undefined && param != '') {
            Y.io(M.cfg.wwwroot+'/mod/kalturavideo/ajax.php', {
                data: 'actions=editorurl&entryid='+param,
                on: {
                    complete: function(i,o,a) {
                        try {
                            var response = Y.JSON.parse(o.responseText);
                            var div = Y.Node.create('<div class="kalturaEditor">'
                                                        +'<div class="yui3-widget-hd"></div>'
                                                        +'<div class="yui3-widget-bd"></div>'
                                                        +'<div class="yui3-widget-ft"></div>'
                                                    +'</div>'
                            );
                            Y.one('body').appendChild(div);
                            Y.one('.kalturaEditor').setStyles({'z-index': 10});
                            Y.one('.kalturaEditor .yui3-widget-bd').setStyles({width:900, height:600});

                            var swf = new Y.SWF('.kalturaEditor .yui3-widget-bd', response.editorurl.url,
                                {
                                    fixedAttributes: {
                                        wmode: "opaque",
                                        allowScriptAccess: "always",
                                        allowFullScreen: true,
                                        allowNetworking: "all"
                                    },
                                    flashVars: response.editorurl.params
                                }
                            );
                            var overlay = new Y.Overlay({
                                srcNode: '.kalturaEditor',
                                centered: true
                            });

                            overlay.render(document.body);
                        }
                        catch(ex) {}
                    }
                 }
             });
        }
    });
}

function onSimpleEditorSaveClick() {
    YUI.use('node', function(Y) {
        Y.one('.kalturaEditor').setStyles({display: 'none'});
        Y.one('.kalturaEditor').remove(true);
    });
}

function onSimpleEditorBackClick(param) {
    onSimpleEditorSaveClick();
}
