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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/assign/adminlib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$ADMIN->add('modules', new admin_category('assignmentplugins',
                new lang_string('assignmentplugins', 'assign'), $module->is_enabled() === false));
$ADMIN->add('assignmentplugins', new admin_category('assignsubmissionplugins',
                new lang_string('submissionplugins', 'assign'), $module->is_enabled() === false));
$ADMIN->add('assignsubmissionplugins', new assign_admin_page_manage_assign_plugins('assignsubmission'));
$ADMIN->add('assignmentplugins', new admin_category('assignfeedbackplugins',
                new lang_string('feedbackplugins', 'assign'), $module->is_enabled() === false));
$ADMIN->add('assignfeedbackplugins', new assign_admin_page_manage_assign_plugins('assignfeedback'));


assign_plugin_manager::add_admin_assign_plugin_settings('assignsubmission', $ADMIN, $settings, $module);
assign_plugin_manager::add_admin_assign_plugin_settings('assignfeedback', $ADMIN, $settings, $module);

if ($ADMIN->fulltree) {
    $yesno = array(0 => new lang_string('no'),
                   1 => new lang_string('yes'));

    $name = new lang_string('requiremodintro', 'admin');
    $description = new lang_string('configrequiremodintro', 'admin');
    $settings->add(new admin_setting_configcheckbox('assign/requiremodintro',
                                                    $name,
                                                    $description,
                                                    1));

    $menu = array();
    foreach (get_plugin_list('assignfeedback') as $type => $notused) {
        $visible = !get_config('assignfeedback_' . $type, 'disabled');
        if ($visible) {
            $menu['assignfeedback_' . $type] = new lang_string('pluginname', 'assignfeedback_' . $type);
        }
    }

    // The default here is feedback_comments (if it exists).
    $name = new lang_string('feedbackplugin', 'mod_assign');
    $description = new lang_string('feedbackpluginforgradebook', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/feedback_plugin_for_gradebook',
                                                  $name,
                                                  $description,
                                                  'assignfeedback_comments',
                                                  $menu));

    $name = new lang_string('showrecentsubmissions', 'mod_assign');
    $description = new lang_string('configshowrecentsubmissions', 'mod_assign');
    $settings->add(new admin_setting_configcheckbox('assign/showrecentsubmissions',
                                                    $name,
                                                    $description,
                                                    0));

    $name = new lang_string('sendsubmissionreceipts', 'mod_assign');
    $description = new lang_string('sendsubmissionreceipts_help', 'mod_assign');
    $settings->add(new admin_setting_configcheckbox('assign/submissionreceipts',
                                                    $name,
                                                    $description,
                                                    1));

    $name = new lang_string('submissionstatement', 'mod_assign');
    $description = new lang_string('submissionstatement_help', 'mod_assign');
    $default = get_string('submissionstatementdefault', 'mod_assign');
    $settings->add(new admin_setting_configtextarea('assign/submissionstatement',
                                                    $name,
                                                    $description,
                                                    $default));

    $name = new lang_string('requiresubmissionstatement', 'mod_assign');
    $description = new lang_string('requiresubmissionstatement_help', 'mod_assign');
    $settings->add(new admin_setting_configcheckbox('assign/requiresubmissionstatement',
                                                    $name,
                                                    $description,
                                                    0));

    $name = new lang_string('modeditdefaults', 'admin');
    $description = new lang_string('condifmodeditdefaults', 'admin');
    $settings->add(new admin_setting_heading('assignmodeditdefaults', $name, $description));

    $name = new lang_string('submissiondrafts', 'mod_assign');
    $description = new lang_string('configsubmissiondrafts', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/submissiondrafts',
                                                  $name,
                                                  $description,
                                                  0,
                                                  $yesno));

    $options = array(
        ASSIGN_ATTEMPT_REOPEN_METHOD_NONE => get_string('attemptreopenmethod_none', 'mod_assign'),
        ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL => get_string('attemptreopenmethod_manual', 'mod_assign'),
        ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS => get_string('attemptreopenmethod_untilpass', 'mod_assign')
    );
    $name = new lang_string('attemptreopenmethod', 'mod_assign');
    $description = new lang_string('configattemptreopenmethod', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/attemptreopenmethod',
                                                  $name,
                                                  $description,
                                                  ASSIGN_ATTEMPT_REOPEN_METHOD_NONE,
                                                  $options));

    $options = array(ASSIGN_UNLIMITED_ATTEMPTS => get_string('unlimitedattempts', 'mod_assign'));
    $options += array_combine(range(1, 30), range(1, 30));
    $name = new lang_string('maxattempts', 'mod_assign');
    $description = new lang_string('configmaxattempts', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/maxattempts',
                                                  $name,
                                                  $description,
                                                  -1,
                                                  $options));

    $name = new lang_string('teamsubmission', 'mod_assign');
    $description = new lang_string('configteamsubmission', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/teamsubmission',
                                                  $name,
                                                  $description,
                                                  0,
                                                  $yesno));

    $name = new lang_string('requireallteammemberssubmit', 'mod_assign');
    $description = new lang_string('configrequireallteammemberssubmit', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/requireallteammemberssubmit',
                                                  $name,
                                                  $description,
                                                  0,
                                                  $yesno));

    $name = new lang_string('sendnotifications', 'mod_assign');
    $description = new lang_string('configsendnotifications', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/sendnotifications',
                                                  $name,
                                                  $description,
                                                  1,
                                                  $yesno));

    $name = new lang_string('sendlatenotifications', 'mod_assign');
    $description = new lang_string('configsendlatenotifications', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/sendlatenotifications',
                                                  $name,
                                                  $description,
                                                  1,
                                                  $yesno));

    $name = new lang_string('blindmarking', 'mod_assign');
    $description = new lang_string('configblindmarking', 'mod_assign');
    $settings->add(new admin_setting_configselect('assign/blindmarking',
                                                  $name,
                                                  $description,
                                                  0,
                                                  $yesno));
}
