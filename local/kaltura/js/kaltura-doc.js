function check_status() {
    console.log('check_status called');
    YUI().use('node','io','json-parse',function(Y) {
        if (!Y.one('.kalturaDocThumb').hasChildNodes()) {
            var imgNode = Y.Node.create('<div><img src="'+M.cfg.wwwroot+'/local/kaltura/images/ajax-loader.gif" alt="Loading..."/></div>');
            var linkNode = Y.Node.create('<div><a href="#" id="check_status">Check status</a></div>');
            Y.one('.kalturaDocThumb').appendChild(imgNode);
            Y.one('.kalturaDocThumb').appendChild(linkNode);
            Y.one('.kalturaDocThumb a').on('click', function(e) {
                e.preventDefault();
                check_status();
                return false;
            });
        }
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: 'action=doccheckstatus&docurl='+urlencode(documentswfurl),
                on: {
                    complete: function(i, o, a) {
                        var response = Y.JSON.parse(o.responseText);
                        console.log(response);
                        if (response.status == true) {
                            Y.one('input[name=syncpoints]').set('disabled', false);
                            Y.one('.kalturaDocThumb').setContent('<img src="'+response.thumbnail+'" alt="processing complete"/>');
                        }
                        else {
                            setTimeout("check_status()", 2000);
                        }
                    }
                }
            }
        );
    });
}

YUI().use('event', function(Y) {
    Y.on('domready', function(e) {
        replaceDocumentButton('input#id_replacedocument');
        Y.one('input[name=syncpoints]').on('click', function(f) {
            f.preventDefault();
            alert('this button *IS* being handled correctly-ish.');
            var vid = Y.one('input[name=kalturavideo]').get('value');
            var doc = Y.one('input[name=kalturadocument]').get('value');
            if (doc != '' && vid != '') {
                overlaySWF('SyncPoints','action=swfdocurl&entryid='+vid+'&docurl='+documentswfurl+'&id='+window.kaltura.cmid);
                var closebutton = Y.Node.create('<input type="submit" value="Close" onclick=".preventDefault();YUI().use(\'node\', function(Y){Y.one(\'.kalturaSyncPoints\').remove(true);});return false;"/>')
                Y.one('.kalturaSyncPoints .yui3-widget-ft').appendChild(closebutton);
            }
            return false;
        });
    });
});

function replaceDocumentButton(buttonselector) {
    replaceButton(buttonselector, 'DocumentUploader', 'action=swfdocuploader');
}

var documentswfurl = '';

function onDocumentUploaderAfterAddEntry(param) {
    YUI.use('node', 'io', 'json-parse', function(Y) {
        Y.one('input[name=kalturadocument]').set('value', param[0].entryId);
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: 'action=convertppt&entryid='+param[0].entryId,
                on: {
                    complete: function(i, o, a) {
                        var response = Y.JSON.parse(o.responseText);
                        documentswfurl = response.url;
                    }
                }
            }
        );
    });
}

function onDocumentUploaderClose(modified) {
    YUI().use('node', function(Y) {
        Y.one('.kalturaDocumentUploader').setStyles({display: 'none'});
        Y.one('.kalturaDocumentUploader').remove(true);
        check_inputs();
    });
}

function check_inputs() {
    YUI().use('node', function(Y) {
        var doc = Y.one('input[name=kalturadocument]').get('value');
        var vid = Y.one('input[name=kalturavideo]').get('value');
        if (doc != '' && vid != '') {

            check_status();
        }
    });
}
