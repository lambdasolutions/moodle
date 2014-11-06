<?php

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');

class weucontact_form extends moodleform {
    function definition() {
        global $USER;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'referrer');
        $mform->setType('referrer', PARAM_RAW);

        // fieldset sender
        $mform->addElement('header', 'sender', get_string('sender', 'local_weucontact'));
        if (isloggedin() && (!isguestuser($USER))) {
            // static_sendername
            $mform->addElement('static', 'static_sendername', get_string('name'),fullname($USER). " (". $USER->email.")");
            $mform->addElement('hidden', 'cf_sendername', fullname($USER));
            $mform->setType('cf_sendername', PARAM_TEXT);

        } else {
            // cf_opensendername
            $mform->addElement('text', 'cf_sendername', get_string('name'));
            $mform->setType('cf_sendername', PARAM_TEXT);
            $mform->addRule('cf_sendername', get_string('missingremoteusername','local_weucontact'), 'required', null, 'client');

            // cf_opensenderemail
            $mform->addElement('text', 'cf_senderemail', get_string('email'));
            $mform->addRule('cf_senderemail', get_string('missingremoteuseremail','local_weucontact'), 'required', null, 'client');
            $mform->setType('cf_senderemail', PARAM_TEXT);
            $mform->addElement('hidden', 'cf_sendermailformat', '1');
            $mform->setType('cf_sendermailformat', PARAM_INT);

        }

        // fieldset email
        $mform->addElement('header', 'email', get_string('email', 'local_weucontact'));

        // cf_mailsubject
        $mform->addElement('text', 'cf_mailsubject', get_string('mailsubject','local_weucontact'));
        $mform->addRule('cf_mailsubject', get_string('missingmailsubject','local_weucontact'), 'required', null, 'client');
        $mform->setType('cf_mailsubject', PARAM_TEXT);

       // cf_mailbody
       $textfieldoptions = array('trusttext'=>false, 'subdirs'=>true, 'maxfiles'=>3, 'maxbytes'=>1024);
       $mform->addElement('editor', 'cf_mailbody', get_string('mailbody','local_weucontact'), null, $textfieldoptions);
       $mform->setType('cf_mailbody', PARAM_RAW);
       $mform->addRule('cf_mailbody', get_string('missingmailbody','local_weucontact'), 'required', null, 'client');
       // $mform->addHelpButton('cf_mailbody', 'userdescription');
       $mform->addHelpButton('cf_mailbody', 'emailbody', 'local_weucontact');

        // buttons
        $this->add_action_buttons(true, get_string('sendemail','local_weucontact'));
    }


    function validation($data, $files) {
        $errors = array();

        if (!isloggedin()) {
            if (! validate_email($data['cf_senderemail'])) {
                $errors['cf_senderemail'] = get_string('invalidemail');
            }
        }

        return $errors;
    }
}