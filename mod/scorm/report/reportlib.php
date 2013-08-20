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
 * Returns an array of reports to which are currently readable.
 * @package    mod
 * @subpackage scorm
 * @author     Ankit Kumar Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Generates and returns list of available Scorm report sub-plugins
 *
 * @param context context level to check caps against
 * @return array list of valid reports present
 */
function scorm_report_list($context) {
    global $CFG;
    static $reportlist;
    if (!empty($reportlist)) {
        return $reportlist;
    }
    $installed = core_component::get_plugin_list('scormreport');
    foreach ($installed as $reportname => $notused) {
        $pluginfile = $CFG->dirroot.'/mod/scorm/report/'.$reportname.'/report.php';
        if (is_readable($pluginfile)) {
            include_once($pluginfile);
            $reportclassname = "scorm_{$reportname}_report";
            if (class_exists($reportclassname)) {
                $report = new $reportclassname();

                if ($report->canview($context)) {
                    $reportlist[] = $reportname;
                }
            }
        }
    }
    return $reportlist;
}
/**
 * Returns The maximum numbers of Questions associated with an Scorm Pack
 *
 * @param int Scorm ID
 * @return int an integer representing the question count
 */
function get_scorm_question_count($scormid) {
    global $DB;
    $count = 0;
    $params = array();
    $select = "scormid = ? AND ";
    $select .= $DB->sql_like("element", "?", false);
    $params[] = $scormid;
    $params[] = "cmi.interactions_%.id";
    $rs = $DB->get_recordset_select("scorm_scoes_track", $select, $params, 'element');
    $keywords = array("cmi.interactions_", ".id");
    if ($rs->valid()) {
        foreach ($rs as $record) {
            $num = trim(str_ireplace($keywords, '', $record->element));
            if (is_numeric($num) && $num > $count) {
                $count = $num;
            }
        }
        // Done as interactions start at 0 (do only if we have something to report).
        $count++;
    }
    $rs->close(); // Closing recordset.
    return $count;
}

/**
 * prints the track details part of the user report.
 * TODO: This should be converted to use renderers at some point.
 *
 * @param object $scorm
 * @param object $sco - Sco we are displaying tracks for
 * @param object $user - User we are displaying tracks for
 * @param int    $attempt - The attempt number we are displaying tracks for
 */
