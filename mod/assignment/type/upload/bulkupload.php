<?php

/**
 *
 * @package   mod-assignment
 * @copyright 2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(__FILE__).'/bulkupload_form.php');
require_once(dirname(__FILE__).'/assignment.class.php');
require_once("$CFG->dirroot/repository/lib.php");

global $USER;

$contextid = required_param('contextid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);

$formdata = new stdClass();
$formdata->userid = $USER->id;
$formdata->offset = optional_param('offset', null, PARAM_INT);
$formdata->forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$formdata->mode = optional_param('mode', null, PARAM_ALPHA);

$url = new moodle_url('/mod/assignment/type/upload/bulkupload.php', array('contextid'=>$contextid,
                            'id'=>$id,'offset'=>$formdata->offset,'forcerefresh'=>$formdata->forcerefresh,'userid'=>$formdata->userid,'mode'=>$formdata->mode));

list($context, $course, $cm) = get_context_info_array($contextid);

require_login($course, true, $cm);
if (isguestuser()) {
    die();
}

if (!$assignment = $DB->get_record('assignment', array('id'=>$cm->instance))) {
    print_error('invalidid', 'assignment');
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$title = strip_tags($course->fullname.': '.get_string('modulename', 'assignment').': '.format_string($assignment->name,true));
$PAGE->set_title($title);
$PAGE->set_heading($title);

$instance = new assignment_upload($cm->id, $assignment, $cm, $course);

$filemanager_options = array('subdirs'=>0, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);

$mform = new mod_assignment_bulk_upload_responses_form(null, array('contextid'=>$contextid, 'userid'=>$formdata->userid, 'options'=>$filemanager_options));

if ($mform->is_cancelled()) {
   redirect($url);
} else if ($formdata = $mform->get_data()) { 
    $instance->upload($mform, $filemanager_options);
    die;
}

echo $OUTPUT->header();

echo $OUTPUT->box_start('generalbox');
if ($instance->can_manage_responsefiles() && ($id==null)) {
    $data = new stdClass();
    // move feedback files to user draft area
    $data = file_prepare_standard_filemanager($data, 'files', $filemanager_options, $context, 'mod_assignment', 'responses', $cm->id);
    // set file manager itemid, so it will find the files in draft area
    $mform->set_data($data);
    $mform->display();
}else {
    echo $OUTPUT->notification(get_string('uploaderror', 'assignment'));
    echo $OUTPUT->continue_button($url);
}
echo $OUTPUT->box_end();

echo $OUTPUT->footer();