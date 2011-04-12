<?php

require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot."/local/kaltura/client/KalturaClient.php");

if (empty($config)) {
    $config = get_config('kalturavideo');
}

function kalturaClientSession() {
    global $DB, $USER, $config;
    $partnerId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'partner_id'));
    $serviceUrl = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'server_uri'));
    $secret = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'secret'));
    $config = new KalturaConfiguration($partnerId);
    $config->serviceUrl = $serviceUrl;
    $client = new KalturaClient($config);
    $ks = $client->session->start($secret,$USER->id, KalturaSessionType::USER, -1, 86400, 'edit:*');
    $client->setKs($ks);
    return $client;
}

function kalturaCWSession_setup($mix=false) {
    global $DB, $USER, $config;
    $partnerId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'partner_id'));
    $serviceUrl = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'server_uri'));
    $secret = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'secret'));

    $uploader_type = 'regular';
    if ($mix) {
        $uploader_type = 'mix';
    }

    $uiId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'uploader_'.$uploader_type));
    $config = new KalturaConfiguration($partnerId);
    $config->serviceUrl = $serviceUrl;
    $client = new KalturaClient($config);
    $ks = $client->session->start($secret,$USER->id, KalturaSessionType::USER, -1, 86400, 'edit:*');
    $client->setKs($ks);
    $url = $serviceUrl."/kcw/ui_conf_id/".$uiId;

    return array('url'=>$url, 'params'=>array('sessionid'=>$ks,'uiId'=>$uiId,'partnerid'=>$partnerId, 'userid'=>$USER->id));
}

function kalturaEditor_setup() {
    global $DB, $USER, $config;
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

function kalturaPlayerUrlBase($mix=false) {
    global $DB, $config;
    $baseurl = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'server_uri'));
    $partnerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'partner_id'));

    $player_type = 'regular';
    $player_theme = $config->player_theme;
    if ($mix) {
        $player_type = 'mix';
        $player_theme = $config->editor_theme;
    }


    $playerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'player_'.$player_type.'_'.$player_theme));

    $swfurl = $baseurl;
    $swfurl .= '/kwidget/wid/_'.$partnerid;
    $swfurl .= '/uiconf_id/'.$playerid.'/entry_id/';

    return $swfurl;
}


