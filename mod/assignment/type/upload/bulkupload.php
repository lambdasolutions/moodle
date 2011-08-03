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
 *
 * @package    mod
 * @subpackage assignment
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/assignment/type/upload/bulkupload_form.php');
require_once($CFG->dirroot.'/mod/assignment/type/upload/assignment.class.php');
require_once($CFG->dirroot.'/repository/lib.php');

$a = required_param('a', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$offset = optional_param('offset', null, PARAM_INT);
$forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$mode = optional_param('mode', null, PARAM_ALPHA);

$assignment = $DB->get_record("assignment", array("id"=>$a), '*', MUST_EXIST);
$course = $DB->get_record("course", array("id"=>$assignment->course),'*', MUST_EXIST);
$cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id, false, MUST_EXIST);

$contextmodule = get_context_instance(CONTEXT_MODULE, $cm->id);

$url = new moodle_url('/mod/assignment/type/upload/bulkupload.php',
                      array('a'=>$a,
                            'offset'=>$offset,
                            'forcerefresh'=>$forcerefresh,
                            'mode'=>$mode));

require_login($course, true, $cm);

$instance = new assignment_upload($cm->id, $assignment, $cm, $course);
if (!$instance->can_manage_responsefiles()) {
    print_error('invalidaccess');
}
$PAGE->set_url($url);
$PAGE->set_context($contextmodule);
$title = strip_tags($course->fullname.': '.get_string('modulename', 'assignment').
         ': '.format_string($assignment->name, true, $course->id));
$PAGE->set_title($title);
$PAGE->set_heading($title);



$filemanager_options = array('subdirs'=>0, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>-1,
                             'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);

$mform = new mod_assignment_bulk_upload_responses_form(null,
         array('userid'=>$USER->id, 'options'=>$filemanager_options));

if (empty($action)) {
    $data = new stdClass();
    // move feedback files to user draft area
    $data = file_prepare_standard_filemanager($data, 'files', $filemanager_options, $contextmodule, 'mod_assignment', 'responses', $cm->id);
    $data->a = $a;
    // set file manager itemid, so it will find the files in draft area
    $mform->set_data($data);
}
if ($mform->is_cancelled()) {
    redirect($url);
} else if ($mform->get_data()) {
    $instance->upload($mform, $filemanager_options);
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');

if (empty($action)) {
    $mform->display();
} else {
    echo $OUTPUT->notification(get_string('uploaderror', 'assignment'));
    echo $OUTPUT->continue_button($url);
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();