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
 * URL module main user interface
 *
 * @package    mod
 * @subpackage url
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/kalturavideo/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // kaltura video instance id

if ($u) {  // Two ways to specify the module
    $url = $DB->get_record('kalturavideo', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('kalturavideo', $url->id, $url->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('kalturavideo', $id, 0, false, MUST_EXIST);
    $url = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
//require_capability('mod/kalturavideo:view', $context);

add_to_log($course->id, 'kalturavideo', 'view', 'view.php?id='.$cm->id, $url->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/kalturavideo/view.php', array('id' => $cm->id));

echo $OUTPUT->header();

$baseurl = $DB->get_record('config_plugins',array('plugin' => 'local_kaltura', 'name'=>'server_uri'));
$partnerid = $DB->get_record('config_plugins',array('plugin' => 'local_kaltura', 'name'=>'partner_id'));
$playerid = $DB->get_record('config_plugins',array('plugin' => 'local_kaltura', 'name'=>'player_regular_light'));

$swfurl = $baseurl->value;
$swfurl .= '/kwidget/wid/_'.$partnerid->value;
$swfurl .= '/uiconf_id/'.$playerid->value.'/entry_id/'.$url->kalturaentry;
echo '<div id="kalturaPlayer"></div>';
echo <<<JAVASCRIPT
<script type="text/javascript">
YUI().use('swf', 'node',function(Y){
    var div = Y.one('#kalturaPlayer');
    div.set('id', 'kalturaPlayer');
    div.setStyles({
        width:400,
        height:290
    });

    var params = {
        fixedAttributes: {
            wmode: "opaque",
            allowScriptAccess: "always",
            allowFullScreen: true,
            allowNetworking: "all"
        },
        flashVars: {
            externalInterfaceDisabled: 0
        }
    };
    var kaltura_player = new Y.SWF('#kalturaPlayer', '$swfurl', params);

});
</script>
JAVASCRIPT;

echo $OUTPUT->footer();
/*switch (url_get_final_display_type($url)) {
    case RESOURCELIB_DISPLAY_EMBED:
        url_display_embed($url, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        url_display_frame($url, $cm, $course);
        break;
    default:
        url_print_workaround($url, $cm, $course);
        break;
}*/
