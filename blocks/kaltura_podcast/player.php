<?php

require('../../config.php');
require_once($CFG->dirroot."/local/kaltura/lib.php");

$id       = required_param('id', PARAM_INT);        // Course id
$entryid  = required_param('entryid', PARAM_ALPHANUMEXT);        // Course id
$course   = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);

$PAGE->set_url('/blocks/kaltura_podcast/player.php', array('id' => $id,'entryid'=>$entryid));

$PAGE->requires->js('/local/kaltura/js/kaltura-play.js');
$PAGE->requires->css('/local/kaltura/styles.css');
$PAGE->set_pagelayout('popup');


//print content
echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo '<script type="text/javascript">window.kaltura = {entryid: "'.$entryid.'"}</script>';
echo '<div class="kalturaPlayer"></div>';
echo $OUTPUT->box_end();
echo $OUTPUT->close_window_button();
echo $OUTPUT->footer();