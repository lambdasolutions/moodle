function initialisevideo(obj) {
    YUI().use("swf","node","io","json-parse","event", function(Y) {
        var player = Y.one(obj.playerselector);
        if (player == undefined) {
            return false;
        }
        player.setStyles({width:400,height:290});
        if (player.hasChildNodes()) {
            player.one('*').remove(true);
        }

        var datastr = '';
        datastr += 'actions=playerurl';
        if (obj.entryid != undefined && obj.entryid != '') {
            datastr += '&entryid='+obj.entryid;
        }
        else if (window.kaltura.entryid != 0 && window.kaltura.entryid != undefined) {
            datastr += '&entryid='+window.kaltura.entryid;
        }
        else if (window.kaltura.cmid != 0 && window.kaltura.cmid != undefined) {
            datastr += '&id='+window.kaltura.cmid;
        }
        else {
            return false;
        }
        if (obj.videotype != undefined) {
            datastr += '&videotype='+obj.videotype;
        }

        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: datastr,
                on: {
                    complete: function(i, o, a) {
                        var data = Y.JSON.parse(o.responseText);
                        var kaltura_player = new Y.SWF(obj.playerselector, data.playerurl.url,
                            {
                                fixedAttributes: {
                                    wmode: "opaque",
                                    allowScriptAccess: "always",
                                    allowFullScreen: true,
                                    allowNetworking: "all"
                                },
                                flashVars: {
                                    externalInterfaceDisabled: 0,
                                    gotoEditorWindow: "gotoEditorWindow"
                                }
                            }
                        );
                    }
                }
            }
        );
    });
}

YUI.use('node','event', function(Y) {
    Y.on("domready",function() { initialisevideo({playerselector:'.kalturaPlayer', videotype: KalturaEntryType_Media}); });
});
