<?php
require_once($CFG->dirroot.'/local/kaltura/client/kaltura_settings.php');
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClientBase.php');
require_once($CFG->dirroot.'/local/kaltura/client/KalturaClient.php');
require_once($CFG->dirroot.'/local/kaltura/client/kaltura_logger.php');
require_once($CFG->dirroot.'/local/kaltura/client/kaltura_helpers.php');
require_once($CFG->dirroot.'/local/kaltura/jsportal.php');


class KalturaPlayerSize
{
    const LARGE = 1;
    const SMALL = 2;
    const CUSTOM = 3;
}

class kaltura_entry
{
  public $id = 0;
  public $entry_id = "";
  public $dimensions = KalturaAspectRatioType::ASPECT_4_3;
  public $size = KalturaPlayerSize::LARGE;
  public $custom_width = 0;
  public $design = "light";
  public $title = "";
  public $context = "";
  public $entry_type = KalturaEntryType::MEDIA_CLIP;
  public $media_type = KalturaMediaType::VIDEO;
}

function kaltura_get_types() //this prevent from the mod/kaltua itself to appear as an activity
{
  return array();
}

function get_cw_wizard($div, $width, $height, $type)
{
  $client = KalturaHelpers::getKalturaClient();
  $swfUrl = KalturaHelpers::getContributionWizardUrl($type);
  $flashVarsStr = KalturaHelpers::flashVarsToString(KalturaHelpers::getContributionWizardFlashVars($client->getKS(),$type));

  $flash_embed = '
    <div id="' . $div . '"></div>
    <script type="text/javascript">
      var kso = new SWFObject("'. $swfUrl .'", "KalturaCW", "'. $width .'", "'. $height .'", "9", "#ffffff");
      kso.addParam("flashVars", "'. $flashVarsStr .'");
      kso.addParam("allowScriptAccess", "always");
      kso.addParam("allowFullScreen", "TRUE");
      kso.addParam("allowNetworking", "all");
      if(kso.installedVer.major >= 9) {
        kso.write("' . $div . '");
      } else {
        document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
      }
    </script>
  ';

    return $flash_embed;
}

function get_se_wizard($div, $width, $height,$entryId)
{
  $params = "''";
  $url = "''";
  $platformUser = "\"" . KalturaHelpers::getSessionUser()->userId . "\"";
  $kalturaSecret = KalturaHelpers::getPlatformKey("secret","");

  if ($kalturaSecret != null && strlen($kalturaSecret) > 0)
  {
      try
	  {
		  $kClient = new KalturaClient(KalturaHelpers::getServiceConfiguration());
		  $kalturaUser = KalturaHelpers::getSessionUser()->userId;
		  $ksId = $kClient -> session -> start($kalturaSecret, $kalturaUser, KalturaSessionType::USER, null, 86400, "*");
		  $kClient -> setKs($ksId );
		  $url = KalturaHelpers::getSimpleEditorUrl(KalturaHelpers::getPlatformKey("editor",null));
		  $params =  KalturaHelpers::flashVarsToString(KalturaHelpers::getSimpleEditorFlashVars($ksId,$entryId, "entry", ""));
	  }
     catch(Exception $exp)
	  {
		  $flash_embed = $exp->getMessage();
	  }
    $flash_embed = '
	    <div id="'. $div .'" style="width:'.$width.'px;height:'.$height.';">
        <script type="text/javascript">
            var kso = new SWFObject("'. $url .'", "KalturaSW", "'. $width .'", "'. $height .'", "9", "#ffffff");
            kso.addParam("flashVars", "'. $params .'");
            kso.addParam("allowScriptAccess", "always");
            kso.addParam("allowFullScreen", "TRUE");
            kso.addParam("allowNetworking", "all");
            if(kso.installedVer.major >= 9) {
                kso.write("' . $div . '");
            } else {
                document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
            }
        </script>
        ';

    return $flash_embed;
  }
}

function get_se_js_functions($thumbUrl) {
   global $CFG, $USER;

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot'  => $CFG->wwwroot,
                               'ssskey'   => $USER->sesskey,
                               'userid'   => $USER->id,
                               'thumburl' => $thumbUrl,
                             ),
                             false,
                             false);

    return '';
}

