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

YUI().use('node', 'io', 'json-parse', 'event', function (Y) {
    replaceVideoButton('input#id_replacevideo');

    Y.on("domready", function () {
        obj = {};
        obj.playerselector = '.kalturaPlayerEdit';
        obj.entryid = Y.one('input[name=kalturavideo]').get('value');
        initialisevideo(obj);
    });
});

function replaceButton(buttonselector, overlayclass, datastr) {
    YUI().use('node', function (Y) {
        var replace_button = Y.one(buttonselector);
        if (replace_button) {
            replace_button.on('click',function (e) {
                e.preventDefault();
                if (window.kalturaWiz == undefined) {
                    window.kalturaWiz = contribWiz();
                }
                return false;
            });
        }
    });
}

function replaceVideoButton(buttonselector) {
    YUI().use('node', function (Y) {
        replaceButton(buttonselector, 'ContributionWizard', 'action=cwurl');
    });
}

function addEntryComplete(entry) {
    alert('entry: '+entry.entryId);
    YUI().use('node', function (Y) {
        var entryId = entry.entryId;
        Y.one('input[name=kalturavideo]').set('value',entryId);
        initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
    });
}

(function (a, b) {
    var document  = window.document,
        navigator = window.navigator,
        location  = window.location;

    YUI().use('node', 'io', 'event', 'json-parse', 'overlay', 'tabview', 'swf', 'yui2-treeview', function (Y) {
        var contribWiz = (function () {
            /* Define a few things... */

            var contribWiz = function () {
                return contribWiz.fn.init();
            },
            /* Nice shot var for holding the ajax url variable */
            _ajaxurl = M.cfg.wwwroot + '/local/kaltura/ajax.php',
            yui = YUI(),
            scaffold = '',

            load_scaffold = function (self) {
                Y.io(_ajaxurl,
                    {
                        data: 'actions[0]=getdomnodes',
                        on: {
                            success: function (i, o, a) {
                                scaffold = Y.JSON.parse(o.responseText)[0];
                            },
                            failure: function (i, o, a) {
                                setTimeout(function () {self(self);}, 1000);
                            }
                        },
                    }
                );
            };

            load_scaffold(load_scaffold);

            contribWiz.fn = contribWiz.prototype = {
                constructor: contribWiz,
                init: function () {
                    this.interfaceNodes = scaffold;

                    if (scaffold === '') {
                        setTimeout(function () {window.kalturaWiz._buildRootInterface()}, 1000);
                    }
                    else {
                        this._buildRootInterface();
                    }

                    return this;

                },
                _buildRootInterface: function () {
                    if (window.kalturaWiz !== undefined) {
                        $this = window.kalturaWiz;
                    }
                    else {
                        $this = this;
                    }

                    if (scaffold === '') {
                        setTimeout($this._buildRootInterface, 1000);
                    } else {
                        $this.interfaceNodes = scaffold;
                        console.debug(scaffold);

                        var node = Y.Node.create($this.interfaceNodes.root);
                        Y.one(document.body).appendChild(node);
                        node = Y.Node.create($this.interfaceNodes.rootstyles);
                        Y.one('head').appendChild(node);
                        $this.domnode = Y.one('#overlayContainer');

                        Y.one('#contribClose').on('click', function (e) {
                            e.preventDefault();
                            $this._destroyInterface();

                            return false;
                        });

                        setTimeout(function() {$this._buildSelectionInterface(Y.one('#kalturahtmlcontrib'));}, 200);
                    }
                },
                _buildSelectionInterface: function (root) {
                    /* Assign this to another value so it is still valid when defining another object */
                    var $this = this;

                    try {
                        if (this.currentnode) {
                            this.currentnode.destroy();
                        }
                    }
                    catch (err) {};

                    /* Fetch and insert dom tree */

                    var node = Y.Node.create($this.interfaceNodes.selectstyles);
                    Y.one(document.head).append(node);

                    node = Y.Node.create(this.interfaceNodes.select);
                    root.append(node);
                    this.currentnode = Y.one('#selectionInterface');

                    /* YUIfy DOM sections */
                    var renderables         = {};
                    renderables.toptabs     = new Y.TabView({srcNode:'#selectionInterface'});
                    renderables.videotabs   = new Y.TabView({srcNode:'#videotabview'});
                    renderables.audiotabs   = new Y.TabView({srcNode:'#audiotabview'});

                    renderables.overlay = new Y.Overlay({
                        srcNode:'#overlayContainer',
                        centered: true
                    });

                    /* Render YUI parts */
                    renderables.toptabs.render();
                    renderables.videotabs.render();
                    renderables.audiotabs.render();
                    renderables.overlay.render();

                    pages = Array(
                        {
                            target: '#sharedaudio',
                            type  : 'audio',
                            access: 'public'
                        },
                        {
                            target: '#myaudio',
                            type  : 'audio',
                            access: 'private'
                        },
                        {
                            target: '#sharedvideo',
                            type  : 'video',
                            access: 'public'
                        },
                        {
                            target: '#myvideo',
                            type  : 'video',
                            access: 'private'
                        }
                    );
                    for (var i = 0; i < pages.length; i++) {
                        var ob = pages[i];
                        $this.pageButtonHandlers({
                            action   : 'list' + ob.access,
                            target   : ob.target,
                            type     : ob.type,
                            page     : window.kalturaWiz.interfaceNodes.selectdata[ob.type + 'list' + ob.access].page.current,
                            pagecount: window.kalturaWiz.interfaceNodes.selectdata[ob.type + 'list' + ob.access].page.count
                        });
                    }


                    /* Load dynamic contents */
                    /* Load webcam recorder */
                    this._swfLoadCallback({
                        passthrough: {
                            target: '#webcamtab',
                        },
                        response: $this.interfaceNodes.selectdata.videourl
                    });
                    /* Load mic recorder */
                    this._swfLoadCallback({
                        passthrough: {
                            target: '#mictab',
                        },
                        response: $this.interfaceNodes.selectdata.audiourl
                    });
                },
                _buildEditInterface: function (upload) {
                    var $this = this;

                    if (this.upload) {
                        this.currentnode.addClass('hidden');
                        this.previousnode = this.currentnode;
                    }
                    else {
                        this.currentnode.get('parentNode').remove(true);
                    }

                    var node = this.interfaceNodes.edit;
                    Y.one('#kalturahtmlcontrib').append(node);

                    var node = Y.Node.create($this.interfaceNodes.editstyles);
                    Y.one('head').append(node);
                    this.currentnode = Y.one('#editInterface');

                    try {
                        if (!this.entryid) {
                            this.entryid = '';
                        }
                    }
                    catch (err) {
                        this.entryid = '';
                    }

                    this.multiJAX([
                        {
                            action: 'geteditdata',
                            passthrough: {
                                entryid: $this.entryid,
                                upload : $this.upload,
                            },
                            params: {
                                entryid: $this.entryid,
                                upload : $this.upload,
                            },
                            successCallback: $this._populateEditCallback
                        }
                    ]);

                    this.tree = new Y.YUI2.widget.TreeView('editcategoriestreeview');
                    this.tree.subscribe('clickEvent', function (e) {
                        console.log(e.node);
                        var textbox = Y.one('#editcategoriestext');
                        var idlist  = Y.one('#editcategoriesids');
                        var categoriestext = textbox.get('value');
                        var categoriesids  = idlist.get('value');
                        var sep = '';
                        if (categoriestext != '') {
                            sep = ', ';
                        }
                        if (categoriesids != '') {
                            sep = ',';
                            if (categoriesids.indexOf(e.node.data.catid) > -1) {
                                return;
                            }
                        }
                        categoriestext += sep + e.node.fullName;
                        categoriesids  += sep + e.node.id;
                        textbox.set('value', categoriestext);
                        idlist.set('value', categoriesids);
                    });
                    this.tree.render();
                },
                _destroyInterface: function () {
                    this.domnode.setStyles({display: 'none'});
                    this.domnode.remove(true);
                    delete(window.kalturaWiz);
                },
                _swfLoadCallback: function (ob) {
                    var swf = new Y.SWF(ob.passthrough.target, ob.response.url,
                        {
                            version: "9.0.124",
                            fixedAttributes: {
                                wmode: ob.response.wmode,
                                allowScriptAccess:"always",
                                allowNetworking:"all",
                                allowFullScreen: "TRUE"
                            },
                            flashVars: ob.response.params
                        }
                    );
                },
                _uploadUrlCallback: function (ob) {
                    /*ob.response.params.delegate = ob.pasthrough.delegate;*/
                    /*this._swfLoadCallback(ob);*/
                },
                _populateEditCallback: function (ob) {
                    Y.one('#edittitle').set('value', ob.response.entry.name);
                    Y.one('#editdescription').set('value', ob.response.entry.description);
                    if (Y.one('#contribkalturathumb').get('src') == M.cfg.wwwroot + '/local/kaltura/images/ajax-loader.gif') {
                        Y.one('#contribkalturathumb').set('src', ob.response.entry.thumbnailUrl);
                    }
                    if (ob.response.entry.categoriesIds != undefined) {
                        Y.one('#editcategoriesids').set('value', ob.response.entry.categoriesIds);

                    }
                    if (ob.response.entry.categories != undefined) {
                        Y.one('#editcategoriestext').set('value', ob.response.entry.categories);
                    }
                    if (ob.response.entry.tags != '') {
                        Y.one('#edittags').set('value', ob.response.entry.tags);
                    }
                    if (ob.upload == false) {
                        Y.one('#edittitle').set('disabled');
                    }
                    if (ob.response.entry.description != '') {
                        Y.one('#editdescription').set('disabled');
                    }
                    console.log(ob.response);
                },
                _mediaListCallback: function (ob) {
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
                                        +'<a href="#" onClick="contribWiz.fn.selectedEntry({entryId: \'' + n.id + '\', upload: false});return false;" class="kalturavideo" status="' + n.status + '" id="' + n.id + '">'
                                            + (
                                                ob.passthrough.type === 'audio' ?
                                                    '<span><div style="float:left;height:90px;width:120px">' + n.name + '</div></span>' :
                                                    '<img src="' + n.thumbnailUrl + '" type="image/jpeg" width="120px" height="90px" alt="' + n.name + '"/>'
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

                    window.kalturaWiz.pageButtonHandlers({
                        action   : ob.passthrough.action,
                        target   : ob.passthrough.target,
                        type     : ob.passthrough.type,
                        page     : ob.passthrough.page,
                        pagecount: ob.response.page.count
                    });
                },
                pageButtonHandlers: function (ob) {
                    var $this   = this,
                    back        = Y.one(ob.target+' .pageb'),
                    forward     = Y.one(ob.target+' .pagef');

                    if (ob.page <= 1) {
                        back.setAttribute('disabled', true);
                    }
                    else {
                        back.setAttribute('disabled', false);
                        back.on(
                            {
                                click: function (e) {
                                    e.preventDefault();

                                    $this.multiJAX([{
                                        action: ob.action,
                                        passthrough: {
                                            target: ob.target,
                                            action: ob.action,
                                            type:   ob.type,
                                            page:   ob.page-1
                                        },
                                        params: {
                                            mediatype: ob.type,
                                            page: ob.page-1
                                        },
                                        successCallback: window.kalturaWiz._mediaListCallback
                                    }]);

                                    return false;
                                }
                            }
                        );
                    }

                    if (ob.page >= ob.pagecount) {
                        forward.setAttribute('disabled', true);
                    }
                    else {
                        forward.setAttribute('disabled', false);
                        forward.on(
                            {
                                click: function (e) {
                                    e.preventDefault();

                                    $this.multiJAX([{
                                        action: ob.action,
                                        passthrough: {
                                            target: ob.target,
                                            action: ob.action,
                                            type:   ob.type,
                                            page:   ob.page-1
                                        },
                                        params: {
                                            mediatype: ob.type,
                                            page: ob.page+1
                                        },
                                        successCallback: window.kalturaWiz._mediaListCallback
                                    }]);

                                    return false;
                                }
                            }
                        );
                    }
                },
                multiJAX: function (conf) {
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
                            success : c.successCallback,
                            failure : (c.failureCallback ? c.failureCallback : function () {})
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
                                success: function (i, o, a) {
                                    response = Y.JSON.parse(o.responseText);
                                    for (var j = 0; j < response.length; j++) {
                                        callbacks[j].success({
                                            response: response[j],
                                            passthrough: passthroughs[j]
                                        });
                                    }
                                },
                                failure: function (i, o, a) {
                                    response = Y.JSON.parse(o.responseText);
                                    for (var j = 0; j < response.length; j++) {
                                        callbacks[j].failure({
                                            response: response[j],
                                            passthrough: passthroughs[j]
                                        });
                                    }
                                }
                            }
                        }
                    );
                },
                addEntryComplete: function (entry) {
                    var entryId = entry.entryId;
                    Y.one('input[name=kalturavideo]').set('value', entryId);
                    initialisevideo({playerselector: '.kalturaPlayerEdit', entryid: entryId});
                },
                selectedEntry: function (ob) {
                    this.entryid = ob.entryId;
                    this.upload  = ob.upload;

                    //TODO: separate data fetching, fetch here

                    this._buildEditInterface(ob.upload);
                },
                destroy: function () {
                    window.kalturaWiz._destroyInterface();
                },
            };
            contribWiz.fn.init.prototype = contribWiz.prototype;
            return contribWiz;
        })();
        a.contribWiz = contribWiz;
    });
})(window);

