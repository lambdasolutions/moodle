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
 * @package   mod_forum
 * @copyright 2014 Dan Marsden <dan@danmarsden.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/forum/gradeform.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/forum/renderable.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$userid = required_param('userid', PARAM_INT); // User id.
$postid = optional_param('postid', 0, PARAM_INT); // Post id.

$params = array();
$params['id'] = $id;

$PAGE->set_url('/mod/forum/grade.php', $params);

$cm = get_coursemodule_from_id('forum', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);

$forum = $DB->get_record("forum", array("id" => $cm->instance), '*', MUST_EXIST);

$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
if (!empty($postid)) {
    $post = forum_get_post_full($postid);
    $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion));
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// Need mod/forum:grade capability.
require_capability('mod/forum:grade', $context);

$params = array('userid' => $user->id);
$data = new stdClass();
$data->grade = '';

$formparams = array($forum, $data, $params, $context);
$mform = new mod_forum_grade_form(null,
    $formparams,
    'post',
    '',
    array('class'=>'gradeform'));

$PAGE->set_title($forum->name);
$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('mod_forum');


echo $OUTPUT->header();
if (!empty($post)) {
    forum_print_post($post, $discussion, $forum, $cm, $course);
}

echo $renderer->render(new forum_form('gradingform', $mform));

echo $OUTPUT->footer($course);