function get_cw_js_functions($type, $divCW, $updateField, $divProps='') {
    global $CFG, $USER, $PAGE;

    $PAGE->requires->js($CFG->wwwroot . '/local/kaltura/js/kaltura.lib.js');

    $kalturaprotal = new kaltura_jsportal();
    $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot'        => $CFG->wwwroot,
                               'ssskey'         => $USER->sesskey,
                               'userid'         => $USER->id,
                               'type'           => $type,
                               'divcW'          => $divCW,
                               'updatefield'    => $updateField,
                               'divprops'       => $divProps,
                               'entrymixtype'   => KalturaEntryType::MIX,
                               'entrymediatype' => KalturaEntryType::MEDIA_CLIP
                             ),
                             false,
                             true);

   $javascript = '<script type="text/javascript">
    type = ' . $type . ';
    local_entry_id = "";

    function set_page_entry(entry)
    {
        local_entry_id = entry;
    }

    function get_page_entry()
    {
        return local_entry_id;
    }
    </script>';


    return $javascript . $output;
}

function get_cw_props_player($div, $width, $height)
{
//  $client = KalturaHelpers::getKalturaClient();
  $playerId = KalturaSettings_PLAY_NOMIX_UICONF_ID;
  $players = KalturaHelpers::getPlayers(KalturaEntryType::MEDIA_CLIP);
  $partnerId= KalturaHelpers::getPlatformKey("partner_id","0");
  $swfUrl = KalturaHelpers::getSwfUrlForWidget($partnerId);
  $swfUrl .=  "/uiconf_id/";
//  $flashVarsStr = KalturaHelpers::flashVarsToString(KalturaHelpers::getKalturaPlayerFlashVars($client->getKS(), -1, "#ReplaceME#"));

  $flash_embed = '
    <script type="text/javascript">
    function show_entry_player(entryId, design)
    {
      var playreId ='.KalturaSettings_PLAY_REGULAR_LIGHT_UICONF_ID.';
      var ts = new Date().getTime() + "0000";
      switch (design)
      {';
      foreach($players as $option_player_name => $option_player_id)
      {
        $flash_embed .= '      case "' . $option_player_name . '": playreId=' . $option_player_id . ';break;';
      }
      $flash_embed .= '      }
      var kso = new SWFObject("'. $swfUrl .'" + playreId + "/entry_id/" + entryId + "/ts/" + ts, "' . $div. '", "'. $width .'", "'. $height .'", "9", "#ffffff");
      kso.addParam("allowScriptAccess", "always");
      kso.addParam("allowFullScreen", "TRUE");
      kso.addParam("allowNetworking", "all");
	  kso.addParam("flashVars", "clientTag=cache_st:" + ts);
      if(kso.installedVer.major >= 9) {
        kso.write("' . $div . '");
      } else {
        document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
      }
    }
    </script>
  ';

    return $flash_embed;
}

function get_cw_properties_pane($entry,$type)
{
  $designes = KalturaHelpers::getDesigns($type);
  $javascript= '
    <style type="text/css">
        #slctDesign
        {
            width: 121px;
        }
        #inpCustomWidth
        {
            width: 60px;
        }
        #inpTitle
        {
            width: 238px;
        }
    </style>

    <div id="divClipProps">
        <div id="divClip">
        </div>
        <div id="divUserSlected">
            <p>
                <span style="font-weight:bold;">' . get_string("title", "kaltura") . '</span>&nbsp;<input id="inpTitle" title="Title:" type="text" value="' . $entry->title . '"/></p>
                <script type="text/javascript">
                if (document.getElementById("inpTitle").value == "")
                {
                  document.getElementById("inpTitle").value = get_field("id_name");
                }
                </script>
            <div id="divRefresh">
                <span style="font-weight:bold;">' . get_string('playerrefreshscreen', 'kaltura') . '</span>
                <input type="submit" name="refreshpage" onclick="change_entry_player();" value="'. get_string('playerrefresh', 'kaltura') .'"/>
            </div>
            <div id="divDesign">
                <span style="font-weight:bold;">' . get_string("playerdesign", "kaltura") . '</span>
                <select id="slctDesign" name="slctDesign" onchange="change_entry_player();">';
                    foreach ($designes as $desKey => $desValue)
                    {
                      $javascript .= '<option value="' . $desKey . '"' . ( $entry->design == $desKey ? 'selected' : '' ) . '>' . $desValue .'</option>';
                    }
        $javascript .= '         </select>
            </div>
            <div id="divDim">
                <table><tr><td valign="top" style="font-weight:bold;">' . get_string("playerdimensions", "kaltura") . '</td><td valign="top">
                    <input id="dimNorm" ' . ( $entry->dimensions == KalturaAspectRatioType::ASPECT_4_3 ? 'checked="checked"' : '' ) . ' name="grpDimension" type="radio" onclick="update_field(\'id_dimensions\',\'' . KalturaAspectRatioType::ASPECT_4_3 .'\', false, \'\');document.getElementById(\'lrgPlayer\').innerHTML=\'365\';document.getElementById(\'smlPlayer\').innerHTML=\'260\'" />' . get_string("normal", "kaltura") . '
                    <p><input id="dimWide" ' . ( $entry->dimensions == KalturaAspectRatioType::ASPECT_16_9 ? 'checked="checked"' : '' ) . ' name="grpDimension" type="radio" onclick="update_field(\'id_dimensions\',\'' .KalturaAspectRatioType::ASPECT_16_9 .'\', false, \'\');document.getElementById(\'lrgPlayer\').innerHTML=\'290\';document.getElementById(\'smlPlayer\').innerHTML=\'211\'" />' . get_string("widescreen", "kaltura") . '</p></td></tr></table>
             </div>
            <div id="divSize">
                <table><tr><td valign="top" style="font-weight:bold;">' . get_string("playersize", "kaltura") . '</td><td valign="top">

                    <input id="sizeLarge" ' . ( $entry->size == KalturaPlayerSize::LARGE ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio" onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::LARGE .'\', false, \'\')" />' . get_string("largeplayer", "kaltura") . '
                <p>
                    <input id="sizeSmall" ' . ( $entry->size == KalturaPlayerSize::SMALL ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio"  onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::SMALL .'\', false, \'\')" />' . get_string("smallplayer", "kaltura") . '</p>
                <p>
                    <input id="sizeCustom" ' . ( $entry->size == KalturaPlayerSize::CUSTOM ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio" onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::CUSTOM .'\', false, \'\')" />' . get_string("customwidth", "kaltura")
                    . '&nbsp;<input id="inpCustomWidth" type="text" value="' . ( $entry->size == KalturaPlayerSize::CUSTOM ?  $entry->custom_width : '' ) . '" onfocus="document.getElementById(\'sizeCustom\').checked=true;update_field(\'id_size\',\'' . KalturaPlayerSize::CUSTOM .'\', false, \'\')"/></p></td></tr></table>
            </div>
        </div>
        <div id="divButtons">
                <input id="btnInserResource" type="button" value="' . get_string("insertintopost", "kaltura") . '" onclick="insert_into_post();"/> <input id="btnCancelResource" type="button" value="' . get_string("cancelpost", "kaltura") . '" onclick="setTimeout(\'window.parent.kalturaCloseModalBox();\',0);"/>
        </div>
    </div>';

    return $javascript;
}

function get_wait_image($div, $field)
{
    global $CFG, $USER, $PAGE;

    $PAGE->requires->js('/local/kaltura/js/kaltura.main.js');
    $PAGE->requires->js('/local/kaltura/js/kaltura.lib.js');

   $kalturaprotal = new kaltura_jsportal();
   $javascript = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                               'ssskey' => $USER->sesskey,
                               'userid' => $USER->id,
                               'param_div' => $div,
                               'param_field' => $field,
                               'videoconversion' => get_string('videoconversion', 'resource_kalturavideo'),
                               'clickhere' => get_string('clickhere', 'resource_kalturavideo'),
                               'convcheckdone' => get_string('convcheckdone', 'resource_kalturavideo'),
                             ),
                             '',
                             true);

   return $javascript;
}

