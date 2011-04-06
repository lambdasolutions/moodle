<?php

require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot."/local/kaltura/client/KalturaClient.php");

function kalturaCWSession_setup() {
        global $DB, $USER;
        $partnerId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'partner_id'));
        $serviceUrl = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'server_uri'));
        $secret = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'secret'));
        $uiId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'uploader_regular'));
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = $serviceUrl;
        $client = new KalturaClient($config);
        $ks = $client->session->start($secret,$USER->id, KalturaSessionType::USER, -1, 86400, 'edit:*');
        $client->setKs($ks);
        $url = $serviceUrl."/kcw/ui_conf_id/".$uiId;

        return array('CWurl'=>$url, 'sessionid'=>$ks,'uiId'=>$uiId,'partnerid'=>$partnerId);
}

function kalturaGlobals_js($config) {
    if(empty($config) || !is_array($config)) {
        return false;
    }
    $ret = '<script type="text/javascript">'."\n";
    $ret .= 'if (window.kaltura == undefined) {
                window.kaltura = {};
            }'."\n";
    foreach ($config as $key => $value) {
        $ret .= "window.kaltura.$key = '$value';\n";
    }
    $ret .= '</script>';
    return $ret;
}

function kalturaPlayer_url() {
    global $DB;
    $baseurl = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'server_uri'));
    $partnerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'partner_id'));
    $playerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'player_regular_light'));

    $swfurl = $baseurl;
    $swfurl .= '/kwidget/wid/_'.$partnerid;
    $swfurl .= '/uiconf_id/'.$playerid.'/entry_id/';

    return $swfurl;
}
