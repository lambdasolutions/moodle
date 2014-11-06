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
 * Settings for the weucontact form
 *
 * @copyright  2014 onwards Dan Marsden (http://danmarsden.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    local_weucontact
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Default Contact e-mail to use.
    $settings = new admin_settingpage('local_weucontact', get_string('pluginname', 'local_weucontact'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext('local_weucontact_email', get_string('defaultemail', 'local_weucontact'),
        get_string('configdefaultemail', 'local_weucontact'), ''));

    $settings->add(new admin_setting_configtextarea('local_weucontact_textheader', get_string('textheader', 'local_weucontact'),
        get_string('configtextheader', 'local_weucontact'), ''));

}