function embed_kaltura($entryId, $width, $height, $type, $design, $show_links = false)
{
  global $CFG, $PAGE;
//  $client = KalturaHelpers::getKalturaClient();
  $playerId = KalturaHelpers::getPlayer($type, $design);
  $partnerId= KalturaHelpers::getPlatformKey("partner_id","0");
  $swfUrl = KalturaHelpers::getSwfUrlForWidget($partnerId);
  $swfUrl .=  "/uiconf_id/$playerId/entry_id/" . $entryId;
  $flashVarsStr = KalturaHelpers::flashVarsToString(KalturaHelpers::getKalturaPlayerFlashVars(/*$client->getKS()*/ "", -1, $entryId));
  $div_id = "kaltura_wrapper_" . $entryId;
  $kaltura_poweredby = '<div style="width:' . $width . 'px;padding-top:6px;font-size:9px;text-align:right"' .
                       '><a href="http://corp.kaltura.com/video_platform/video_publishing" target="_blank">Video Player' .
                       '</a> by <a href="http://corp.kaltura.com" target="_blank">Kaltura</a></div>';

  if ($show_links == false) {

    $kaltura_poweredby = '';
  }

   $PAGE->requires->js('/local/kaltura/js/kaltura.main.js');
   $PAGE->requires->js('/local/kaltura/js/kaltura.lib.js');

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                             ),
                             false,
                             true);

  $align = '';
  $custom_style = '';
  $links = '<a href="http://corp.kaltura.com/solutions/education_technology_video">education video</a><a href="http://www.kaltura.com">video platform</a><a href="http://corp.kaltura.com/video_platform/video_streaming">video streaming</a><a href="http://corp.kaltura.com/video_platform /video_hosting">video hosting</a>';

    $html = $output . '
      <div id="'. $div_id .'" class="kaltura_wrapper" style="'. $align . $custom_style .'"'. /*$embed_options['js_events'] . */'>'. $links .'</div>'. $kaltura_poweredby;
      $html .= '<script type="text/javascript">';
      $html .= '        var kaltura_swf = new SWFObject("'. $swfUrl .'", "player_'. $entryId .'", "'. $width .'", "'. $height .'", "9", "#ffffff");';
      $html .= '        kaltura_swf.addParam("wmode", "opaque");';
      $html .= '        kaltura_swf.addParam("allowScriptAccess", "always");';
      $html .= '        kaltura_swf.addParam("allowFullScreen", "TRUE");';
      $html .= '        kaltura_swf.addParam("allowNetworking", "all");';
      $html .= '        kaltura_swf.addParam("flashVars", "'. $flashVarsStr .'");';
      $html .= '        kaltura_swf.write("'. $div_id .'");';
      $html .= '        </script>';

    return $html;
}


