/* This file is part of the Kaltura Collaborative Media Suite which allows users to do with audio, video, and animation what Wiki
platfroms allow them to do with text.

Copyright (C) 2006-2008 Kaltura Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see
<http://www.gnu.org/licenses/>.

*/

// initModalBox called from gotoCW - to open the contribution wizard as an iFrame in the // widget page var PWIDTH = 320; var PHEIGHT =
210;
var tmp_id = 0;
function kalturaInitModalBox(url, options) {
    var starta = new Date();
    //create timestamped unique id for every time
    the iframe opens tmp_id = starta.getTime();
    // timestamp to id if (document.getElementById("overlay")) { overlay_obj =
    document.getElementById("overlay");
    modalbox_obj = document.getElementById("modalbox");
    overlay_obj.parentNode.removeChild(overlay_obj);
    modalbox_obj.parentNode.removeChild(modalbox_obj);
}
var objBody = document.getElementsByTagName("body").item(0);

// create overlay div and hardcode some functional styles (aesthetic styles are in CSS file) var objOverlay =
document.createElement("div");
objOverlay.setAttribute('id', 'overlay');
objBody.appendChild(objOverlay, objBody.firstChild);

var width = 680;
var height = 360;
if (options) {
    if (options.width) width = options.width;
    //+10; if (options.height) height =
    options.height;
    //+16; }
    // create modalbox div, same note about styles as above var objModalbox = document.createElement("div"); objModalbox.setAttribute('id',
    'modalbox');
    //objModalbox.setAttribute('style',
    'width:' + width + 'px;height:' + height + 'px;margin-top:' + (0 - height / 2) + 'px;margin-left:' + (0 - width / 2) + 'px;');
    objModalbox.style.width = width +
    'px';
    objModalbox.style.height = height + 'px';
    objModalbox.style.marginTop = (0 - height / 2) + 'px';
    objModalbox.style.marginLeft = (0
    - width / 2) + 'px';

    // create content div inside objModalbox var objModalboxContent = document.createElement("div"); objModalboxContent.setAttribute('id',
    'mbContent');
    if (url != null) {
        thehtml = '<iframe allowtransparency="true" name="' + tmp_id + '" id="kaltura_modal_iframe"
scrolling="no" width="' + width + '" height="' + height + '" frameborder="0" src="' + url + '">';
        objModalboxContent.innerHTML = thehtml;
    }
    objModalbox.appendChild(objModalboxContent, objModalbox.firstChild);

    objBody.appendChild(objModalbox, objOverlay.nextSibling);
    return '';

    return objModalboxContent;
}

function kalturaCloseModalBox() {
    /* if ( this != window.top ) { window.top.kalturaCloseModalBox(); return false; } */
    //alert (
    "kalturaCloseModalBox");
    // TODO - have some JS to close the modalBox without refreshing the page if there is no need overlay_obj =
    document.getElementById("overlay");modalbox_obj = document.getElementById("modalbox");
    if (overlay_obj)
    overlay_obj.parentNode.removeChild(overlay_obj);
    if (modalbox_obj) modalbox_obj.parentNode.removeChild(modalbox_obj);

    return false;
}

function kalturaRefreshTop() {
    /* if ( this != window.top ) { window.top.kalturaRefreshTop(); return false; } */
    window.location.reload(true);
}

function get_field(field_name) {
    if (this != window.top) {
        return window.top.get_field(field_name);
    }
    return
    document.getElementById(field_name).value;
}

function update_field(field_name, value, close_modal, presspost) {
    if (this != window.top) {
        window.top.update_field(field_name, value,
        close_modal, presspost);
        return false;
    }
    document.getElementById(field_name).value = value;
    // if (presspost != '')
    window.top.document.getElementById(presspost).click();
    if (presspost != '') eval("window.top." + presspost + "();");
    if (close_modal)
    window.top.kalturaCloseModalBox();
}

function update_img(field_name, value, close_modal, presspost) {
    if (this != window.top) {
        window.top.update_img(field_name, value,
        close_modal, presspost);
        return false;
    }
    document.getElementById(field_name).src = value;
    // if (presspost != '')
    window.top.document.getElementById(presspost).click();
    if (presspost != '') eval("window.top." + presspost + "();");
    if (close_modal)
    window.top.kalturaCloseModalBox();
}

function make_preview(thumb_div, field) {
    field_elem = document.getElementById(field);
    if (field_elem) {
        if
        (!document.getElementById('kfield_preview')) {
            new_div = document.createElement('div');
            new_div.setAttribute('id', 'kfield_preview');
            field_elem.parentNode.insertBefore(new_div, field_elem);
        }
        document.getElementById('kfield_preview').innerHTML = '';
        wrap_div =
        document.createElement('div');
        wrap_div.className = 'kthumb';
        img_elem = document.createElement('img');
        img_elem.src = thumb_div;
        wrap_div.appendChild(img_elem);

        document.getElementById('kfield_preview').appendChild(wrap_div);

    }
}

