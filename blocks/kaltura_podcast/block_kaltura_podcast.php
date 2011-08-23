<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Kaltura Podcast block
 *
 * @package    block
 * @subpackage kaltura_podcast
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_kaltura_podcast extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_kaltura_podcast');
    }
    function applicable_formats() {
        return array('all' => true);
    }
    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('pluginname', 'block_kaltura_podcast'));
    }
    public function instance_allow_config() {
        return true;
    }
    function get_content() {
        global $CFG, $COURSE, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        //check existence of local plugin
        if (!file_exists($CFG->dirroot.'/local/kaltura/lib.php')) {
            $this->content->footer = $OUTPUT->notification(get_string('missingkalturaplugin', 'block_kaltura_podcast'));
            return $this->content;
        }
        //check existence of local course data plugin.
        if (!file_exists($CFG->dirroot.'/local/une_course_data/lib.php')) {
            $this->content->footer = $OUTPUT->notification(get_string('missingcoursedataplugin', 'block_kaltura_podcast'));
            return $this->content;
        }
        $this->block_kaltura_podcast_generate($COURSE->id);
        return $this->content;
    }
    function block_kaltura_podcast_generate($courseid, $updatecache=false, $updatesort=false, $thisconfig='') {
        global $CFG, $OUTPUT;
        if (empty($thisconfig)) {
            $thisconfig = $this->config;
        }
        $cachedir = $CFG->dataroot.'/cache/kaltura_podcast/';
        require_once($CFG->dirroot.'/local/kaltura/lib.php');

        //get local meta tag:
        $playlistcategory = course_data_find_by_shortname($CFG->block_kaltura_podcast_meta_category, $courseid);
        if (empty($playlistcategory)) {
            $this->content->footer = $OUTPUT->notification(get_string('missingcoursedatameta', 'block_kaltura_podcast', $CFG->block_kaltura_podcast_meta_category));
            return $this->content;
        }
        try {
            $client = kalturaClientSession(true);
            $config = $client->getConfig();
        } catch (Exception $e) {
            $this->content->text .= get_string('contenterror','block_kaltura_podcast', $e->getMessage());
            return $this->content;
        }
        //check if not update and file exists.
        if (!$updatecache && !empty($thisconfig->playlistid) && file_exists($cachedir.$thisconfig->playlistid.'.txt')){
            $cachefilename = $cachedir.$thisconfig->playlistid.'.txt';
            $handle = fopen($cachefilename, 'rb');
            $this->content->text = fread($handle, filesize($cachefilename));
            fclose($handle);
        } else {
            $updatecache = true;
            //check if this block already has a playlistid set or if the category has changed.
            if (empty($thisconfig->playlistid) || (isset($thisconfig->metatag) && $thisconfig->metatag != $playlistcategory)) {
                $thisconfig->metatag = $playlistcategory; //save category into config so we can check if it has changed.
                //create a new playlist
                try {
                    $playlist = new KalturaPlaylist;
                    $playlist->name = $playlistcategory;
                    $playlist->description = get_string('autocreatedplaylist','block_kaltura_podcast');
                    $playlist->partnerId = $config->partnerId;
                    $playlist->type = 5; //5==playlist type
                    $playlist->playlistType = 10; //10== dynamic
                    $playlist->totalResults = $CFG->block_kaltura_podcast_maxfeed;

                    //add filter for this playlist.
                    $filter = new KalturaMediaEntryFilterForPlaylist;
                    $filter->patnerIdEqual = $config->partnerId;
                    $filter->categoriesMatchOr = $playlistcategory;
                    $filter->mediaTypeIn = '1,2,5,6,201';
                    $filter->moderationStatusIn = '2,5,6,1';
                    $filter->limit = $CFG->block_kaltura_podcast_maxfeed;
                    $filter->statusIn = '2,1';
                    $filter->orderBy = 'recent';
                    $playlist->filters = array($filter);

                    $result = $client->playlist->add($playlist);
                    if (!empty($result->id)) {
                        $thisconfig->playlistid = $result->id;
                        //now create a new itunes feed for the above playlist.
                        $feed = new KalturaITunesSyndicationFeed;
                        $feed->playlistId = $thisconfig->playlistid;
                        $feed->name = $playlistcategory;
                        $feed->type = 3; //3== itundes feed type.
                        $feed->ownerName = $CFG->block_kaltura_podcast_feed_owner_name;
                        $feed->ownerEmail = $CFG->block_kaltura_podcast_feed_owner_email;
                        $feed->categories = KalturaITunesSyndicationFeedCategories::EDUCATION;
                        $feed->feedLandingPage = $CFG->block_kaltura_podcast_landing_page;
                        $feed->landingPage = $CFG->block_kaltura_podcast_landing_page;
                        $feed->flavorParamId = $CFG->block_kaltura_podcast_content_flavor;

                        $result = $client->syndicationFeed->add($feed);
                        if (!empty($result->id)) {
                            $thisconfig->feedid = $result->id;
                        }
                    } else {
                        $this->content->footer = $OUTPUT->notification(get_string('errorcreatingplaylist', 'block_kaltura_podcast'));
                        return $this->content;
                    }
                    $thisconfig->courseid = $courseid;
                    $this->instance_config_save($thisconfig);
                } catch (Exception $e) {
                    $this->content->text .= get_string('contenterror','block_kaltura_podcast', $e->getMessage());
                    return $this->content;
                }
            } else if (!empty($thisconfig->playlistid) && $updatesort) {
                $playlist = new KalturaPlaylist;
                $playlist->name = $playlistcategory;
                $playlist->description = get_string('autocreatedplaylist','block_kaltura_podcast');
                $playlist->type = 5; //5==playlist type
                $playlist->playlistType = 10; //10== dynamic
                $playlist->totalResults = $CFG->block_kaltura_podcast_maxfeed;

                //add filter for this playlist.
                $filter = new KalturaMediaEntryFilterForPlaylist;
                $filter->patnerIdEqual = $config->partnerId;
                $filter->categoriesMatchOr = $playlistcategory;
                $filter->mediaTypeIn = '1,2,5,6,201';
                $filter->moderationStatusIn = '2,5,6,1';
                $filter->limit = $CFG->block_kaltura_podcast_maxfeed;
                $filter->statusIn = '2,1';
                $filter->orderBy = $thisconfig->feedsort;
                $playlist->filters = array($filter);

                $client->playlist->update($thisconfig->playlistid, $playlist);
                $updatecache = true;
            }

            try {
                $results = $client->playlist->execute($thisconfig->playlistid);
            } catch (Exception $e) {
                $this->content->text .= get_string('contenterror','block_kaltura_podcast', $e->getMessage());
                return $this->content;
            }

            if (!empty($results)) {
                $numfeed = (!empty($thisconfig->feednum) ? $thisconfig->feednum : 10);

                //check if we need to sort this array. - kaltura doesn't perform natural sorting on numbers - eg 1, 10, 2, 20
                if (!empty($thisconfig->feedsort) && $thisconfig->feedsort == 'name') {
                    $res = array();
                    foreach ($results as $result) {
                        $res[$result->name] = $result;
                    }
                    uksort($res, "strnatcasecmp");
                    $results = $res;
                }
                $count = 0;
                $this->content->text = '';
                $row = 0;
                foreach ($results as $result) {
                    if ($count <= $numfeed) {
                        if ($result->mediaType == KalturaMediaType::IMAGE) {
                            $this->content->text .= '<div class="podcastitem image r'.$row.'">'.
                                                    '<a href="'.$result->downloadUrl.'">'.format_string($result->name).'</div>';
                        } else {
                            $mediatype = '';
                            if ($result->mediaType == KalturaMediaType::AUDIO) {
                                $mediatype = 'audio';
                            } else if ($result->mediaType == KalturaMediaType::VIDEO) {
                                $mediatype = 'video';
                            } else if ($result->mediaType == KalturaMediaType::LIVE_STREAM_FLASH) {
                                $mediatype = 'flash';
                            } else if ($result->mediaType == KalturaMediaType::LIVE_STREAM_QUICKTIME) {
                                $mediatype = 'quicktime';
                            } else if ($result->mediaType == KalturaMediaType::LIVE_STREAM_REAL_MEDIA) {
                                $mediatype = 'realmedia';
                            } else if ($result->mediaType == KalturaMediaType::LIVE_STREAM_WINDOWS_MEDIA) {
                                $mediatype = 'windowsmedia';
                            }
                            $feedtitle = format_string($result->name).'<span class="kalturaduration">('.format_time($result->duration).')</span>';
                            $url = new moodle_url('/blocks/kaltura_podcast/player.php',array('id'=>$courseid, 'entryid'=>$result->id));
                            $action = new popup_action('click', $url, 'popup', array('height' => 450, 'width' => 450));
                            $popup = $OUTPUT->action_link($url,$feedtitle , $action);
                            $this->content->text .= '<div class="podcastitem r'.$row.' '.$mediatype.'">'.$popup.'</div>';
                        }
                        $count++;
                        $row = empty($row) ? 1 : 0;
                    }
                }
            } else {
                $this->content->text .= get_string('nopodcasts','block_kaltura_podcast');
            }
        }
        //now save file if set
        if ($updatecache && !empty($this->content->text)) {
            check_dir_exists($cachedir);
            $handle = fopen($cachedir.$thisconfig->playlistid.'.txt', 'w');
            fwrite($handle, $this->content->text);
            fclose($handle);
        }
        //now display links to feeds.
        $this->content->footer = '<a href="itms://www.kaltura.com/api_v3/getFeed.php?partnerId='.$config->partnerId.'&feedId='.$thisconfig->feedid.'" target=_blank">'.
                                 $OUTPUT->pix_icon('itunesicon',get_string('itunesfeed', 'block_kaltura_podcast'),'block_kaltura_podcast').'</a>';
        $this->content->footer .= '<a href="'.$config->serviceUrl.'/index.php/partnerservices2/executeplaylist?partner_id='.$config->partnerId.'&format=8&playlist_id='.$thisconfig->playlistid.'" target="_blank">'.
                                  $OUTPUT->pix_icon('i/rss',get_string('mrssfeed', 'block_kaltura_podcast')).'</a>';
        $this->content->text = get_string('blockheader', 'block_kaltura_podcast').$this->content->text;
        $this->content->footer .= get_string('blockfooter', 'block_kaltura_podcast');
    }
    function cron() {
        global $DB;
        // We are going to measure execution times
        $starttime =  microtime();

        $rs = $DB->get_recordset('block_instances', array('blockname'=>'kaltura_podcast'));
        mtrace('');
        $counter = 0;
        foreach ($rs as $rec) {
            $config = unserialize(base64_decode($rec->configdata));
            if (!empty($config->playlistid)) {
                $block = block_instance('kaltura_podcast', $rec);
                $block->block_kaltura_podcast_generate($config->courseid, true);
                $counter++;
            }
        }
        mtrace($counter . ' kaltura feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');
    }
    function instance_config_save($data, $nolongerused = false) {
        global $COURSE;
        if (isset($data->feedsort)) { //only trigger this if comes from edit_form
            $this->block_kaltura_podcast_generate($COURSE->id, true, true, $data);
        }
        parent::instance_config_save($data);
    }
}

function course_data_find_by_shortname($shortname,$courseid) {
    global $DB;
    $sql = "SELECT d.data FROM {local_course_info_data} d, {local_course_info_field} f
            WHERE f.id=d.fieldid AND f.shortname = ? AND d.courseid = ?";
    return $DB->get_field_sql($sql, array($shortname, $courseid));
}