function embed_kswfdoc($entryId, $width, $height, $context_id) {
  global $CFG, $USER, $PAGE;
  $client = KalturaHelpers::getKalturaClient();
  $kswf_player = KalturaHelpers::getPlatformKey("video_presentation",KalturaSettings_PLAY_VIDEO_PRESENTATION_UICONF_ID);
  $partnerId= KalturaHelpers::getPlatformKey("partner_id","0");
  $swfUrl = KalturaHelpers::getSwfUrlForWidget($partnerId);
  $div_id = "kaltura_wrapper_" . $entryId;
  $kaltura_poweredby = '';
  $align = 'margin-top:-20px';
  $custom_style = '';
  $links = '';
  $config = $client -> getConfig();


  $context = get_context_instance(CONTEXT_COURSE, $context_id);
	if (has_capability('moodle/course:manageactivities',$context)) //check if admin of this widget
	{
		//is admin
		$kc = KalturaHelpers::getKalturaClient(0,'edit:'.$_GET['entry']);
		$adminvars = '"&adminMode=true"+"&partnerid='.$config->partnerId.'"+"&subpid='.$config->partnerId*100 . '"+"&ks='.$client->getKs().'"+"&uid='.$USER->id.'"';
	} else {
        //is student
        $kc = KalturaHelpers::getKalturaClient(0,0);
        $adminvars = '"&adminMode=false"+"&partnerid='.$config->partnerId.'"+"&subpid='.$config->partnerId*100 . '"+"&ks='.$client->getKs().'"+"&uid='.$USER->id.'"';
	}

    $host = 'www.kaltura.com';
	if ($client -> getConfig() -> serviceUrl != 'http://www.kaltura.com')
	{
	  $host = str_replace('http://', '', $kc->config->serviceUrl);
	}
	$flashVarsStr = '"host='.$host.'"+'.$adminvars.'+"&debugMode=1" +"&kshowId=-1"+"&pd_sync_entry='.$entryId.'"';

   $PAGE->requires->js($CFG->wwwroot . '/local/kaltura/js/kaltura.main.js');
   $PAGE->requires->js($CFG->wwwroot . '/local/kaltura/js/kaltura.lib.js');

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                             ),
                             false,
                             true);

    $html = $output . '
      <div id="'. $div_id .'" class="kaltura_wrapper" style="'. $align . $custom_style .'"'. $embed_options['js_events'] .'>'. $links .'</div>'. $kaltura_poweredby;
      $html .= '<script type="text/javascript">';
      $html .= '        var kaltura_swf = new SWFObject("'.$swfUrl.'/uiconf_id/'.$kswf_player.'", "' .$div_id . '", "'. $width .'", "'. $height .'", "9", "#ffffff");';
      $html .= '        kaltura_swf.addParam("flashVars", '. $flashVarsStr .');';
      $html .= '        kaltura_swf.addParam("wmode", "opaque");';
      $html .= '        kaltura_swf.addParam("allowScriptAccess", "always");';
      $html .= '        kaltura_swf.addParam("allowFullScreen", "TRUE");';
      $html .= '        kaltura_swf.addParam("allowNetworking", "all");';
      $html .= '        if(kaltura_swf.installedVer.major >= 9) {';
      $html .= '          kaltura_swf.write("'. $div_id .'");';
      $html .= '        } else {';
      $html .= '          document.getElementById("'. $div_id .'").innerHTML = "Flash player version 9 and above is required. <a href=\'http://get.adobe.com/flashplayer/\'>Upgrade your flash version</a>";';
      $html .= '        }</script>';

    return $html;
}

