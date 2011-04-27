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
 * kalturavideo module admin settings and defaults
 *
 * @package    mod
 * @subpackage kalturavideo
 * @copyright  2011 Brett Wilkins  <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");
    require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');

    $supportedtypes = array(KalturaEntryType::MEDIA_CLIP=>get_string('mediaclip','kalturavideo'),
                            KalturaEntryType::MIX=>get_string('mix','kalturavideo'),
                           );
    $defaultsupportedtypes = array(KalturaEntryType::MEDIA_CLIP,
                            KalturaEntryType::MIX,
                           );


    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('kalturavideo/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 0));
    $settings->add(new admin_setting_configmultiselect('kalturavideo/supportedmedia',
        get_string('supportedmedia', 'kalturavideo'), get_string('configsupportedmedia','kalturavideo'),
        $defaultsupportedtypes, $supportedtypes));
    $settings->add(new admin_setting_configselect('kalturavideo/player_theme',
        get_string('playertheme','kalturavideo'), get_string('playerthemeexplain','kalturavideo'),
        'light',
        array('light'=> get_string('light', 'kalturavideo'), 'dark'=>get_string('dark','kalturavideo'))));
    $settings->add(new admin_setting_configselect('kalturavideo/editor_theme',
        get_string('editortheme','kalturavideo'), get_string('editorthemeexplain','kalturavideo'),
        'light',
        array('light'=> get_string('light', 'kalturavideo'), 'dark'=>get_string('dark','kalturavideo'))));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('kalturavideomodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturavideo/printheading',
        get_string('printheading', 'kalturavideo'), get_string('printheadingexplain', 'kalturavideo'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturavideo/printintro',
        get_string('printintro', 'kalturavideo'), get_string('printintroexplain', 'kalturavideo'),
        array('value'=>1, 'adv'=>false)));
}
