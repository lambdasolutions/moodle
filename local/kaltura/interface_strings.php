<?PHP

function construct_interface($select, $edit) {
    $interfaceNodes = array();

    $interfaceNodes['root'] = <<<ROOT
    <div id="overlayContainer">
        <div id="kalturahtmlcontrib" class="contentArea"></div>
        <input type="submit" value="Close" id="contribClose"/>
    </div>
ROOT;

    $editstr[] = <<<EDIT
    <div id="editInterface" class="contentArea">
        <div class="editprogressdiv">
            Updatable progress bar area
        </div>
        <div class="editmaindiv">
            <span class="editthumbspan">
                Area for thumbnail
            </span>
            <span class="editcontentspan">
                Area for content editing
                <label for="edittitle">Title: </label><input id="edittitle" type="text" /><br />
                <label for="editdescription">Description: </label><input id="editdescription" type="text" /><br/>
                <label for="editcategoriestext">Categories: </label>
                <div id="editcategories">
                    <input id="editcategoriestext" type="text" />
                    <div id="editcategoriestreeview">
EDIT;
    if (!empty($edit->categorylist)) {
        $editstr[] = '<ul>';
        foreach ($edit->categorylist['categories']->objects as $index => $category) {
            $editstr[] = "<li>$category->name</li>";
        }
        $editstr[] = '</ul>';
    }
    $editstr[] = <<<EDIT
                    </div>
                </div>
            </span>
        </div>
    </div>
EDIT;

    $interfaceNodes['edit'] = implode('', $editstr);

    $interfaceNodes['select'] = <<<SELECT
    <div id="selectionInterface" class="contentArea">
        <ul>
            <li><a href="#videotab">Video</a></li>
            <li><a href="#audiotab">Audio</a></li>
        </ul>
        <div>
            <div id="videotab" class="contentArea">
                <div id="videotabview" class="contentArea">
                    <ul>
                        <li><a href="#uploadvideotab">Upload from File</a></li>
                        <li><a href="#webcamtab">Record from Webcam</a></li>
                        <li><a href="#myvideo">My Video</a></li>
                        <li><a href="#sharedvideo">Shared Video</a></li>
                    </ul>
                    <div class="contentArea">
                        <div id="uploadvideotab" class="contentArea">
                            <label for="uploadvideospan">Upload Video</label>
                            <span id="uploadvideospan">
                                <input type="submit" id="uploadvideobutton" value="Upload" />
                            </span>
                        </div>
                        <div id="webcamtab" class="contentArea">
                        </div>
                        <div id="myvideo" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager('video', $select->videolistprivate);
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                        <div id="sharedvideo" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager('video', $select->videolistpublic);
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="audiotab" class="contentArea">
                <div id="audiotabview" class="contentArea">
                    <ul>
                        <li><a href="#uploadaudiotab">Upload from File</a></li>
                        <li><a href="#mictab">Record from Microphone</a></li>
                        <li><a href="#myaudio">My Audio</a></li>
                        <li><a href="#sharedaudio">Shared Audio</a></li>
                    </ul>
                    <div class="contentArea">
                        <div id="uploadaudiotab" class="contentArea">
                            <label for="uploadaudiospan">Upload Audio</label>
                            <span id="uploadaudiospan">
                                <input type="submit" id="uploadaudiobutton" value="Upload" />
                            </span>
                        </div>
                        <div id="mictab" class="contentArea">
                        </div>
                        <div id="myaudio" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager('audio', $select->audiolistprivate);
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                        <div id="sharedaudio" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager('audio', $select->audiolistpublic);
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
SELECT;

    $interfaceNodes['selectdata'] = array(
        'videourl'          => $select->videourl,
        'audiourl'          => $select->audiourl,
        'videouploadurl'    => $select->videouploadurl,
        'audiouploadurl'    => $select->audiouploadurl,
        'audiolistpublic'   => $select->audiolistpublic,
        'videolistpublic'   => $select->videolistpublic,
        'audiolistprivate'  => $select->audiolistprivate,
        'videolistprivate'  => $select->videolistprivate,
    );

    $interfaceNodes['styles'] = <<<STYLES
    <style>
    #overlayContainer {
        background: #CCC;
        width     : 500px;
        height    : 300px;
    }
    .yui3-tabview-panel,
    .yui3-tabview,
    #overlayContainer .contentArea {
        width : 100%;
        height: 100%;
    }
    .hidden, .hidden * {
        display: none;
        width  : 0px;
        height : 0px;
    .kalThumb {
        float : left;
        height: 90px;
        width : 120px;
    }
    .editprogressdiv {
        display: block;
        float: left;
        height: 60px;
    }
    .editmaindiv {
        display: block;
        float: left;
        height: 240px;
    }
    .editthumbspan {
        display: block;
        float: left;
        width: 100px;
    }
    .editcontentspan {
        display: block;
        float: left;
        width: 400px;
    }
    </style>
STYLES;

    return $interfaceNodes;
}

function constructMediaPager($mediatype, $data) {
    $pagebdisabled = '';
    $pagefdisabled = '';
    if ($data['page']['current'] === $data['page']['count']) {
        $pagefdisabled = ' disabled="disabled" ';
    }
    if ($data['page']['current'] == 1) {
        $pagebdisabled = ' disabled="disabled" ';
    }
    $listhtml = '<span class="' . $mediatype . 'container">';
    $controlshtml =  '<span class="controls">'
                    .'<a href="#" class="pageb"' . $pagebdisabled . '>&lt;</a> Page ' . $data['page']['current'] . ' <a href="#" class="pagef"' . $pagefdisabled . '>&gt;</a>'
                    .'</span>';

    foreach ($data['objects'] as $entry) {
        if ($mediatype == 'audio') {
            $thumbhtml = '<span><div class="kalThumb">' . $entry->name . '</div></span>';
        }
        else { //Assume video
            $thumbhtml = '<img src="' . $entry->thumbnailUrl . '" type="image/jpeg" width="120px" height="90px" alt="' . $entry->name . '"/>';
        }

        $listhtml .= '<span class="thumb">'
                        .'<a href="#" onclick="window.kalturaWiz.selectedEntry({entryId: \'' . $entry->id . '\', upload: false});return false;" class="kalturavideo" id="' . $entry->id . '">'
                            .$thumbhtml
                        .'</a>'
                    .'</span>';
    }

    $listhtml .= '</span>';

    return $listhtml . $controlshtml;
}
////////////////////////////////////////////////////////////////
/*
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

                multiJAX([{
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

                multiJAX([{
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
*/
///////////////////////////////////////////
?>
