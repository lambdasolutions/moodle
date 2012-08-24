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
 * Course format allowing a single activity to be shown on the course homepage.
 *
 * @package    format
 * @subpackage singlemod
 * @copyright  2012 Dan Marsden <dan@danmarsden.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$choosemodule = optional_param('choosesinglemod', '', PARAM_ALPHANUM);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$context = context_course::instance($COURSE->id);

if (!empty($choosemodule) && confirm_sesskey($sesskey) && has_capability('moodle/course:update', $context)) {
    set_config($COURSE->id, $choosemodule, 'format_singlemod');
    $module = $choosemodule;
} else {
    // First check to see if a module is set in db.
    // TODO: is config_plugins the right place to store this field?
    $module = $DB->get_field('config_plugins', 'value', array('plugin'=> 'format_singlemod', 'name'=>$COURSE->id));
}

if (empty($module) || !file_exists("$CFG->dirroot/mod/$module/locallib.php")) {
    // Get list of modules that support this format.
    $records = $DB->get_records('modules', array('visible'=>1), 'name');
    foreach ($records as $record) {
        if (plugin_supports('mod', $record->name, FEATURE_COURSEFORMAT_SINGLEMOD)) {
            $supportedmodules[] = $record->name;
        }
    }
    if (empty($supportedmodules)) {
        echo $OUTPUT->notification(get_string('nosupportedpluginsfound', 'courseformat_singlemod'));
    }

    // Check if only 1 supported module returned and if so, use it, Moodle core only currently has 1 supported module - SCORM.
    if (count($supportedmodules) == 1) {
        $module = reset($supportedmodules);
        set_config($COURSE->id, $module, 'format_singlemod');
    }
}

$formatdisplayed = false;
if (!empty($module) && file_exists("$CFG->dirroot/mod/$module/locallib.php")) {
    require_once($CFG->dirroot.'/mod/'.$module.'/locallib.php');
    $moduleformat = $module.'_course_format_display';
    if (function_exists($moduleformat)) {
        $moduleformat($USER, $course);
        $formatdisplayed = true;
    }
}

// No valid format was selected - lets show the form to allow selection.
if (!empty($supportedmodules) && !$formatdisplayed) {
    if (has_capability('moodle/course:update', $context)) {
        $modules = array();
        foreach ($supportedmodules as $supportedmodule) {
            $modules[$supportedmodule] = get_string('pluginname', $supportedmodule);
        }
         // Show form and allow the user to select which module to use.
        echo '<form id="choosemodform" method="post" action="' . $PAGE->url->out(false) .'">';
        echo '<p><label for="choosesinglemod"> ' . get_string('selectmodule', 'format_singlemod') . '</label> ';
        echo html_writer::select($modules, 'choosesinglemod', '', '');
        echo '<input type="submit" value="'.get_string('choose').'"/>';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" /></p>';
        echo '</form>';
    } else {
        echo $OUTPUT->notification(get_string('notsetup', 'format_singlemod'));
    }
}