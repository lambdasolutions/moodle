<?PHP

function construct_interface_tmp($select, $edit) {
    global $CFG;
    $interfaceNodes = array();

    $interfaceNodes['root'] = <<<ROOT
    <div id="overlayContainer">
        <div id="kalturahtmlcontrib" class="contentArea"></div>
        <input type="submit" value="Close" id="contribClose"/>
    </div>
ROOT;
    $interfaceNodes['rootstyles'] = <<<STYLES
    <style>
    #overlayContainer {
        background: #CCC;
        width     : 500px;
        height    : 300px;
        border    : 1px solid #000;
    }
    </style>
STYLES;

    $editstr[] = <<<EDIT
    <div id="editInterface" class="contentArea">
        <div id="editprogressdiv">
            Updatable progress bar area
        </div>
        <div id="editmaindiv">
            <span id="editthumbspan">
                <img src="$CFG->wwwroot/local/kaltura/images/ajax-loader.gif" id="contribkalturathumb" alt="Thumbnail" />
            </span>
            <span id="editcontentspan">
                <div class="editentry">
                    <label for="edittitle">Title: </label>
                    <input id="edittitle" type="text" colspan="30" />
                </div>
                <div class="editentry">
                <label for="editdescription">Description: </label>
                <input id="editdescription" type="text" colspan="30" />
                </div>
                <div class="editentry">
                <label for="edittags">Tags: </label>
                <input id="edittags" type="text" colspan="30" />
                </div>
                <div class="editentry">
                    <label for="editcategoriestext">Categories: </label>
                    <span id="editcategories">
                        <input id="editcategoriesids" type="hidden" />
                        <input id="editcategoriestext" type="text" colspan="30" disabled />
                        <div id="editcategoriestreeview">
                        </div>
                    </span>
                </div>
            </span>
        </div>
        <div id="editfooterdiv">
            <input id="editupdate" type="submit" value="Update" />
        </div>
    </div>
EDIT;

    $interfaceNodes['edit'] = implode('', $editstr);
    $interfaceNodes['editstyles'] = <<<STYLES
    <style>
    #editprogressdiv {
        display: block;
        float  : left;
        height : 60px;
    }
    #editmaindiv {
        display: block;
        float  : left;
        height : 200px;
    }
    #editfooterdiv {
        display: block;
        float  : left;
        height : 40px;
    }
    #editthumbspan {
        display: block;
        float  : left;
        width  : 110px;
    }
    #editcontentspan {
        display: block;
        float  : left;
        width  : 390px;
    }
    #contribkalturathumb {
        width : 100px;
        height: 75px;
        border: 1px solid #000;
        margin-left: 4px;
    }
    .editentry {
        display: block;
    }
    .editentry label {
        width  : 70px;
        display: inline-block;
    }
    .editentry input,
    .editentry > span {
        width  : 300px;
        display: inline-block;
    }
    #editcategoriestreeview {
        overflow: auto;
        height  : 120px;
    }
    span.fullName {
        display: none;
    }
    </style>
STYLES;

    $interfaceNodes['editdata'] = array(
        'categorylist'      => array(),
    );

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
    $interfaceNodes['select'] .= constructMediaPager_tmp('video');
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                        <div id="sharedvideo" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager_tmp('video');
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
    $interfaceNodes['select'] .= constructMediaPager_tmp('audio');
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                        <div id="sharedaudio" class="contentArea">
                            <div>
SELECT;
    $interfaceNodes['select'] .= constructMediaPager_tmp('audio');
    $interfaceNodes['select'] .= <<<SELECT
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
SELECT;

    $interfaceNodes['selectstyles'] = <<<STYLES
    <style>
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
    </style>
STYLES;

    return $interfaceNodes;
}

function constructMediaPager_tmp($mediatype) {
    global $CFG;
    $pagefdisabled = ' disabled="disabled" ';
    $pagebdisabled = ' disabled="disabled" ';
    $listhtml = '<span class="' . $mediatype . 'container">';
    $controlshtml =  '<span class="controls">'
                    .'<a href="#" class="pageb"' . $pagebdisabled . '>&lt;</a> Page 1 <a href="#" class="pagef"' . $pagefdisabled . '>&gt;</a>'
                    .'</span>';

    $data = array();
    $data['objects'] = array();
    for ($i = 0; $i < 9; $i++) {
        $tmp = new stdClass;
        $tmp->thumbnailUrl = $CFG->wwwroot.'/local/kaltura/images/ajax-loader.gif';
        $tmp->name = 'Temporary Data';
        $tmp->id = '0_dfsf323';
        $data['objects'][] = $tmp;
    }

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
