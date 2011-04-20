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
 * kalturapresentation module admin settings and defaults
 *
 * @package    mod
 * @subpackage kalturapresentation
 * @copyright  2011 Brett Wilkins  <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_NEW,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('kalturapresentation/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 0));
    $settings->add(new admin_setting_configmultiselect('kalturapresentation/displayoptions',
        get_string('displayoptions', 'kalturapresentation'), get_string('configdisplayoptions', 'kalturapresentation'),
        $defaultdisplayoptions, $displayoptions));
    $settings->add(new admin_setting_configselect('kalturapresentation/player_theme',
        get_string('playertheme','kalturapresentation'), get_string('playerthemeexplain','kalturapresentation'),
        array('value'=>'light'),
        array('light'=> get_string('light', 'kalturapresentation'), 'dark'=>get_string('dark','kalturapresentation'))));
    $settings->add(new admin_setting_configselect('kalturapresentation/editor_theme',
        get_string('editortheme','kalturapresentation'), get_string('editorthemeexplain','kalturapresentation'),
        array('value'=>'light'),
        array('light'=> get_string('light', 'kalturapresentation'), 'dark'=>get_string('dark','kalturapresentation'))));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('kalturapresentationmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturapresentation/printheading',
        get_string('printheading', 'kalturapresentation'), get_string('printheadingexplain', 'kalturapresentation'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturapresentation/printintro',
        get_string('printintro', 'kalturapresentation'), get_string('printintroexplain', 'kalturapresentation'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('kalturapresentation/display',
        get_string('displayselect', 'kalturapresentation'), get_string('displayselectexplain', 'kalturapresentation'),
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('kalturapresentation/popupwidth',
        get_string('popupwidth', 'kalturapresentation'), get_string('popupwidthexplain', 'kalturapresentation'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('kalturapresentation/popupheight',
        get_string('popupheight', 'kalturapresentation'), get_string('popupheightexplain', 'kalturapresentation'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
