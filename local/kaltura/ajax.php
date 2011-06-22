<?php
/**
 * Kaltura Local Plugin for Moodle 2
 * Copyright (C) 2009 Petr Skoda  (http://skodak.org)
 * Copyright (C) 2011 Catalyst IT (http://www.catalyst.net.nz)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    local
 * @subpackage kaltura
 * @author     Brett Wilkins <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero GPL v3 or later
 */

require('../../config.php');
require_once("lib.php");
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');

$actions       = optional_param('actions', '', PARAM_CLEAN);
$params        = optional_param('params', null, PARAM_CLEAN);

require_login();

$returndata = array();

foreach ($actions as $index => $action) {
    if (isset($params[$index])) {
        $p = $params[$index];
    }

    switch ($action) {
        case 'playerurl':
            $entry = null;
            if (!empty($p['id'])) {
                $cm = get_coursemodule_from_id('kalturavideo', $p['id'], 0, false, MUST_EXIST);
                $entry = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);
            }
            else if (!empty($p['entryid'])) {
                $entry = new stdClass;
                $entry->kalturavideo = $p['entryid'];
            }

            $url = kalturaPlayerUrlBase();
            $returndata[$index] = array('url' => $url.$entry->kalturavideo, 'params' => array());
            break;

        case 'cwurl':
            $returndata[$index] = kalturaCWSession_setup();
            break;

        case 'audiourl':
            $client = kalturaClientSession();
            $config = $client->getConfig();

            $returndata[$index] = array('url' => $CFG->wwwroot.'/local/kaltura/objects/audio.swf', 'params' => array('ks' => $client->getKs(), 'host' => $config->serviceUrl, 'uid' => $USER->id, 'pid' => $config->partnerId, 'subpid' => $config->partnerId*100, 'kshowId' => -1, 'autopreview' => true, 'themeUrl' => $CFG->wwwroot.'/local/kaltura/objects/skin.swf', 'entryName' => 'New Entry', 'entryTags' => 'audio', 'thumbOffset' => 1, 'useCamera' => 'false'));
            break;

        case 'videourl':
            $client = kalturaClientSession();
            $config = $client->getConfig();

            $returndata[$index] = array('url' => $CFG->wwwroot.'/local/kaltura/objects/video.swf', 'params' => array('ks' => $client->getKs(), 'host' => $config->serviceUrl, 'uid' => $USER->id, 'pid' => $config->partnerId, 'subpid' => $config->partnerId*100, 'kshowId' => -1, 'autopreview' => true, 'themeUrl' => $CFG->wwwroot.'/local/kaltura/objects/skin.swf', 'entryName' => 'New Entry', 'entryTags' => 'audio', 'thumbOffset' => 1));
            break;

        case 'search':
            list($client, $filter, $pager) = buildListFilter();

            $filter->searchTextMatchOr($search_term);

            $results = $client->media->listAction($filter, $pager);
            $count   = $client->media->count($filter);
            $pagecount = ceil($count/$pager->pageSize);

            if ($pager->pageIndex > $pagecount) {
                $returndata = array();
                break;
            }

            $returndata[$index] = array(
                'page' => array(
                    'count' => $pagecount,
                    'current' => $pager->pageIndex,
                ),
                'count' => $count,
                'objects' => $results->objects,
            );
            break;

        case 'listpublic':
            list($client, $filter, $pager) = buildListFilter();

            $results = $client->media->listAction($filter, $pager);
            $count   = $client->media->count($filter);
            $pagecount = ceil($count/$pager->pageSize);

            if ($pager->pageIndex > $pagecount) {
                $returndata = array();
                break;
            }

            $returndata[$index] = array(
                'page' => array(
                    'count' => $pagecount,
                    'current' => $pager->pageIndex,
                ),
                'count' => $count,
                'objects' => $results->objects,
            );
            break;

        case 'listprivate':
            list($client, $filter, $pager) = buildListFilter();

            $filter->userIdEqual = $USER->id;

            $results = $client->media->listAction($filter, $pager);
            $count   = $client->media->count($filter);
            $pagecount = ceil($count/$pager->pageSize);

            if ($pager->pageIndex > $pagecount) {
                $returndata = array();
                break;
            }

            $returndata[$index] = array(
                'page' => array(
                    'count' => $pagecount,
                    'current' => $pager->pageIndex,
                ),
                'count' => $count,
                'objects' => $results->objects,
            );
            break;

        default:
            break;
    }
}

function buildListFilter() {
    global $params;

    $client = kalturaClientSession();
    $config = $client->getConfig();

    $pager = new KalturaFilterPager();
    $pager->pageSize  = 9;
    $pager->pageIndex = 1;
    if (!empty($params['page'])) {
        $pager->pageIndex=$params['page'];
    }

    $filter = new KalturaMediaEntryFilter();
    $filter->patnerIdEqual = $config->partnerId;
    $filter->statusEqual   = KalturaEntryStatus::READY;
    if (isset($params['mediatype']) && $params['mediatype'] == 'video') {
        $filter->mediaTypeEqual = KalturaMediaType::VIDEO;
    }
    else if (isset($params['mediatype']) && $params['mediatype'] == 'audio') {
        $filter->mediaTypeEqual = KalturaMediaType::AUDIO;
    }

    return array($client, $filter, $pager);
}

header('Content-Type: application/json');

echo json_encode($returndata);
?>
