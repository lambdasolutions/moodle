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
 * kalturavideo module main user interface
 *
 * @package    mod
 * @subpackage kalturavideo
 * @copyright  2011 Brett Wilkins <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot."/local/kaltura/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID

$cm = get_coursemodule_from_id('kalturavideo', $id, 0, false, MUST_EXIST);
$entry = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/kalturavideo:view', $context);

add_to_log($course->id, 'kalturavideo', 'view', 'view.php?id='.$cm->id, $entry->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/kalturavideo/view.php', array('id' => $cm->id));
$PAGE->requires->js('/local/kaltura/js/kaltura-common.js');
$PAGE->requires->js('/local/kaltura/js/kaltura-play.js');

echo $OUTPUT->header();

echo '<div class="kalturaPlayer"></div>';
echo '<script>window.kaltura = {}; window.kaltura.cmid='.$id.';</script>';

echo $OUTPUT->footer();
?>
