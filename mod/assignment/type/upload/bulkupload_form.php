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
 * @package    mod
 * @subpackage assignment
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class mod_assignment_bulk_upload_responses_form extends moodleform {
    protected function definition() {
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
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'a');
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
