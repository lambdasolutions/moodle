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
 * Mandatory public API of kalturapresentation module
 *
 * @package    mod
 * @subpackage kalturapresentation
 * @copyright  2011 Brett Wilkins <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in kalturapresentation module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function kalturapresentation_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function kalturapresentation_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function kalturapresentation_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function kalturapresentation_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function kalturapresentation_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add kalturapresentation instance.
 * @param object $data
 * @param object $mform
 * @return int new kalturapresentation instance id
 */
function kalturapresentation_add_instance($data, $mform) {
    global $DB;



    $data->timemodified = time();
    $data->id = $DB->insert_record('kalturapresentation', $data);

    return $data->id;
}

/**
 * Update kalturapresentation instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function kalturapresentation_update_instance($data, $mform) {
    global $CFG, $DB;



    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('kalturapresentation', $data);

    return true;
}

/**
 * Delete kalturapresentation instance.
 * @param int $id
 * @return bool true
 */
function kalturapresentation_delete_instance($id) {
    global $DB;

    if (!$kalturapresentation = $DB->get_record('kalturapresentation', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('kalturapresentation', array('id'=>$kalturapresentation->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $kalturapresentation
 * @return object|null
 */
function kalturapresentation_user_outline($course, $user, $mod, $kalturapresentation) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'kalturapresentation',
                                              'action'=>'view', 'info'=>$kalturapresentation->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $kalturapresentation
 */
function kalturapresentation_user_complete($course, $user, $mod, $kalturapresentation) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'kalturapresentation',
                                              'action'=>'view', 'info'=>$kalturapresentation->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'kalturapresentation');
    }
}

/**
 * Returns the users with data in one kalturapresentation
 *
 * @param int $kalturapresentationid
 * @return bool false
 */
function kalturapresentation_get_participants($kalturapresentationid) {
    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function kalturapresentation_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/local/kaltura/lib.php");

    if (!$kalturapresentation = $DB->get_record('kalturapresentation', array('id'=>$coursemodule->instance), 'id, name, display, displayoptions, kalturaentry, parameters')) {
        return NULL;
    }

    $info = new stdClass();
    $info->name = $kalturapresentation->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = kalturapresentation_guess_icon($kalturapresentation->kalturaentry);

    $display = kalturapresentation_get_final_display_type($kalturapresentation);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullkalturapresentation = "$CFG->wwwroot/mod/kalturapresentation/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($kalturapresentation->displayoptions) ? array() : unserialize($kalturapresentation->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->extra = "onclick=\"window.open('$fullkalturapresentation', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullkalturapresentation = "$CFG->wwwroot/mod/kalturapresentation/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->extra = "onclick=\"window.open('$fullkalturapresentation'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_OPEN) {
        $fullkalturapresentation = "$CFG->wwwroot/mod/kalturapresentation/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->extra = "onclick=\"window.location.href ='$fullkalturapresentation';return false;\"";
    }

    return $info;
}

/**
 * This function extends the global navigation for the site.
 * It is important to note that you should not rely on PAGE objects within this
 * body of code as there is no guarantee that during an AJAX request they are
 * available
 *
 * @param navigation_node $navigation The kalturapresentation node within the global navigation
 * @param stdClass $course The course object returned from the DB
 * @param stdClass $module The module object returned from the DB
 * @param stdClass $cm The course module instance returned from the DB
 */
function kalturapresentation_extend_navigation($navigation, $course, $module, $cm) {
    /**
     * This is currently just a stub so that it can be easily expanded upon.
     * When expanding just remove this comment and the line below and then add
     * you content.
     */
    $navigation->nodetype = navigation_node::NODETYPE_LEAF;
}

function kalturapresentation_guess_icon() {
    return false;
}

function kalturapresentation_get_final_display_type(){}
