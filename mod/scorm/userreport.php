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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This page displays the user data from a single attempt
 *
 * @package mod
 * @subpackage scorm
 * @copyright 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/scorm/locallib.php');
require_once($CFG->dirroot.'/mod/scorm/report/reportlib.php');

$user = required_param('user', PARAM_INT); // User ID.
$id = optional_param('id', '', PARAM_INT); // Course Module ID, or
$a = optional_param('a', '', PARAM_INT); // SCORM ID
$b = optional_param('b', '', PARAM_INT); // SCO ID.

$attempt = optional_param('attempt', 0, PARAM_INT); // Attempt number passed by main reports.
if (empty($attempt)) {
    $attempt = optional_param('pattempt', 0, PARAM_INT); // Attempt number passed by pagination.
    $actualattempt = $attempt+1; // Pagination starts at 0.
} else {
    $actualattempt = $attempt;
    $attempt = $attempt -1; // Pagination starts at 0.
}

// Building the url to use for links.+ data details buildup.
$url = new moodle_url('/mod/scorm/userreport.php');
$url->param('user', $user);
$url->param('pattempt', $attempt);

if (!empty($id)) {
    $url->param('id', $id);
    $cm = get_coursemodule_from_id('scorm', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    if (!empty($b)) {
        $url->param('b', $b);
        $selsco = $DB->get_record('scorm_scoes', array('id' => $b), '*', MUST_EXIST);
        $a = $selsco->scorm;
    }
    if (!empty($a)) {
        $url->param('a', $a);
        $scorm = $DB->get_record('scorm', array('id' => $a), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $scorm->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('scorm', $scorm->id, $course->id, false, MUST_EXIST);
    }
}
$user = $DB->get_record('user', array('id'=>$user), user_picture::fields(), MUST_EXIST);
$maxattempt = scorm_get_last_attempt($scorm->id, $user->id);

$PAGE->set_url($url);
// END of url setting + data buildup.

// Checking login +logging +getting context.
require_login($course, false, $cm);
$contextmodule = context_module::instance($cm->id);
require_capability('mod/scorm:viewreport', $contextmodule);

add_to_log($course->id, 'scorm', 'userreport', 'userreport.php?id='.$cm->id, $scorm->id, $cm->id);

// Print the page header.
$strreport = get_string('report', 'scorm');
$strattempt = get_string('attempt', 'scorm');

$PAGE->set_title("$course->shortname: ".format_string($scorm->name));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strreport, new moodle_url('/mod/scorm/report.php', array('id'=>$cm->id)));

if (empty($b)) {
    if (!empty($a)) {
        $PAGE->navbar->add("$strattempt $actualattempt - ".fullname($user));
    }
} else {
    $PAGE->navbar->add("$strattempt $actualattempt - ".fullname($user),
                       new moodle_url('/mod/scorm/userreport.php', array('a'=>$a, 'user'=>$user->id, 'attempt'=>$attempt)));
    $PAGE->navbar->add($selsco->title);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($scorm->name));
// End of Print the page header.

// Printing user details.
$output = $PAGE->get_renderer('mod_scorm');
echo $output->view_user_heading($user, $course, $PAGE->url, $attempt, $maxattempt);

scorm_show_user_summary($scorm, $user, $actualattempt);

if (!empty($b)) {
    scorm_show_track_details($scorm, $selsco, $user, $actualattempt);
}

echo $OUTPUT->footer();