function scorm_show_track_details($scorm, $sco, $user, $attempt) {
    global $OUTPUT, $CFG;
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo $OUTPUT->heading('<a href="'.$CFG->wwwroot.'/mod/scorm/player.php?a='.$scorm->id.
                          '&amp;mode=browse&amp;scoid='.$sco->id.'" target="_new">'.
                          format_string($sco->title).'</a>');

    echo '<div class="mdl-align">'."\n";
    $scoreview = '';
    if ($trackdata = scorm_get_tracks($sco->id, $user->id, $attempt)) {
        if ($trackdata->score_raw != '') {
            $scoreview = get_string('score', 'scorm').':&nbsp;'.$trackdata->score_raw;
        }
        if ($trackdata->status == '') {
            $trackdata->status = 'notattempted';
        }
    } else {
        $trackdata->status = 'notattempted';
        $trackdata->total_time = '';
    }
    $strstatus = get_string($trackdata->status, 'scorm');
    echo '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.
        $strstatus.'" />&nbsp;'.scorm_format_duration($trackdata->total_time).'<br />'.$scoreview.'<br />';
    echo '</div>'."\n";
    echo '<hr /><h2>'.get_string('details', 'scorm').'</h2>';
    // Print general score data.
    $table = new html_table();
    $table->head = array(get_string('element', 'scorm'), get_string('value', 'scorm'));
    $table->align = array('left', 'left');
    $table->wrap = array('nowrap', 'nowrap');
    $table->width = '100%';
    $table->size = array('*', '*');
    $existelements = false;
    $elements = array(
        'min'    => 'score_min',
        'raw'    => 'score_raw',
        'max'    => 'score_max',
        'status' => 'status',
        'time'   => 'total_time');
    $printedelements = array();
    foreach ($elements as $key => $element) {
        if (isset($trackdata->$element)) {
            $existelements = true;
            $printedelements[]=$element;
            $row = array();
            $row[] = get_string($key, 'scorm');
            switch ($key) {
                case 'status':
                    $row[] = $strstatus;
                    break;
                case 'time':
                    $row[] = s(scorm_format_duration($trackdata->$element));
                    break;
                default:
                    $row[] = s($trackdata->$element);
                    break;
            }
            $table->data[] = $row;
        }
    }
    if ($existelements) {
        echo '<h3>'.get_string('general', 'scorm').'</h3>';
        echo html_writer::table($table);
    }
    // Print Interactions data.
    $table = new html_table();
    $table->head = array(
        get_string('identifier', 'scorm'),
        get_string('type', 'scorm'),
        get_string('result', 'scorm'),
        get_string('student_response', 'scorm'));
    $table->align = array('center', 'center', 'center', 'center');
    $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap');
    $table->width = '100%';
    $table->size = array('*', '*', '*', '*', '*');
    $existinteraction = false;
    $i = 0;
    $interactionid = 'cmi.interactions.'.$i.'.id';

    while (isset($trackdata->$interactionid)) {
        $existinteraction = true;
        $printedelements[]=$interactionid;
        $elements = array(
            $interactionid,
            'cmi.interactions.'.$i.'.type',
            'cmi.interactions.'.$i.'.result',
            'cmi.interactions.'.$i.'.learner_response');
        $row = array();
        foreach ($elements as $element) {
            if (isset($trackdata->$element)) {
                $row[] = s($trackdata->$element);
                $printedelements[]=$element;
            } else {
                $row[] = '&nbsp;';
            }
        }
        $table->data[] = $row;
        $i++;
        $interactionid = 'cmi.interactions.'.$i.'.id';
    }
    if ($existinteraction) {
        echo '<h3>'.get_string('interactions', 'scorm').'</h3>';
        echo html_writer::table($table);
    }

    // Print Objectives data.
    $table = new html_table();
    $table->head = array(
        get_string('identifier', 'scorm'),
        get_string('status', 'scorm'),
        get_string('raw', 'scorm'),
        get_string('min', 'scorm'),
        get_string('max', 'scorm'));
    $table->align = array('center', 'center', 'center', 'center', 'center');
    $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
    $table->width = '100%';
    $table->size = array('*', '*', '*', '*', '*');
    $existobjective = false;

    $i = 0;
    $objectiveid = 'cmi.objectives.'.$i.'.id';

    while (isset($trackdata->$objectiveid)) {
        $existobjective = true;
        $printedelements[]=$objectiveid;

        // Merge 2004 and 1.2 SCORM formats.
        if (scorm_version_check($scorm->version, SCORM_13)) {
            $sucstatuskey = 'cmi.objectives.'.$i.'.success_status';
            $progstatuskey = 'cmi.objectives.'.$i.'.progress_measure';
            $compstatuskey = 'cmi.objectives.'.$i.'.completion_status';
            $statuskey = 'cmi.objectives.'.$i.'.status';
            if (isset($trackdata->$sucstatuskey)) {
                $trackdata->$statuskey = $trackdata->$sucstatuskey;
            } else if (isset($trackdata->$progstatuskey)) {
                $trackdata->$statuskey = $trackdata->$progstatuskey;
            } else if (isset($trackdata->$compstatuskey)) {
                $trackdata->$statuskey = $trackdata->$compstatuskey;
            }
        }
        $elements = array(
            $objectiveid,
            'cmi.objectives.'.$i.'.status',
            'cmi.objectives.'.$i.'.score.min',
            'cmi.objectives.'.$i.'.score.raw',
            'cmi.objectives.'.$i.'.score.max');
        $row = array();
        foreach ($elements as $element) {
            if (isset($trackdata->$element)) {
                $row[] = s($trackdata->$element);
                $printedelements[]=$element;
            } else {
                $row[] = '&nbsp;';
            }
        }
        $table->data[] = $row;

        $i++;
        $objectiveid = 'cmi.objectives.'.$i.'.id';
    }
    if ($existobjective) {
        echo '<h3>'.get_string('objectives', 'scorm').'</h3>';
        echo html_writer::table($table);
    }
    $table = new html_table();
    $table->head = array(get_string('element', 'scorm'), get_string('elementdefinition', 'scorm'), get_string('value', 'scorm'));
    $table->align = array('left', 'left');
    $table->wrap = array('nowrap', 'wrap');
    $table->width = '100%';
    $table->size = array('*', '*');

    $existelements = false;

    foreach ($trackdata as $element => $value) {
        if (substr($element, 0, 3) == 'cmi') {
            if (!(in_array ($element, $printedelements))) {
                $existelements = true;
                $row = array();
                $string=false;
                if (stristr($element, '.id') !== false) {
                    $string="interactionsid";
                } else if (stristr($element, '.result') !== false) {
                    $string="interactionsresult";
                } else if (stristr($element, '.student_response') !== false) {
                    $string="interactionsresponse";
                } else if (stristr($element, '.type') !== false) {
                    $string="interactionstype";
                } else if (stristr($element, '.weighting') !== false) {
                    $string="interactionsweight";
                } else if (stristr($element, '.time') !== false) {
                    $string="interactionstime";
                } else if (stristr($element, '.correct_responses._count') !== false) {
                    $string="interactionscorrectcount";
                } else if (stristr($element, '.learner_response') !== false) {
                    $string="interactionslearnerresponse";
                } else if (stristr($element, '.score.min') !== false) {
                    $string="interactionsscoremin";
                } else if (stristr($element, '.score.max') !== false) {
                    $string="interactionsscoremax";
                } else if (stristr($element, '.score.raw') !== false) {
                    $string="interactionsscoreraw";
                } else if (stristr($element, '.latency') !== false) {
                    $string="interactionslatency";
                } else if (stristr($element, '.pattern') !== false) {
                    $string="interactionspattern";
                } else if (stristr($element, '.suspend_data') !== false) {
                    $string="interactionssuspenddata";
                }
                $row[]=$element;
                if (empty($string)) {
                    $row[]=null;
                } else {
                    $row[] = get_string($string, 'scorm');
                }
                if (strpos($element, '_time') === false) {
                    $row[] = s($value);
                } else {
                    $row[] = s(scorm_format_duration($value));
                }
                $table->data[] = $row;
            }
        }
    }
    if ($existelements) {
        echo '<h3>'.get_string('othertracks', 'scorm').'</h3>';
        echo html_writer::table($table);
    }
    echo $OUTPUT->box_end();

}

