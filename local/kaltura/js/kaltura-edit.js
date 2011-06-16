/**
 * Kaltura editing JavaScript functions for Kaltura Plugins for Moodle 2
 * @source: http://github.com/bwilkins/local-kaltura
 *
 * @licstart
 * Copyright (C) 2011 Catalyst IT Ltd (http://catalyst.net.nz)
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU Affero
 * General Public License (GNU Affero GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero GPL for more details.
 *
 * As additional permission under GNU Affero GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU Affero GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

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
                overlayHTML();
                return false;
            });
        }
    });
}

function overlayHTML() {
    YUI().use('node','overlay', 'tabview', function(Y) {
        var tabview = Y.Node.create('<div id="overlayContainer">'
                                        +'<div id="kalturahtmlcontrib">'
                                            +'<ul>'
                                                +'<li><a href="#videotab">Video</a></li>'
                                                +'<li><a href="#audiotab">Audio</a></li>'
                                            +'</ul>'
                                            +'<div>'
                                                +'<div id="videotab">Video Content</div>'
                                                +'<div id="audiotab">Audio Content</div>'
                                            +'</div>'
                                        +'</div>'
                                        +'<input type="submit" value="Close" id="contribClose"/>'
                                    +'</div>');
        Y.one('body').appendChild(tabview);
        var videotab = Y.Node.create('<div id="videotabview">'
                                        +'<ul>'
                                            +'<li><a href="#uploadvideotab">Upload from File</a></li>'
                                            +'<li><a href="#webcamtab">Record from Webcam</a></li>'
                                            +'<li><a href="#myvideo">My Video</a></li>'
                                            +'<li><a href="#sharedvideo">Shared Video</a></li>'
                                        +'</ul>'
                                        +'<div>'
                                            +'<div id="uploadvideotab">Upload from File</div>'
                                            +'<div id="webcamtab">Record from Webcam</div>'
                                            +'<div id="myvideo">My Video</div>'
                                            +'<div id="sharedvideo">Shared Video</div>'
                                        +'</div>'
                                    +'</div>');
        Y.one('#videotab').setContent(videotab);
        var audiotab = Y.Node.create('<div id="audiotabview">'
                                        +'<ul>'
                                            +'<li><a href="#uploadaudiotab">Upload from File</a></li>'
                                            +'<li><a href="#mictab">Record from Mic</a></li>'
                                            +'<li><a href="#myaudio">My Audio</a></li>'
                                            +'<li><a href="#sharedaudio">Shared Audio</a></li>'
                                        +'</ul>'
                                        +'<div>'
                                            +'<div id="uploadaudiotab">Upload from File</div>'
                                            +'<div id="mictab">Record from Mic</div>'
                                            +'<div id="myaudio">My Audio</div>'
                                            +'<div id="sharedaudio">Shared Audio</div>'
                                        +'</div>'
                                    +'</div>');
        Y.one('#audiotab').setContent(audiotab);

        var tabs = new Y.TabView({srcNode:'#kalturahtmlcontrib'});
        var vid  = new Y.TabView({srcNode:'#videotabview'});
        var aud  = new Y.TabView({srcNode:'#audiotabview'});

        Y.one('#overlayContainer').setStyles({background: '#FFFFFF',border: '1px'});
        Y.one('#overlayContainer > div > div > div > div > div > div').setStyles({width: 450, height: 300});

        Y.one('#contribClose').on('click', function(e) {
            e.preventDefault();
            Y.one('#overlayContainer').setStyles({display: 'none'});
            Y.one('#overlayContainer').remove(true);

            return false;
        });

        var overlay = new Y.Overlay({
            srcNode:'#overlayContainer',
            centered: true
        });
        tabs.render();
        aud.render();
        vid.render();
        overlay.render(document.body);

        ajaxSwfLoad({
            datastr: 'action=audiourl',
            target:  '#mictab'
        });
        ajaxSwfLoad({
            datastr: 'action=videourl',
            target:  '#webcamtab'
        });
    });
}

function ajaxSwfLoad(conf) {
    YUI().use('io','json-parse','swf', function(Y) {
        if (!conf || !conf.datastr || !conf.target) {
            return false;
        }
        if (!conf.fixedAttributes) {
            conf.fixedAttributes = {
                allowScriptAccess:"always",
                allowNetworking:"all",
                allowFullScreen: "TRUE"
            }
        }
        Y.io(M.cfg.wwwroot+'/local/kaltura/ajax.php',
            {
                data: conf.datastr,
                on: {
                    complete: function (i,o,a) {
                        response = Y.JSON.parse(o.responseText);
                        var swf = new Y.SWF(conf.target, response.url,
                        {
                            version: "9.0.124",
                            fixedAttributes: {
                                allowScriptAccess:"always",
                                allowNetworking:"all",
                                allowFullScreen: "TRUE"
                            },
                            flashVars: response.params
                        });
                    }
                }
            }
        );
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

function addEntryComplete(entry) {
    YUI().use('node', function(Y) {
        var entryId = entry.entryId;
        Y.one('input[name=kalturavideo]').set('value',entryId);
        initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
    });
}

function addEntryResult(entry) {
    addEntryComplete(entry);
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
