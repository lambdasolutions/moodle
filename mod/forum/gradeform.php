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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_form
 * @copyright 2014 Dan Marsden <dan@danmarsden.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once('HTML/QuickForm/input.php');

/**
 * forum grade form
 *
 * @package   mod_form
 * @copyright 2014 Dan Marsden <dan@danmarsden.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_forum_grade_form extends moodleform {
    /** @var forum $forum */
    private $forum;

    /**
     * Define the form - called by parent constructor.
     */
    public function definition() {
        $mform = $this->_form;

        list($forum, $data, $params, $context) = $this->_customdata;
        $userid = $params['userid'];
        // Visible elements.
        $gradingdisabled = false;
        $gradinginstance = mod_forum_get_grading_instance($userid, $data->grade, $gradingdisabled, $context, 'posts');

        $mform->addElement('header', 'gradeheader', get_string('grade'));
        if ($gradinginstance) {
            $gradingelement = $mform->addElement('grading',
                'advancedgrading',
                get_string('grade').':',
                array('gradinginstance' => $gradinginstance));
            if ($gradingdisabled) {
                $gradingelement->freeze();
            } else {
                $mform->addElement('hidden', 'advancedgradinginstanceid', $gradinginstance->get_id());
                $mform->setType('advancedgradinginstanceid', PARAM_INT);
            }
        } else {
            // TODO: Use simple direct grading.
        }

        if ($data) {
            $this->set_data($data);
        }
    }

}
