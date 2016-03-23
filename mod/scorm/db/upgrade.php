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
 * Upgrade script for the scorm module.
 *
 * @package    mod_scorm
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_scorm_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014072500) {

        // Define field autocommit to be added to scorm.
        $table = new xmldb_table('scorm');
        $field = new xmldb_field('autocommit', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'displayactivityname');

        // Conditionally launch add field autocommit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Scorm savepoint reached.
        upgrade_mod_savepoint(true, 2014072500, 'scorm');
    }

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015031800) {

        // Check to see if this site has any AICC packages - if so set the aiccuserid to pass the username
        // so that the data remains consistent with existing packages.
        $alreadyset = $DB->record_exists('config_plugins', array('plugin' => 'scorm', 'name' => 'aiccuserid'));
        if (!$alreadyset) {
            $hasaicc = $DB->record_exists('scorm', array('version' => 'AICC'));
            if ($hasaicc) {
                set_config('aiccuserid', 0, 'scorm');
            } else {
                // We set the config value to hide this from upgrades as most users will not know what AICC is anyway.
                set_config('aiccuserid', 1, 'scorm');
            }
        }
        // Scorm savepoint reached.
        upgrade_mod_savepoint(true, 2015031800, 'scorm');
    }

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015091400) {
        $table = new xmldb_table('scorm');

        // Changing the default of field forcecompleted on table scorm to 0.
        $field = new xmldb_field('forcecompleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'maxattempt');
        // Launch change of default for field forcecompleted.
        $dbman->change_field_default($table, $field);

        // Changing the default of field displaycoursestructure on table scorm to 0.
        $field = new xmldb_field('displaycoursestructure', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'displayattemptstatus');
        // Launch change of default for field displaycoursestructure.
        $dbman->change_field_default($table, $field);

        // Scorm savepoint reached.
        upgrade_mod_savepoint(true, 2015091400, 'scorm');
    }

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    // MDL-50620 Add mastery override option.
    if ($oldversion < 2016021000) {
        $table = new xmldb_table('scorm');

        $field = new xmldb_field('masteryoverride', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'lastattemptlock');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016021000, 'scorm');
    }

    // New table structure.
    if ($oldversion < 2016032300) {
        // Define table scorm_scoes_attempt to be created.
        $table = new xmldb_table('scorm_scoes_attempt');

        // Adding fields to table scorm_scoes_attempt.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scormid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table scorm_scoes_attempt.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('user', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('scorm', XMLDB_KEY_FOREIGN, array('scormid'), 'scorm', array('id'));
        $table->add_key('scoe', XMLDB_KEY_FOREIGN, array('scoid'), 'scorm_scoes', array('id'));

        // Conditionally launch create table for scorm_scoes_attempt.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table scorm_scoes_element to be created.
        $table = new xmldb_table('scorm_scoes_element');

        // Adding fields to table scorm_scoes_element.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('element', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table scorm_scoes_element.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table scorm_scoes_element.
        $table->add_index('element', XMLDB_INDEX_UNIQUE, array('element'));

        // Conditionally launch create table for scorm_scoes_element.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table scorm_scoes_value to be created.
        $table = new xmldb_table('scorm_scoes_value');

        // Adding fields to table scorm_scoes_value.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('elementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table scorm_scoes_value.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('attempt', XMLDB_KEY_FOREIGN, array('attemptid'), 'scorm_scoes_attempt', array('id'));
        $table->add_key('element', XMLDB_KEY_FOREIGN, array('elementid'), 'scorm_scoes_element', array('id'));

        // Conditionally launch create table for scorm_scoes_value.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2016032300, 'scorm');
    }

    if ($oldversion < 2016032301) {

        // Add temporary trackid field to the scorm_scoes_attempt table to help speed up the data migration.
        $table = new xmldb_table('scorm_scoes_attempt');
        $field = new xmldb_field('trackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'attempt');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $trans = $DB->start_delegated_transaction();

        // First grab all elements and store those.
        $sql = "INSERT INTO {scorm_scoes_element} (element)
                    SELECT DISTINCT element FROM {scorm_scoes_track}";
        $DB->execute($sql);

        // Now store all data in the scorm_scoes_attempt table
        $sql = "INSERT INTO {scorm_scoes_attempt} (userid, scormid, scoid, attempt, trackid)
                    SELECT userid, scormid, scoid, attempt, id as trackid FROM {scorm_scoes_track}";
        $DB->execute($sql);

        // Now store all translated data in the scorm_scoes_value table.
        $sql = "INSERT INTO {scorm_scoes_value} (attemptid, elementid, value, timemodified)
                SELECT a.id as attemptid, e.id as elementid, t.value as value, t.timemodified
                  FROM {scorm_scoes_track} t
                  JOIN {scorm_scoes_element} e ON e.element = t.element
                  JOIN {scorm_scoes_attempt} a ON t.id = a.trackid";
        $DB->execute($sql);

        $trans->allow_commit();

        $table = new xmldb_table('scorm_scoes_attempt');
        $field = new xmldb_field('trackid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Drop old table scorm_scoes_track.
        $table = new xmldb_table('scorm_scoes_track');

        // Conditionally launch drop table for scorm_scoes_track.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Scorm savepoint reached.
        upgrade_mod_savepoint(true, 2016032301, 'scorm');
    }

    return true;
}
