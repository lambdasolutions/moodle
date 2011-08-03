<?php

require_once($CFG->libdir.'/formslib.php');//putting this is as a safety as i got a class not found error.
/**
 * @package   mod-assignment
 * @copyright 2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class mod_assignment_bulk_upload_responses_form extends moodleform {
    function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        // visible elements
        $mform->addElement('header', 'qgprefs', get_string('bulkupload', 'assignment'));
        $mform->addElement('filemanager', 'files_filemanager', '', null, $instance['options']);

        $choices = array(
            0 => get_string('bulkupload_rename', 'assignment'),
            1 => get_string('bulkupload_replace', 'assignment'),
            2 => get_string('bulkupload_skip', 'assignment') );
        $mform->addElement('select', 'overwritefeedback', get_string('bulkupload_overwrite', 'assignment'), $choices);
        $mform->setType('overwritefeedback', PARAM_INT);
        $mform->addHelpButton('overwritefeedback', 'bulkupload_overwrite', 'assignment');

        // hidden params
        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'uploadresponses');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'mode', 'all');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'offset', -1);
        $mform->setType('action', PARAM_ALPHA);

        // buttons
        $mform->addElement('submit', 'uploadzip', get_string('uploadzip', 'assignment'));

    }
}
