<?php

require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot."/local/kaltura/client/KalturaClient.php");

function kaltura_replace_video_js($divid, $buttonid, $inputid) {
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

        $insert = <<<HERE
        <div id="$divid">
            <div class="yui3-widget-hd"></div>
            <div class="yui3-widget-bd"></div>
            <div class="yui3-widget-ft"></div>
        </div>
        <script type="text/javascript">
        /* <![CDATA[ */
        YUI().use("swf","node", "overlay", function(Y) {
            var div = Y.one("div#$divid");
            div.setStyles({width:800, height: 600, display: 'none'});

            var swf = new Y.SWF("#$divid .yui3-widget-bd", "$url", {  version: "9.0.115",
                            fixedAttributes: {wmode: "opaque",
                                              allowScriptAccess:"always",
                                              allowNetworking:"all",
                                              allowFullScreen: "TRUE"},
                            flashVars: {partnerId: "$partnerId",
                                        userId: "$USER->id",
                                        sessionId: "$ks",
                                        uiConfId: "$uiId",
                                        afterAddEntry: "onContributionWizardAfterAddEntry",
                                        close: "onContributionWizardClose",
                                        kShowId: -2,
                                        terms_of_use: "http://corp.kaltura.com/tandc"
                            }
            });

            var overlay = new Y.Overlay({
                srcNode: "#$divid",
                centered: true
            });

            Y.one('input[name=$buttonid]').on('click',function(e) {
                e.preventDefault();
                Y.one('#$divid').setStyles({display: 'block'});
                overlay.set("centered", true);
                overlay.render();
                return false;
            });
         });

        function onContributionWizardAfterAddEntry(param) {
            var entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
            YUI().use('node', function(Y) {
                Y.one('$inputid').set('value',entryId);
            });
        }

        function onContributionWizardClose(modified) {
                YUI().use('node', function(Y){
                    Y.one('#$divid').setStyles({display: 'none'});
                });

        }
        /* ]]> */
        </script>
HERE;

    return $insert;
}

function kaltura_play_video_js($divid, $entry_id, $form_id=null) {
    global $DB;
    $baseurl = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'server_uri'));
    $partnerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'partner_id'));
    $playerid = $DB->get_field('config_plugins','value',array('plugin' => 'local_kaltura', 'name'=>'player_regular_light'));

    $swfurl = $baseurl;
    $swfurl .= '/kwidget/wid/_'.$partnerid;
    $swfurl .= '/uiconf_id/'.$playerid.'/entry_id/'.$entry_id;

    $ret = <<<JAVASCRIPT
    <script type="text/javascript">
    function initialisevideo(entry_id) {
        YUI().use('swf', 'node',function(Y){
            var div = Y.one('#$divid');
            div.setStyles({
                width:400,
                height:290
            });

            var url = '$swfurl';
JAVASCRIPT;
    if (empty($entry_id) && !empty($form_id)) {
        $ret .= <<<JAVASCRIPT
            if (entry_id === '' || entry_id === undefined) {
                var fragment = Y.one('$form_id').get('value');
                if (fragment !== '' && fragment !== undefined) {
                    url += fragment;
                }
            } else {
                url += entry_id;
            }

JAVASCRIPT;
    }
    $ret .= <<<JAVASCRIPT
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

            var kaltura_player = new Y.SWF('#$divid', url, params);

        });
    }
    initialisevideo('$entry_id');
    </script>
JAVASCRIPT;

    return $ret;
}