function insert_into_post() {
    var design = document.getElementById('slctDesign');

    update_field('id_design', design.options[design.selectedIndex].value, false, '');
    update_field('id_custom_width',
    document.getElementById('inpCustomWidth').value, false, '');
    update_field('id_title', document.getElementById('inpTitle').value, true,
    'show_wait');
}

function clear_field(field_elem, preview_div) {
    document.getElementById(field_elem).value = '';
    document.getElementById(preview_div).innerHTML = '';
}

function scrollright(divid, amount) {
    scroller = document.getElementById(divid);
    if (scroller) {
        scroller.scrollLeft += amount;
    }
}

function scrollleft(divid, amount) {
    scroller = document.getElementById(divid);
    if (scroller) {
        if (amount) scroller.scrollLeft -=
        amount;
        else scroller.scrollLeft = 0;
    }
}

var current_item = '';

function load_item_to_view(entry_id, url, type) {
    switch_item_in_player(entry_id, type);
    if (current_item != '') {
        document.getElementById(current_item).className = 'kobj';
    }
    document.getElementById(entry_id).className = 'kobj active';
    current_item =
    entry_id;
    switch_data(url);
}
var img_src = '';
function set_img_src(str) {
    img_src = str;
}
function switch_item_in_player(entry_id,
type) {
    YUI().use('node', 'io',
    function(Y) {
        var static_library_img = Y.one('#static_library_img') var static_library_player =
        Y.one('#static_library_player') if (!type || type == 2) {
            var kplayer = Y.one('#kplayer')

            kplayer.hide(true)

            var thumb_url = ''
            var uri = wwwroot + '/kaltura/kthumb_url.php?id=' + entry_id + '&type=1&height=364&width=410'
            function success(i, o,
            a) {
                if (o.isOk == true) {
                    set_img_src(result)
                }
            }
            Y.on("io.success", success, Y, true) Y.io(uri, {
                sync = true
            })

            if (!static_library_img) {
                var div = Y.one('#static_library_player_div') var img = div.create('<img>') img.set('id',
                'static_library_img') div.appendChild(img) static_library_img = img
            }

            static_library_img.set('src', img_src)
        } else {
            static_library_player.show(true) kdp = new
            KalturaPlayerController('static_library_player') kdp.insertEntry(entry_id) static_library_img.hide(true)
        }
    })
}

function switch_data(url, entry) {
    YUI().use('node', 'io',
    function(Y) {
        var kitem_metadata = Y.one('#kitem_metadata') if (!entry) {
            kitem_metadata.load(url)
        } else {
            data = 'entry_id=' + entry data += '&title=' + kitem_metadata.one(':input[name=title]').get('value')
            data += '&desciption=' + kitem_metadata.one(':input[name=description]').get('value') data += '&tags=' +
            kitem_metadata.one(':input[name=tags]').get('value')
            //data += '&'+kitem_metadata.one(':input[name=entry_id]').get('value') data +=
            '&field=' + kitem_metadata.one(':input[name=field]').get('value') data += '&clone=' +
            kitem_metadata.one(':input[name=clone]').get('value') data += '&skippreview=' +
            kitem_metadata.one(':input[name=skippreview]').get('value') data += '&fullinject=' +
            kitem_metadata.one(':input[name=fullinject]').get('value') data += '&presspost=' +
            kitem_metadata.one(':input[name=presspost]').get('value')

            var cfg = {
                data: data,
                on: {
                    complete: function(i, o, a) {
                        if (o && o.responseText) {
                            kitem_metadata.setContent(o.responseText)
                        }
                    }
                }
            }
            Y.io(url, cfg)
        }
    })
}

