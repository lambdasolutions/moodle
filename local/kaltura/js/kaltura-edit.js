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
        if (replace_button) {
            replace_button.on('click',function(e) {
                e.preventDefault();
                window.kalturaWiz = contribWiz();
                return false;
            });
        }
    });
}

function replaceVideoButton(buttonselector) {
    YUI().use('node', function(Y) {
        replaceButton(buttonselector, 'ContributionWizard', 'action=cwurl');
    });
}

function addEntryComplete(entry) {
    alert('entry: '+entry.entryId);
    YUI().use('node', function(Y) {
        var entryId = entry.entryId;
        Y.one('input[name=kalturavideo]').set('value',entryId);
        initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
    });
}

(function (a, b) {
    var document  = window.document,
        navigator = window.navigator,
        location  = window.location;

    YUI().use('node','io','event','json-parse','overlay','tabview','swf', function(Y) {
        var contribWiz = (function() {
            /* Define a few things... */

            var contribWiz = function() {
                return contribWiz.fn.init();
            },
            /* Nice shot var for holding the ajax url variable */
            _ajaxurl = M.cfg.wwwroot + '/local/kaltura/ajax.php',
            yui = YUI(),
            scaffold = '',

            load_scaffold = function(self) {
                Y.io(M.cfg.wwwroot + '/local/kaltura/js/kaltura-edit_scaffold.html',
                    {
                        on: {
                            success: function(i,o,a) {
                                scaffold = o.responseText;
                            },
                            failure: function(i,o,a) {
                                setTimeout(function() {self(self);}, 1000);
                            }
                        },
                    }
                );
            };

            load_scaffold(load_scaffold);

            contribWiz.fn = contribWiz.prototype = {
                constructor: contribWiz,
                init: function(target) {
                    this._buildInterface();
                    this._yuifyInterface();
                    this._renderInterface();

                    this.multiJAX([
                        {
                            action: 'videourl',
                            passthrough: {
                                target: '#webcamtab'
                            },
                            callback: this._swfLoadCallback
                        },
                        {
                            action: 'audiourl',
                            passthrough: {
                                target: '#mictab'
                            },
                            callback: this._swfLoadCallback
                        },
                        {
                            action: 'listpublic',
                            passthrough: {
                                target: '#sharedvideo',
                                action: 'listpublic',
                                type:   'video'
                            },
                            params: {
                                mediatype: 'video'
                            },
                            callback: this._mediaListCallback
                        },
                        {
                            action: 'listprivate',
                            passthrough: {
                                target: '#myvideo',
                                action: 'listprivate',
                                type:   'video'
                            },
                            params: {
                                mediatype: 'video'
                            },
                            callback: this._mediaListCallback
                        },
                        {
                            action: 'listpublic',
                            passthrough: {
                                target: '#sharedaudio',
                                action: 'listpublic',
                                type:   'audio'
                            },
                            params: {
                                mediatype: 'audio'
                            },
                            callback: this._mediaListCallback
                        },
                        {
                            action: 'listprivate',
                            passthrough: {
                                target: '#myaudio',
                                action: 'listprivate',
                                type:   'audio'
                            },
                            params: {
                                mediatype: 'audio'
                            },
                            callback: this._mediaListCallback
                        }
                    ]);

                    return this;
                },
                _yuifyInterface: function() {
                    this.renderables           = {};
                    this.renderables.toptabs   = new Y.TabView({srcNode:'#kalturahtmlcontrib'});
                    this.renderables.videotabs = new Y.TabView({srcNode:'#videotabview'});
                    this.renderables.audiotabs = new Y.TabView({srcNode:'#audiotabview'});

                    Y.one('#overlayContainer').setStyles({background: '#CCC',border: '1px'});
                    Y.all('#overlayContainer > div > div > div > div > div > div').setStyles({width: 450, height: 300});

                    Y.one('#contribClose').on('click', function(e) {
                        e.preventDefault();

                        Y.one('#overlayContainer').setStyles({display: 'none'});
                        Y.one('#overlayContainer').remove(true);

                        return false;
                    });

                    this.renderables.overlay = new Y.Overlay({
                        srcNode:'#overlayContainer',
                        centered: true
                    });
                },
                _renderInterface: function() {
                    this.renderables.toptabs.render();
                    this.renderables.videotabs.render();
                    this.renderables.audiotabs.render();
                    this.renderables.overlay.render();
                },
                _buildInterface: function() {
                    var node = Y.Node.create(scaffold);
                    Y.one(document.body).appendChild(node);
                    this.domnode = Y.one('#overlayContainer');
                },
                _destroyInterface: function() {
                    this.domnode.setStyles({display: 'none'});
                    this.domnode.remove(true);
                },
                multiJAX: function(conf) {
                    var str = '';
                    var callbacks = Array();
                    var passthroughs = Array();
                    for (var i = 0; i < conf.length; i++) {
                        var c = conf[i];
                        var actionstr = 'actions[' + i + ']=' + c.action;
                        var paramstr_head  = 'params[' + i + ']';
                        var paramstr = '';
                        if (c.params) {
                            for (var p in c.params) {
                                var paramstr = paramstr + paramstr_head + '[' + p + ']=' + c.params[p] + '&';
                            }
                        }
                        str += actionstr + '&' + paramstr; /* paramstr should already end with a & */
                        callbacks[i] = {
                            complete: c.callback,
                            failure : c.failure ? c.failure : this._defaultFailureCallback
                        };
                        if (c.passthrough) {
                            passthroughs[i] = c.passthrough;
                        }
                        else {
                            passthroughs[i] = {};
                        }
                    }
                    str = str.replace(/&$/,'');
                    console.log(str);

                    Y.io(_ajaxurl,
                        {
                            data: str,
                            on: {
                                complete: function (i,o,a) {
                                    response = Y.JSON.parse(o.responseText);
                                    console.log(response);
                                    for (var j = 0; j < response.length; j++) {
                                        callbacks[j].complete({
                                            response: response[j],
                                            passthrough: passthroughs[j]
                                        });
                                    }
                                },
                                failure: function (i,o,a) {
                                    /* TODO: Do stuff */
                                }
                            }
                        }
                    );
                },
                _swfLoadCallback: function(ob) {
                    var swf = new Y.SWF(ob.passthrough.target, ob.response.url,
                        {
                            version: "9.0.124",
                            fixedAttributes: {
                                allowScriptAccess:"always",
                                allowNetworking:"all",
                                allowFullScreen: "TRUE"
                            },
                            flashVars: ob.response.params
                        }
                    );
                },
                _mediaListCallback: function(ob) {
                    var $this = this;
                    if (!ob.passthrough.page) {
                        ob.passthrough.page = 1;
                    }

                    if (ob.response) {
                        Y.one(ob.passthrough.target+' .controls').setContent('');
                        Y.one(ob.passthrough.target+' .'+ob.passthrough.type+'container').setContent('');
                    }

                    var node = Y.Node.create('<a href="#" class="pageb">&lt;</a>Page ' + ob.passthrough.page + '<a href="#" class="pagef">&gt;</a>');
                    Y.one(ob.passthrough.target+' .controls').appendChild(node);

                    for (var i = 0; i < ob.response.count; i++) {
                        var n = ob.response.objects[i];
                        if (n) {
                            Y.one(ob.passthrough.target + ' .' + ob.passthrough.type + 'container').appendChild(
                                Y.Node.create(
                                    '<span class="thumb">'
                                        +'<a href="#" onClick="addEntryComplete({entryId: \''+n.id+'\'});return false;" class="kalturavideo" status="'+n.status+'" id="'+n.id+'">'
                                            + (
                                                ob.passthrough.type === 'audio' ?
                                                    '<span><div style="float:left;height:90px;width:120px">' + n.name + '</div></span>' :
                                                    '<img src="'+n.thumbnailUrl+'" type="image/jpeg" width="120px" height="90px" alt="'+n.name+'"/>'
                                                )
                                        +'</a>'
                                    +'</span>'
                                )
                            );
                        }
                    }
                    Y.all(ob.passthrough.target+' .thumb img').setStyles({
                        background: '#000000',
                        /*padding: '0.5em'*/
                    });

                    var back    = Y.one(ob.passthrough.target+' .pageb');
                    var forward = Y.one(ob.passthrough.target+' .pagef');

                    if (ob.passthrough.page <= 1) {
                        back.setAttribute('disabled', true);
                    }
                    else {
                        back.setAttribute('disabled', false);
                        back.on(
                            {
                                click: function (e) {
                                    e.preventDefault();

                                    contribWiz.fn.multiJAX([{
                                        action: ob.passthrough.action,
                                        passthrough: {
                                            target: ob.passthrough.target,
                                            action: ob.passthrough.action,
                                            type:   ob.passthrough.type,
                                            page:   ob.passthrough.page-1
                                        },
                                        params: {
                                            mediatype: ob.passthrough.type,
                                            page: ob.passthrough.page-1
                                        },
                                        callback: $this._mediaListCallback
                                    }]);

                                    return false;
                                }
                            }
                        );
                    }

                    if (ob.passthrough.page >= ob.response.page.count) {
                        forward.setAttribute('disabled', true);
                    }
                    else {
                        forward.setAttribute('disabled', false);
                        forward.on(
                            {
                                click: function (e) {
                                    e.preventDefault();

                                    contribWiz.fn.multiJAX([{
                                        action: ob.passthrough.action,
                                        passthrough: {
                                            target: ob.passthrough.target,
                                            action: ob.passthrough.action,
                                            type:   ob.passthrough.type,
                                            page:   ob.passthrough.page-1
                                        },
                                        params: {
                                            mediatype: ob.passthrough.type,
                                            page: ob.passthrough.page+1
                                        },
                                        callback: $this._mediaListCallback
                                    }]);

                                    return false;
                                }
                            }
                        );
                    }
                },
                addEntryComplete: function(entry) {
                    var entryId = entry.entryId;
                    Y.one('input[name=kalturavideo]').set('value', entryId);
                    initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
                },
                show: function() {
                    if (this.domnode.getStyle('display') !== 'block') {
                        this.domnode.setStyle('display', 'block');
                    }
                },
                destroy: function() {
                    this._destroyInterface();
                },
            };
            contribWiz.fn.init.prototype = contribWiz.prototype;
            return contribWiz;
        })();
        a.contribWiz = contribWiz;
    });
})(window);

