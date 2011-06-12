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
 *
 * @package    local
 * @subpackage kaltura
 * @copyright  2011 Catalyst IT <http://catalyst.net.nz/>
 * @author     Brett Wilkins <brett@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("lib.php");
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');

$id         = optional_param('id', 0, PARAM_INT);
$action     = optional_param('action', '', PARAM_TAGLIST);
$entryid    = optional_param('entryid', '', PARAM_CLEAN);
$docurl     = optional_param('docurl','', PARAM_TEXT);

require_login();

$returndata = array();

$admin = false;
if (!empty($id)) {
    $cm = get_coursemodule_from_id('', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    if (has_capability('moodle/course:manageactivities', $context)) {
        $admin = true;
    }
}

switch ($action) {
    case 'playerurl':
        $entry = null;
        if (!empty($id)) {
            $cm = get_coursemodule_from_id('kalturavideo', $id, 0, false, MUST_EXIST);
            $entry = $DB->get_record('kalturavideo', array('id'=>$cm->instance), '*', MUST_EXIST);
        }
        else if (!empty($entryid)) {
            $entry = new stdClass;
            $entry->kalturavideo = $entryid;
        }
        else {
            //what are we displaying? :o
            break;
        }

        $url = kalturaPlayerUrlBase();
        $returndata = array('url' => $url.$entry->kalturavideo, $params = array());
        break;

    case 'cwurl':
        $returndata = kalturaCWSession_setup();
        break;

    case 'convertppt':
        if (!empty($entryid)) {
            require_once($CFG->dirroot.'/local/kaltura/client/KalturaPlugins/KalturaDocumentClientPlugin.php');
            $client = kalturaClientSession();
            $client->document = new KalturaDocumentsService($client);
            $result = $client->document->convertPptToSwf($entryid);
            $returndata = array('entryid' => $entryid, 'url' => $result);
        }
        break;

    case 'swfdocurl':
        if (!empty($entryid) && !empty($docid)) {
            $client = kalturaClientSession($admin);
            $config = $client->getConfig();


            $path = urldecode($docurl);

            if (strpos($config->serviceUrl, 'www.kaltura.com') &&
                strpos($path, 'www.kaltura.com'))
            {
                $path = str_replace('www.kaltura.com','cdn.kaltura.com',$path);
            }

            $xml = '<sync><video><entryId>'.$entryid.'</entryId></video><slide><path>'.$path.'</path></slide>';
            $xml .= '<times></times></sync>';

            $entry = new KalturaDataEntry();
            $entry->dataContent = $xml;
            $entry->mediaType = KalturaEntryType::DOCUMENT;
            $result = $client->data->add($entry);

            $uiconf = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'video_presentation'));
            $url = $config->serviceUrl.'/kwidget/wid/_'.$config->partnerId.'/ui_conf_id/'.$uiconf;

            $returndata = array(
                'url' => $url,
                'params' => array(
                    'sessionId' => $client->getKs(),
                    'adminMode' => $admin,
                    'partnerId' => $config->partnerId,
                    'subpid' => $config->partnerId*100,
                    'userId' => $USER->id,
                    'kShowId' => -1,
                    'pd_sync_entry' => $result->id,
                    'host' => str_replace('http://', '', $config->serviceUrl)
                )
            );
        }
        break;

        case 'swfdocuploader':
            $client = kalturaClientSession();
            $config = $client->getConfig();
            $url = $config->serviceUrl.'/kcw/ui_conf_id/4391521';
            $returndata = array(
                                                'url' => $url,
                                                'params' => array(
                                                    'sessionid' => $client->getKs(),
                                                    'partnerid' => $config->partnerId,
                                                    'userid' => $USER->id,
                                                    'uuid' => '4391521',
                                                )
                                            );
            break;

        case 'doccheckstatus':
            require_once($CFG->dirroot.'/local/kaltura/client/KalturaPlugins/KalturaDocumentClientPlugin.php');
            $client = kalturaClientSession();
            $client->document = new KalturaDocumentsService($client);
            $data = $client->document->get($entryid);
            if ($data->status == KalturaEntryStatus::READY) {
                $returndata = array('status' => true,
                                    'thumbnail' => $data->thumbnailUrl);
            }
            else {
                $returndata = array('status' => false);
            }
            break;

    default:
        break;
}

header('Content-Type: application/json');

echo json_encode($returndata);
?>