function update_other_elements(entry_id, entry_title, type) {
    //$("#"+entry_id+" span[@class=title]").html(entry_title);
    //setTimeout('switch_item_in_player("'+entry_id+'", '+type+')',100); }
    function fix_window_size(options) {
        if (this != window.top) {
            window.top.fix_window_size(options);
            return false;
        }
        var width = 680;
        var
        height = 360;
        if (options) {
            if (options.width) width = options.width;
            if (options.height) height = options.height;
        }
        YUI().use('node',
        function(Y) {
            Y.one('#kaltura_modal_iframe').setStyles({
                width: width,
                height: height
            })

            objModalbox = Y.one('#modalbox') if (objModalbox) {
                objModalbox.setStyles({
                    width: width,
                    height: height,
                })
                objModalbox.setStyle('margin-top', (0 - height / 2) + 'px') objModalbox.setStyle('margin-left', (0 - width / 2) + 'px')
            }
        })
    }

    function delete_entry(entry_id, delete_url) {
        var r = confirm("Are you sure you want to delete this item ?");
        if (r == true) {
            YUI().use('io',
            function(Y) {
                Y.io(delete_url + "?entry_id=" + entry_id, {
                    sync: true
                });
                kalturaRefreshTop();
            })
        }
    }

    function test(id) {
        kdp = new KalturaPlayerController('kaltura-static-player');
        kdp.getMediaSeekTime();
        kdp.getPlayheadTime();
    }

    function openLightBox(entryId, objId) {
        YUI().use('node',
        function(Y) {
            if (this == window.top) {
                //if($.browser.msie) kdps_ie =
                Y.NodeList.getDOMNodes(Y.all('div.kaltura_wrapper').all('object'));
                //else kdps_ff =
                Y.NodeList.getDOMNodes(Y.all('div.kaltura_wrapper').all('embed'));
                for (i = 0; i < kdps_ie.length; i++) {
                    kdp_id = kdps_ie[i].id;
                    kdp =
                    new KalturaPlayerController(kdp_id);
                    kdp.pause();
                }
                for (i = 0; i < kdps_ff.length; i++) {
                    kdp_id = kdps_ff[i].id;
                    kdp = new
                    KalturaPlayerController(kdp_id);
                    kdp.pause();
                }
                if (objId != 'null') {
                    kdp_id = objId;
                } else {
                    kdp_id = 'kplayer_' + entryId;
                    if
                    (!document.getElementById(kdp_id)) {
                        // assume only one player in page (like portfolio) kdp_id = 'kplayer'; } } time = ''; if
                        (document.getElementById(kdp_id)) {
                            kdp = new KalturaPlayerController(kdp_id);
                            try {
                                time = kdp.getMediaSeekTime();
                            } catch(err) {
                                // do
                                nothing
                            }

                        }
                        url = wwwroot + "/kaltura/kdp.php?entry=" + entryId + "&seekto=" + time;
                        kalturaInitModalBox(url, {
                            width: 824,
                            height: 630
                        });
                    }
                })
            }

            function toggle_wall_pic_size(pic_id, img_id) {
                myimg = document.getElementById(img_id);
                orig_source = myimg.src;
                if
                (orig_source.indexOf('width/120') > 0) {
                    new_src = orig_source.replace('width/120', 'width/' + PWIDTH);
                    new_src =
                    new_src.replace('height/65', 'height/' + PHEIGHT);
                    document.getElementById(pic_id).className = 'wall_photo_minus';
                } else if
                (orig_source.indexOf('width/' + PWIDTH) > 0) {
                    new_src = orig_source.replace('width/' + PWIDTH, 'width/120');
                    new_src =
                    new_src.replace('height/' + PHEIGHT, 'height/65');
                    document.getElementById(pic_id).className = 'wall_photo_plus';
                }
                myimg.src = new_src;
            }

            function assignment_remove_video(div_id, input_id, key, detach_link) {
                YUI().use('node', 'io',
                function(Y) {
                    Y.one('#' +
                    div_id).hide(true);
                    Y.one('#' + input_id).set('value', '');
                    Y.io(detach_link + "?entry=" + div_id + "&key=" + key)
                })
            }
            function
            remove_kaltura_video(div_id, key, detach_link) {
                YUI().use('node', 'io',
                function(Y) {
                    Y.one('#' + div_id).hide(true);
                    Y.one('#replace_link_' + div_id).setContent('Add Image');
                    Y.one('#remove_link_' + div_id).setContent('');
                    Y.io(detach_link + "&entry=" +
                    div_id + "&key=" + key)
                })
            }

            var vid_div_id = '';
            function get_vid_div_id() {
                return vid_div_id;
            }
            function replace_resource_video(tag, vid_id) {
                YUI().use('node',
                'io',
                function(Y) {
                    vid_div_id = "replace_" + vid_id;
                    var action = 'video';
                    var url = wwwroot +
                    "/kaltura/html_resource_ajax/replace_tags.php";
                    Y.io(url + "?action=" + action + "&tag=" + escape(tag), {
                        on: {
                            success: function(i, o,
                            a) {
                                var divid = get_vid_div_id();
                                Y.one("#" + divid).setContent(o.responseText)
                            }
                        }
                    })
                })
            }

            var swf_replacement_div_id = '';
            function get_swf_replacement_div_id() {
                return swf_replacement_div_id;
            }
            function
            replace_resource_swf(tag, div_id) {
                YUI().use('node', 'io',
                function(Y) {
                    var action = 'swf';
                    swf_replacement_div_id = div_id;
                    var url =
                    wwwroot + '/kaltura/html_resource_ajax/replace_tags.php';
                    Y.io(url + "?action=" + action + "&tag=" + escape(tag), {
                        on: {
                            success:
                            function(i, o, a) {
                                var divid = get_swf_replacement_div_id() Y.one("#" + divid).setContent(o.responseText)
                            }
                        }
                    })
                })
            }