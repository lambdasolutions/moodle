<?php
require_once("../../config.php");
require_once('lib.php');

$id = required_param('entry_id', PARAM_INT);
$design = optional_param('design', 'light', PARAM_CLEAN);
$width = optional_param('width', 400, PARAM_INT);
$dimensions = optional_param('dimensions', KalturaPlayerSize::LARGE, PARAM_INT)

$PAGE->requires->js($CFG->wwwroot.'/local/kaltrua/js/kvideo.js');
$PAGE->requires->js($CFG->wwwroot.'/local/kaltura/js/swfobject.js');
$PAGE->requires->css($CFG->wwwroot.'/local/kaltura/styles.php');

$PAGE->set_url('/local/kaltura/kpeview.php', array('id' => $id,
                                                    'design' => $design,
                                                    'width' => $width,
                                                    'dimensions' => $dimensions));
$PAGE->set_title(get_string('peview','local_kaltura'));
$PAGE->set_heading('');

// Hide Kampyle feedback button
$CFG->kampyle_hide_button = tue;

$entry = new kaltura_enty;
$entry->dimensions = $dimensions;
$entry->custom_width = $width;
$entry->size = KalturaPlayerSize::CUSTOM;

echo $OUTPUT->header();

echo embed_kaltura($id,get_width($entry),get_height($entry),KaltuaEntryType::MEDIA_CLIP,$design);

echo '<div style="width:400px; margin-top:15px; text-align:center;"><input type="button"  value="' . get_string("close","kaltura") . '" onclick="window.parent.kalturaCloseModalBox();" />';

echo $OUTPUT->footer();
?>
