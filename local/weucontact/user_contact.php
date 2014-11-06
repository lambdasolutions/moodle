<?php
require_once('../../config.php');
require_once('user_contact_form.php');

$cid = optional_param('id', 0, PARAM_INT); // course ID
$course = $DB->get_record('course', array('id' => $cid));

$referrerurl = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    $referrerurl = $_SERVER['HTTP_REFERER'];
    // See if we can guess the courseid based on the referrerurl.
    if (empty($course) && strpos($referrerurl, $CFG->wwwroot.'/course/view.php?id=') === 0) {
        $query = parse_url($referrerurl, PHP_URL_QUERY);
        parse_str($query, $params);
        if (!empty($params['id'])) {
            $course = $DB->get_record('course', array('id' => $params['id']));
        }
    }
}
if (!empty($course)) {
    $cid = $course->id;
}
if (isloggedin()) {
    // call require login to set up headers.
    require_login($course);
}

$PAGE->set_url('/local/weucontact/usercontact.php', array('id' => $cid));

if (!empty($course)) {
    $context = context_course::instance($course->id);
    $PAGE->set_title(get_string('contact','local_weucontact'));
    $PAGE->set_heading(get_string('contact','local_weucontact'));
} else {
    $context = context_system::instance();
}

$PAGE->set_context($context);

$PAGE->navbar->add(get_string('contact','local_weucontact'));

$mform = new weucontact_form($CFG->wwwroot.'/local/weucontact/user_contact.php');

$mform->set_data(array('id' => $cid, 'referrer' => $referrerurl));

$courses = array(); // List of courses this user is enrolled in.
if ($mform->is_cancelled()) {
    // submission was canceled. Return back.
    $returnurl = ($cid == SITEID) ? new moodle_url('/index.php') : new moodle_url('/course/view.php', array('id' => $cid));
    redirect($returnurl);
} else if ($fromform = $mform->get_data()) {
    // form was successfully submitted. Now send.
    $subject = $fromform->cf_mailsubject;
    $bodyhtml = format_text($fromform->cf_mailbody['text'], $fromform->cf_mailbody['format'], array('context' => $context, 'para' => false, 'newlines' => true));
    $bodytext = html_to_text($bodyhtml);
    $spacer = '--------------------------------';
    $bodyhtml .= "<p>".$spacer."</p>";
    $bodytext .= "/n/n".$spacer."/n";
    if (isloggedin()) {
        $from = $USER;
        $replyto = '';

        $courses = enrol_get_users_courses($USER->id, true); // get list of enrolled courses.
    } else {
        $from = $fromform->cf_sendername;
        $replyto = $fromform->cf_senderemail;
        // Check to see if this user has an account.
        $checkuser = $DB->get_record('user', array('email' => $replyto));
        if (!empty($checkuser)) {
            // Get the list of courses this user is enrolled in.
            $courses = enrol_get_users_courses($checkuser->id, true);
        }
    }
    // Add previous page to text.
    $bodyhtml .= "<p>".get_string('previouspage', 'local_weucontact').' '.$fromform->referrer."</p>";

    // Add course enrolments to text.
    if (!empty($courses)) {
        $bodyhtml .= "<p>".get_string('courselist', 'local_weucontact')."</p>";
        $bodytext .= "/n/n".get_string('courselist', 'local_weucontact')."/n";
    }
    foreach ($courses as $c) {
        $bodyhtml .= "<a href = '".$CFG->wwwroot."/course/view.php?id=".$c->id."'>".$c->shortname . ':</a> '. $c->fullname.'<br/>';
        $bodytext .= $c->shortname . ': '. $c->fullname.'/n';

    }
    // Add other info about browser/IP addresses etc.
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $spacer = '--------------------------------';
        $bodyhtml .= "<p>".$spacer."</p>";
        $bodytext .= "/n/n".$spacer."/n";
        $bodyhtml .= "<p>".$_SERVER['HTTP_USER_AGENT']."</p>";
        $bodytext .= "/n/n".$_SERVER['HTTP_USER_AGENT']."/n";

    }
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $bodyhtml .= "<p>".$spacer."</p>";
        $bodytext .= "/n/n".$spacer."/n";
        $bodyhtml .= "<p>".$_SERVER['REMOTE_ADDR']."</p>";
        $bodytext .= "/n/n".$_SERVER['REMOTE_ADDR']."/n";
        if (isset($_SERVER['REMOTE_HOST'])) {
            $bodyhtml .= "<p>".$_SERVER['REMOTE_HOST']."</p>";
            $bodytext .= "/n/n".$_SERVER['REMOTE_HOST']."/n";
        }
    }

    $contact = $DB->get_record('user', array('email' => $CFG->local_weucontact_email));
    if (empty($contact)) {
        $contact = core_user::get_support_user();
    }

    if (empty($contact) || !validate_email($contact->email)) {
        error("This form appears to be broken, please use a different method to contact us and let us know this form is broken!");
    }
    email_to_user($contact, $from, $subject, $bodytext, $bodyhtml, '', '', true, $replyto);

    $returnurl = ($cid == SITEID) ? new moodle_url('/index.php') : new moodle_url('/course/view.php', array('id' => $cid));
    redirect($returnurl, get_string('messagesent', 'local_weucontact'));
} else {
    //this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    //or on the first display of the form.
    //put data you want to fill out in the form into array $toform here then :
    echo $OUTPUT->header();
    echo $OUTPUT->box($CFG->local_weucontact_textheader);
    $mform->display();
   // Finish the page
   echo $OUTPUT->footer();
}