function scorm_show_user_summary($scorm, $user, $attempt) {
    global $DB, $OUTPUT;

    if ($scoes = $DB->get_records_select('scorm_scoes', "scorm=? ORDER BY id", array($scorm->id))) {
        // Print general score data.
        $table = new html_table();
        $table->head = array(
            get_string('title', 'scorm'),
            get_string('status', 'scorm'),
            get_string('time', 'scorm'),
            get_string('score', 'scorm'),
            '');
        $table->align = array('left', 'center', 'center', 'right', 'left');
        $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
        $table->width = '80%';
        $table->size = array('*', '*', '*', '*', '*');
        foreach ($scoes as $sco) {
            if ($sco->launch!='') {
                $row = array();
                $score = '&nbsp;';
                if ($trackdata = scorm_get_tracks($sco->id, $user->id, $attempt)) {
                    if ($trackdata->score_raw != '') {
                        $score = $trackdata->score_raw;
                    }
                    if ($trackdata->status == '') {
                        if (!empty($trackdata->progress)) {
                            $trackdata->status = $trackdata->progress;
                        } else {
                            $trackdata->status = 'notattempted';
                        }
                    }
                    $detailslink = '<a href="userreport.php?b='.$sco->id.'&amp;user='.$user->id.'&amp;attempt='.$attempt.
                                   '" title="'.get_string('details', 'scorm').'">'.get_string('details', 'scorm').'</a>';
                } else {
                    $trackdata = new stdClass();
                    $trackdata->status = 'notattempted';
                    $trackdata->total_time = '&nbsp;';
                    $detailslink = '&nbsp;';
                }
                $strstatus = get_string($trackdata->status, 'scorm');
                $row[] = '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.
                    $strstatus.'" />&nbsp;'.format_string($sco->title);
                $row[] = get_string($trackdata->status, 'scorm');
                $row[] = scorm_format_duration($trackdata->total_time);
                $row[] = $score;
                $row[] = $detailslink;
            } else {
                $row = array(format_string($sco->title), '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
            }
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }
}