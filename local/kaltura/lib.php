<?php

require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot."/local/kaltura/client/KalturaClient.php");


function kalturaClientSession($admin=false) {
    global $DB, $USER;
    $partnerId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'partner_id'));
    $serviceUrl = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'server_uri'));
    $config = new KalturaConfiguration($partnerId);
    $config->serviceUrl = $serviceUrl;
    $client = new KalturaClient($config);

    if ($admin) {
        $secret = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'admin_secret'));
        $ks = $client->session->start($secret, $USER->id, KalturaSessionType::ADMIN, $partnerId);
        $client->setKs($ks);
    }
    else {
        $secret = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'secret'));
        $ks = $client->session->start($secret, $USER->id, KalturaSessionType::USER, $partnerId);
        $client->setKs($ks);
    }
    return $client;
}

function kalturaCWSession_setup($admin=false) {
    global $DB, $USER;
    $client = kalturaClientSession($admin);

    $uploader_type = 'regular';

    $config = $client->getConfig();
    $serviceUrl = $config->serviceUrl;
    $uiId = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'uploader_'.$uploader_type));
    $url = $serviceUrl."/kcw/ui_conf_id/".$uiId;

    return array('url'=>$url, 'params'=>array('sessionId'=>$client->getKs(),'uiConfId'=>$uiId,'partnerId'=>$config->partnerId, 'userId'=>$USER->id));
}

/*function kalturaEditor_setup($entryid) {
    global $DB, $USER;
    $serviceUrl = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'server_uri'));
    $editor = $DB->get_field('config_plugins','value',array('plugin'=>'local_kaltura', 'name'=>'editor'));
    $client = kalturaClientSession();
    $config = $client->getConfig();

    $url = $serviceUrl.'/kse/ui_conf_id/'.$editor;
    $params = array(
                        'entry_id' => $entryid,
                        'kshow_id' => 'entry-'.$entryid,
                        'partner_id' => $config->partnerId,
                        'uid' => $USER->id,
                        'ks' => $client->getKs(),
                        'uiConfId' => $editor,
                         'backF' => 'onSimpleEditorBackClick',
                         'saveF' => 'onSimpleEditorSaveClick'
    );
    return array('url' => $url, 'params' => $params);
}*/

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

function kalturaPlayerUrlBase() {
    global $DB;
    $config = get_config('kalturavideo');
    $baseurl = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'server_uri'));
    $partnerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'partner_id'));

    $player_type = 'regular';

    $playerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'player_'.$player_type.'_'.$config->player_theme));

    $swfurl = $baseurl;
    $swfurl .= '/kwidget/wid/_'.$partnerid;
    $swfurl .= '/ui_conf_id/'.$playerid.'/entry_id/';

    return $swfurl;
}


