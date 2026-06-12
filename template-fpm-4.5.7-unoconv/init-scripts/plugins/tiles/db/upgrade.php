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
 * Upgrade scripts for course format "Tiles"
 *
 * @package    format_tiles
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade script for format_tiles
 * Copied in part from the script for format "Topics"
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws file_exception
 * @throws stored_file_creation_exception
 * @throws upgrade_exception
 */
function xmldb_format_tiles_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/course/format/tiles/db/upgradelib.php');

    if ($oldversion < 2017102700) {

        // Remove 'numsections' option and hide or delete orphaned sections.
        format_tiles_upgrade_remove_numsections();

        // Where course format options not being used any more, clean up the old data.
        format_tiles_remove_unused_format_options();

        upgrade_plugin_savepoint(true, 2017102700, 'format', 'tiles');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2018080103) {
        // Rename the field "tiletopleftthistile" to "tileicon".
        // The latter is much simpler and the former was only used for legacy reasons.
        $DB->set_field('course_format_options', 'name', 'tileicon',
            ['format' => 'tiles', 'name' => 'tiletopleftthistile']);

        // Same for "defaulttiletopleftdisplay".
        $DB->set_field('course_format_options', 'name', 'defaulttileicon',
            ['format' => 'tiles', 'name' => 'defaulttiletopleftdisplay']);

        // Delete any 'course default' records for tile icons as these are no longer used.
        $DB->delete_records_select(
            'course_format_options',
            "format = 'tiles' AND name = 'tileicon' AND value = 'course default'"
        );

        // Delete any zero values for tile outcome id as these are no longer stored (represent no outcome selected).
        $DB->delete_records_select(
            'course_format_options',
            "format = 'tiles' AND name = 'tileoutcomeid' AND value = '0'"
        );

        // Field headertextabovetiles and prefixtitlewithnumber were removed in this release so delete any settings.
        $DB->delete_records_select(
            'course_format_options',
            "format = 'tiles' AND (name = 'headertextabovetiles' OR name = 'prefixtitlewithnumber')"
        );

        // Field courseusebarforheadings setting has been simplified to yes or no (1 or 0) instead of several options.
        // So change all legacy values to 0 or 1.
        $DB->set_field_select(
            "course_format_options", "value", "1", "format='tiles' AND name = 'courseusebarforheadings' AND value != 'standard'"
        );
        $DB->set_field_select(
            "course_format_options", "value", "0", "format='tiles' AND name = 'courseusebarforheadings' AND value = 'standard'"
        );

        // Which filter button a user previously had pressed is now stored in browser session storage.
        // Same for whether sec zero is collapsed, so delete from database.
        $DB->delete_records_select(
            'user_preferences', $DB->sql_like("name", "?", false), ["format_tiles_filterbutton_%"]
        );
        $DB->delete_records_select(
            'user_preferences', $DB->sql_like("name", "?", false), ["format_tiles_collapseseczero_%"]
        );

        $DB->set_field_select(
            "course_format_options",
            'value',
            'handshake-o',
            "format='tiles' AND value='handshake' and (name='tileicon' or name='defaulttileicon')"
        );
        $DB->set_field_select(
            "course_format_options",
            'value',
            'calendar-check-o',
            "format='tiles' AND value='calendar-check' and (name='tileicon' or name='defaulttileicon')"
        );

        unset_config('persistfilterbuttons', 'format_tiles'); // Removed functionality.
        unset_config('fontimporttext', 'format_tiles'); // Removed functionality.
        unset_config('fontfamilytext', 'format_tiles'); // Removed functionality.
        unset_config('displaytileitems', 'format_tiles'); // Removed functionality.
        unset_config('showiconslist', 'format_tiles'); // Removed functionality.

        upgrade_plugin_savepoint(true, 2018080103, 'format', 'tiles');
    }

    if ($oldversion < 2019052100) {
        // Check the "URL" box under admin settings "Modal resources" (this is a new setting we want on by default).
        $setting = get_config('format_tiles', 'modalresources');
        if ($setting == '') {
            set_config('modalresources', 'url', 'format_tiles');
        } else {
            $setting = explode(",", get_config('format_tiles', 'modalresources'));
            if (in_array('url', $setting) === false) {
                $setting[] = 'url';
                $setting = implode(",", $setting);
                set_config('modalresources', $setting, 'format_tiles');
            }
        }

        // Store the sample photo tile image in the database.
        $fs = get_file_storage();
        $filerecord = format_tiles\local\tile_photo::file_api_params();
        $filerecord['contextid'] = \context_system::instance()->id;
        $filerecord['itemid'] = 0;
        $filerecord['mimetype'] = 'image/jpeg';
        $filerecord['filename'] = 'sample_image.jpg';
        $path = $CFG->dirroot . '/course/format/tiles/';
        $existingfile = $fs->get_file(
            $filerecord['contextid'],
            $filerecord['component'],
            $filerecord['filearea'],
            $filerecord['itemid'],
            $filerecord['filepath'],
            $filerecord['filename']
        );
        if (!$existingfile) {
            $fs->create_file_from_pathname($filerecord, $path . $filerecord['filename']);
        }

        upgrade_plugin_savepoint(true, 2019052100, 'format', 'tiles');
    }

    if ($oldversion < 2024020200) {

        // New way of storing tile images, using a new format_tiles_tile_options table instead of core course_format_options.

        // Define table format_tiles_tile_options to be created.
        $table = new xmldb_table('format_tiles_tile_options');

        // Adding fields to table format_tiles_tile_options.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('optiontype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('elementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('optionvalue', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table format_tiles_tile_options.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Adding indexes to table format_tiles_tile_options.
        $table->add_index('elementid-optiontype', XMLDB_INDEX_NOTUNIQUE, ['elementid', 'optiontype']);
        $table->add_index('courseid-elementid-optiontype', XMLDB_INDEX_UNIQUE, ['courseid', 'elementid', 'optiontype']);

        // Conditionally launch create table for format_tiles_tile_options.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            // If we have courses using the course_format_options table to store tilephoto choices, change them to use new table.
            // We use an adhoc task to pass this to cron as it may take a long time during the upgrade if lots of courses/data.
            $legacycourses = $DB->get_fieldset_sql(
                "SELECT DISTINCT courseid from {course_format_options}
                WHERE format = 'tiles' AND name = 'tilephoto' OR name = 'tileicon' ORDER BY courseid"
            );
            foreach ($legacycourses as $courseid) {
                $task = new \format_tiles\task\migrate_legacy_data();
                $task->set_custom_data(['courseid' => $courseid]);
                \core\task\manager::queue_adhoc_task($task);
            }
        }

        // Tiles savepoint reached.
        upgrade_plugin_savepoint(true, 2024020200, 'format', 'tiles');
    }

    if ($oldversion < 2024061800) {
        // Course index in Tiles is no longer an experimental feature so activate it.
        // Site admin can de-activate if they wish via plugin settings.
        set_config('usecourseindex', 1, 'format_tiles');
        upgrade_plugin_savepoint(true, 2024061800, 'format', 'tiles');
    }

    // Remove any adhoc tasks queued for deleted code (commit 7f0c8db6).
    if ($oldversion < 2025041631) {
        $deletedclasses = ['\format_tiles\task\deferred_register', '\format_tiles\task\delete_empty_sections'];
        foreach ($deletedclasses as $deletedclass) {
            $DB->delete_records('task_adhoc', ['component' => 'format_tiles', 'classname' => $deletedclass]);
        }
        upgrade_plugin_savepoint(true, 2025041631, 'format', 'tiles');
    }

    return true;
}
