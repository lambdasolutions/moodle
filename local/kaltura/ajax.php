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
require_once("lib.php");
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');

$id         = optional_param('id', 0, PARAM_INT);
$actions    = optional_param('actions', '', PARAM_TAGLIST);
$entryid    = optional_param('entryid', '', PARAM_CLEAN);
$videotype  = optional_param('videotype', 0, PARAM_INT);
$mixentries = optional_param('mixentries', '', PARAM_TAGLIST);
$mixname    = optional_param('mixname','', PARAM_TEXT);

require_login();

$actions = explode(',', $actions);

$returndata = array();

foreach ($actions as $action) {
    switch ($action) {
        case 'playerurl':
            $entry = null;
            if (!empty($id)) {
                $cm = get_coursemodule_from_id('kalturavideo', $id, 0, false, MUST_EXIST);
                $entry = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);
            }
            else if (!empty($entryid)) {
                $entry = new stdClass;
                $entry->kalturaentry = $entryid;
            }
            else {
                //what are we displaying? :o
                break;
            }
            if ($videotype == KalturaEntryType::MIX) {
                $mix = true;
            }
            else {
                $mix = false;
            }

            $url = kalturaPlayerUrlBase($mix);
            $returndata['playerurl'] = array('url' => $url.$entry->kalturaentry);
            break;

        case 'cwurl':
            if ($videotype == KalturaEntryType::MIX) {
                $tmp = kalturaCWSession_setup(true);
            } else {
                $tmp = kalturaCWSession_setup();
            }
            $returndata['cwurl'] = $tmp;
            break;

        case 'editorurl':
            if (empty($entryid)) {
                break;
            }
            $returndata['editorurl'] = kalturaEditor_setup($entryid);
            break;

        case 'mixaddentries':
            if (!empty($mixentries)) {
                $client = kalturaClientSession();
                $mix = new KalturaMixEntry();
                $mix->name = "Editable video";
                if (!empty($mixname)) {
                    $mix->name = $mixname;
                }
                $mix->editorType = KalturaEditorType::ADVANCED;
                $mix = $client->mixing->add($mix);

                $mixentries = explode(',', $mixentries);
                foreach ($mixentries as $mid) {
                    $client->mixing->appendMediaEntry($mix->id, $mid);
                }
                $returndata['mixaddentries'] = array('entryid' => $mix->id);
            }
            break;

        default:
            break;
    }
}

header('Content-Type: application/json');

echo json_encode($returndata);
?>
