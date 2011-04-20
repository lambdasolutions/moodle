pptIdHolder = document.getElementById("id_ppt_input");
pptThumbHolder = document.getElementById("thumb_doc_holder");
videoIdHolder = document.getElementById("id_video_input");
videoThumbHolder = document.getElementById("thumb_video_holder");
pptDnldUrlHolder = document.getElementById("id_ppt_dnld_url");

function get_uploader() {
    YUI().use('node', function(Y) {
        var id = Y.one('.kalturaDocUploader *:first-child').get('id');
        return document.getElementById(id);
    }
}

function check_status() {
    YUI().use('node','io','json-parse',function(Y) {
        //TODO: functionality of uploading()
        var dnldUrl = Y.one('#dnldHolder').get('value');
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: 'actions=docCheckStatus&downloadUrl='+encodeURIComponent(dnldUrl),
                on: {
                    complete: function(i, o, a) {
                        response = Y.JSON.parse();
                        if (response.docCheckStatus.status == true) {
                            //
                            //trigger have_ppt - this should also trigger check for video selected etc
                        }
                    }
                }
            }
        );
    });
}

function create_swfdoc() {
    YUI().use('node','io','json-parse','overlay',function(Y) {
        var entryid = Y.one('input[name=kalturaentry]').get('value');
        if (doc != '') {
            Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
                {
                    data: 'actions=swfdocurl&entryid='+entryid+'&id='+window.kaltura.cmid,
                    on: {
                        complete: function(i, o, a) {
                            response = Y.JSON.parse(o.responseText);
                            try {
                                var div = Y.Node.create('<div class="kalturaSyncPoints">'
                                                            +'<div class="yui3-widget-hd"></div>'
                                                            +'<div class="yui3-widget-bd"></div>'
                                                            +'<div class="yui3-widget-ft"></div>'
                                                        +'</div>'
                                );
                                Y.one('body').appendChild(div);
                                Y.one('div.kalturaSyncPoints .yui3-widget-bd').setStyles({width:780, height: 400});

                                var swf = new Y.SWF('.kalturaSyncPoints .yui3-widget-bd', response.swfdocurl.url,
                                    {
                                                version: "9.0.115",
                                                fixedAttributes: {
                                                    wmode: "opaque",
                                                    allowScriptAccess: "always",
                                                    allowFullScreen: true,
                                                    allowNetworking: "all"
                                                },
                                                flashVars: response.swfdocurl.params
                                    }
                                );

                                var overlay = new Y.Overlay({
                                    srcNode: '.kalturaSyncPoints',
                                    centered: true
                                });
                            } catch(ex) {}
                        }
                    }
                }
            );
        }
        else {
            var pptid = Y.one('#pptid').get('value');
            var videoid = Y.one('#videoid').get('value');
            Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
                {
                    data: 'actions=createswfdoc&pptid='+pptid+'&videoid='+videoid;
                    on: {
                        complete: function(i, o, a) {
                            response = Y.JSON.parse(o.responseText);
                            try {
                                Y.one('input[name=kalturaentry]').set('value', response.createswfdoc.entryid);
                                create_swfdoc();
                            } catch(ex) {}
                        }
                    }
                }
            )
        }
    });
}

function user_selected() {
    get_uploader().upload();
}

function uploaded() {
    get_uploader().addEntries();
}

function uploading() {
    YUI().use('node', function(Y) {
        if (Y.one('#docThumbHolder').hasChildNodes() == false) {
            var img = Y.Node.create('<img src="'+M.cfg.wwwroot+'/local/kaltura/images/ajax-loader.gif"/>');
            Y.one('#docThumbHolder').appendChild(img);
        }
    });
}

function entries_added(obj) {
    YUI().use('node','io','json-parse', function(Y) {
        Y.one("#docThumbHolder").innerHTML = txt_document;
        myobj = obj[0];
        Y.one("#id_ppt_input").set('value', myobj.entryId);

        Y.io(M.cfg.wwwroot+"/local/kaltura/ajax.php",
            {
                data: "actions=convertppt&entryid=" + myobj.entryId,
                on: {
                    complete: function(i, o, a) {
                        response = Y.JSON.parse(o.responseText);
                        if(response.convertppt.url != '') {
                            pptDnldUrlHolder.value = response.convertppt.url;
                        }
                    }
                }
            }
        );
        get_uploader().removeFiles(0,0); //Does this work in YUI?
    });
}

delegate = {
    selectHandler: user_selected,
    progressHandler: uploading,
    allUploadsCompleteHandler: uploaded,
    entriesAddedHandler: entries_added
};

YUI().use('node','io','json-parse','event', function(Y) {
    Y.on('domready', function() {
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: 'actions=swfdocuploader&id='+window.kaltura.cmid,
                on: {
                    complete: function(i, o, a) {
                        response = Y.JSON.parse(o.responseText);
                        try {
                            var swf = new Y.SWF('.kalturaDocUploader', M.cfg.wwwroot+'/kupload/ui_conf_id/1002613',
                                {
                                    version: "9.0.115",
                                    fixedAttributes: {
                                        wmode: "opaque",
                                        allowScriptAccess: "always",
                                        allowFullScreen: true,
                                        allowNetworking: "all"
                                    },
                                        flashVars: {
                                        ks: response.swfdocuploader.ks,
                                        uid: response.swfdocuploader.userid,
                                        partnerId: response.swfdocuploader.partnerid,
                                        subPId: response.swfdocuploader.partnerid*100,
                                        entryId: -1,
                                        maxUploads: 10,
                                        maxFileSize: 128,
                                        maxTotalSize: 200,
                                        uiConfId: 1002613,
                                        jsDelegate: delegate
                                    }
                                }
                            );
                        } catch(ex) {}
                    }
                }
            }
        );
        if (Y.one('input[name=kalturavideo]').get('value') != ''
            && Y.one('input[name=kalturadocument]').get('value') != '') {
            Y.one("#sync_btn").set('disabled', false);
        }
    });
});

function save_sync() {
    create_swfdoc();
    document.getElementById("btn_uploaddoc").disabled = true;
    document.getElementById("btn_selectvideo").disabled = true;
    document.getElementById("divKalturaKupload").innerHTML = "";
}
