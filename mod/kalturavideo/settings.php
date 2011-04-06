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
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('kalturavideo/framesize',
        get_string('framesize', 'kalturavideo'), get_string('configframesize', 'kalturavideo'), 130, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('kalturavideo/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configpasswordunmask('kalturavideo/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'kalturavideo'), ''));
    $settings->add(new admin_setting_configcheckbox('kalturavideo/rolesinparams',
        get_string('rolesinparams', 'kalturavideo'), get_string('configrolesinparams', 'kalturavideo'), false));
    $settings->add(new admin_setting_configmultiselect('kalturavideo/displayoptions',
        get_string('displayoptions', 'kalturavideo'), get_string('configdisplayoptions', 'kalturavideo'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('kalturavideomodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturavideo/printheading',
        get_string('printheading', 'kalturavideo'), get_string('printheadingexplain', 'kalturavideo'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('kalturavideo/printintro',
        get_string('printintro', 'kalturavideo'), get_string('printintroexplain', 'kalturavideo'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('kalturavideo/display',
        get_string('displayselect', 'kalturavideo'), get_string('displayselectexplain', 'kalturavideo'),
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('kalturavideo/popupwidth',
        get_string('popupwidth', 'kalturavideo'), get_string('popupwidthexplain', 'kalturavideo'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('kalturavideo/popupheight',
        get_string('popupheight', 'kalturavideo'), get_string('popupheightexplain', 'kalturavideo'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
