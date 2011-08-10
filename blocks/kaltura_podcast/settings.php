<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
//settings to use to link to course meta stuff.
    $settings->add(new admin_setting_configtext('block_kaltura_podcast_meta_category', get_string('podcastmetacategory', 'block_kaltura_podcast'),
                       get_string('podcastmetacategory_desc', 'block_kaltura_podcast'), 'kaltura_podcast_cat', PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('block_kaltura_podcast_player',
        get_string('player', 'block_kaltura_podcast'), get_string('player_desc', 'block_kaltura_podcast'), '1466432', PARAM_TEXT, 8));

    //setting for player to use when click link in block
    $settings->add(new admin_setting_configtext('block_kaltura_podcast_content_flavor',
        get_string('contentflavor', 'block_kaltura_podcast'), get_string('contentflavor_desc', 'block_kaltura_podcast'), '6', PARAM_INT, 8));

    //settings for Itunes feed.
    $settings->add(new admin_setting_configtext('block_kaltura_podcast_landing_page', get_string('landingpage', 'block_kaltura_podcast'),
                       get_string('landingpage_desc', 'block_kaltura_podcast'), '', PARAM_URL));

    $settings->add(new admin_setting_configtext('block_kaltura_podcast_feed_owner_name', get_string('feedownername', 'block_kaltura_podcast'),
                       get_string('feedownername_desc', 'block_kaltura_podcast'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_kaltura_podcast_feed_owner_email', get_string('feedowneremail', 'block_kaltura_podcast'),
                       get_string('feedowneremail_desc', 'block_kaltura_podcast'), '', PARAM_EMAIL));

    $settings->add(new admin_setting_configtext('block_kaltura_podcast_maxfeed', get_string('maxfeed','block_kaltura_podcast'),
                       get_string('maxfeed_desc','block_kaltura_podcast'),'50',PARAM_INT));
}