function get_height($entry)
{
  if ($entry->dimensions == KalturaAspectRatioType::ASPECT_4_3)
  {
    switch($entry->size)
    {
      case KalturaPlayerSize::LARGE:
        return 365;
        break;
      case KalturaPlayerSize::SMALL:
        return 260;
        break;
      case KalturaPlayerSize::CUSTOM:
        return $entry->custom_width*3/4 + 65;
        break;
      default:
        return 365;
       break;
    }
  }
  else
  {
    switch($entry->size)
    {
      case KalturaPlayerSize::LARGE:
        return 290;
       break;
      case KalturaPlayerSize::SMALL:
        return 211;
        break;
      case KalturaPlayerSize::CUSTOM:
        return $entry->custom_width*9/16 + 65;
        break;
      default:
        return 290;
        break;
    }

  }
}

function get_width($entry)
{
    switch($entry->size)
    {
      case KalturaPlayerSize::LARGE:
        return 400;
        break;
      case KalturaPlayerSize::SMALL:
        return 260;
        break;
      case KalturaPlayerSize::CUSTOM:
        return $entry->custom_width;
        break;
      default:
        return 400;
        break;
    }
}

function kaltura_process_options(&$config)
{
    
}

function kaltura_user_complete($course, $user, $mod, $instance)
{
/// Print a detailed representation of what a  user has done with
/// a given particular instance of this module, for user activity reports.

    return true;
}

function kaltura_user_outline()
{
/// Return a small object with summary information about what a
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    $return = NULL;
    return $return;
}

function kaltura_cron()
{
    return true;

}

function kaltura_print_recent_activity($course, $viewfullnames, $timestart)
{
/// Given a course and a date, prints a summary of all kaltura resources past and present
/// This function is called from course/lib.php: print_recent_activity()

    return false;
}

function kaltura_uninstall()
{
    delete_records('log_display','module',"kaltura");
    delete_records('config_plugins','plugin',"local_kaltura");
    drop_table('kaltura_entries');
}
?>
