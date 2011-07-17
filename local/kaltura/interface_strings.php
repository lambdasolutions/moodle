<?PHP

function construct_interface($select, $edit) {
    global $CFG;
    $interfaceNodes = array();

    $interfaceNodes['root'] = <<<ROOT
    <div id="overlayContainer">
        <div id="kalturahtmlcontrib" class="contentArea"></div>
        <input type="submit" value="Close" id="contribClose"/>
    </div>
ROOT;

    $categories = array();
    $depth      = array();
    if (!empty($edit->categorylist)) {
        foreach ($edit->categorylist['categories']->objects as $index => $category) {
            if (empty($depth[$category->depth])) {
                $depth[$category->depth] = array();
            }
            $c              = new stdclass;
            $c->type        = 'text';
            $c->label       = $category->name;
            $c->catFullName = $category->fullName;
            $c->parentId    = $category->parentId;
            $c->catId       = $category->id;
            $depth[$category->depth][$category->id] = $c;
        }

        for ($i = count($depth)-1; $i > 0; $i--) {
            foreach ($depth[$i] as $id => $category) {
                $parent = $depth[$i-1][$category->parentId];
                if (empty($parent->children)) {
                    $parent->children = array();
                }
                $parent->children[] = $category;
            }
        }

        $categories = array_values($depth[0]);
    }

    $editstr[] = <<<EDIT
    <div id="editInterface" class="contentArea">
             <ul class="yui3-tabview-list" >
                        <li class="yui3-tab-selected"><a href="">Edit Information</a></li>
                    </ul>
        <div id="edit-inner">
		<div id="edit-content">
            <input type="hidden" id="editentryid" />
            <div id="editprogressdiv">
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
        </div>
    </div>
EDIT;

    $interfaceNodes['edit'] = implode('', $editstr);
    $interfaceNodes['editdata'] = array(
        'categorylist'      => $categories,
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
                                <div id="uploadvideo"></div>
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
                                <div id="uploadaudio"></div>
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
            $thumbhtml = '<span><div class="kalthumb">' . $entry->name . '</div></span>';
        }
        else { //Assume video
            $thumbhtml = '<img src="' . $entry->thumbnailUrl . '" type="image/jpeg" class="kalthumb" alt="' . $entry->name . '"/>';
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
function constructCategoryMarkup($category) {
    $editstr = array();
    $editstr[] = "<li>$category->name";
    if (!empty($category->children)) {
        $editstr[] = '<ul>';
        foreach ($category->children as $c) {
            $editstr[] = constructCategoryMarkup($c);
        }
        $editstr[] = '</ul>';
    }
    $editstr[] = '</li>';
    return implode('', $editstr);
}
?>
