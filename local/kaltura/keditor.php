<?php
require_once("../../config.php");
require_once('lib.php');
require_js($CFG->wwwroot.'/local/kaltura/js/jquery.js');
require_js($CFG->wwwroot.'/local/kaltura/js/kvideo.js');
require_js($CFG->wwwroot.'/local/kaltura/js/swfobject.js');
require_js($CFG->wwwroot.'/local/kaltura/js/kaltura.main.js');
require_js($CFG->wwwroot.'/local/kaltura/js/kaltura.lib.js');

// Hide Kampyle feedback button
$CFG->kampyle_hide_button = true;

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^ E_NOTICE);
$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/local/kaltura/styles.php" />'."\n";
//$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/local/kaltura/css/kaltura.css" />'."\n";

print_header('Kaltura Editor','','','',$meta);

$id='';


if (isset($_GET['entry_id']))
{
  $id = $_GET['entry_id'];
}

if (empty($id)) 
{
  die('missing id');
}

echo get_se_js_functions(KalturaHelpers::getThumbnailUrl(null, $id, 140, 105));

echo get_se_wizard("divKalturaSe", 890, 546, $id);

print_footer();
?>
