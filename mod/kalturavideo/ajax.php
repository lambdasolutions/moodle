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
 * URL module main user interface
 *
 * @package    mod
 * @subpackage kalturavideo
 * @copyright  2011 Brett Wilkins <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("locallib.php");
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');

$id             = optional_param('id', 0, PARAM_INT);
$fields         = optional_param('fields', '', PARAM_TAGLIST);
$entryid        = optional_param('entryid', '', PARAM_CLEAN);
$uploader       = optional_param('uploader', false, PARAM_BOOL);
$mix            = optional_param('mix', false, PARAM_BOOL);

if ($id != 0) {
    $cm = get_coursemodule_from_id('kalturavideo', $id, 0, false, MUST_EXIST);
    $entry = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
}

if (!$uploader) {
    if (empty($entry)) {
        $entry = new stdClass;
        $entry->videotype = KalturaEntryType::MEDIA_CLIP;
        if ($mix) {
            $entry->videotype = KalturaEntryType::MIX;
        }
    } else if (!empty($entry) && empty($entryid)) {
        $entryid = $entry->kalturaentry;
    }
}

require_login();

$fields = explode(',', $fields);

$returndata = array();

foreach ($fields as $field) {
    switch ($field) {
        case 'url':
            if (!$uploader) {
                if ($entry->videotype != KalturaEntryType::MIX and !$mix) {
                    $url = kalturaPlayerUrlBase();
                } else if ($entry->videotype == KalturaEntryType::MIX or $mix) {
                    $url = kalturaPlayerUrlBase(true);
                }
                $returndata['url'] = $url.$entryid;
            } else {
                if (!$mix) {
                    $tmp = kalturaCWSession_setup();
                } else if ($mix) {
                    $tmp = kalturaCWSession_setup(true);
                }
                $returndata['url'] = $tmp['url'];
                $returndata['params'] = $tmp['params'];
            }
            break;

        default:
            break;
    }
}

header('Content-Type: application/json');

echo json_encode($returndata);
?>
