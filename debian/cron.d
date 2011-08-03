#
# Cron job for the 'feature-kaltura' Moodle instance
#
4-59/5 * * * * www-data /usr/bin/site-crondispatcher /var/www/feature-kaltura/moodle/admin/cli/cron.php moodle-site-feature-kaltura 28
