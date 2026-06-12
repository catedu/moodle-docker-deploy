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
 * Helper functions for use in tests.
 *
 * @package    format_tiles
 * @copyright  2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Helper function to create a course from MBZ file in plugin codebase.
 * @param string $filename
 * @param string $newcoursename
 * @return int
 * @throws base_plan_exception
 * @throws base_setting_exception
 * @throws dml_exception
 * @throws dml_transaction_exception
 * @throws file_exception
 * @throws restore_controller_exception
 * @throws stored_file_creation_exception
 */
function helper_restore_test_course(string $filename, string $newcoursename): int {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

    $mbzpath = "$CFG->dirroot/course/format/tiles/tests/fixtures/mbz/$filename";

    $ctx = \context_system::instance();

    $fs = get_file_storage();
    $adminuser = get_admin();

    $timenow = time();
    $filerecord = new \stdClass;
    $filerecord->component = 'format_tiles';
    $filerecord->filearea = 'coursebackups';
    $filerecord->filepath = '/';
    $filerecord->filename = $filename;
    $filerecord->itemid = $timenow;
    $filerecord->contextid = $ctx->id;
    $filerecord->userid = $adminuser->id;
    $filerecord->timecreated = $timenow;

    $fs->delete_area_files($ctx->id, 'format_tiles', 'coursebackups');
    $backupfile = $fs->create_file_from_pathname($filerecord, $mbzpath);

    $unzipdirname = $timenow;
    if (check_dir_exists("$CFG->tempdir/backup/$unzipdirname", false)) {
        $unlinkfiles = array_diff(scandir("$CFG->tempdir/backup/$unzipdirname"), ['.', '..']);
        foreach ($unlinkfiles as $unlinkfile) {
            unlink("$CFG->tempdir/backup/$unzipdirname/$unlinkfile");
        }
    } else {
        make_temp_directory("backup/$unzipdirname");
    }
    $backupfile->extract_to_pathname(
        get_file_packer('application/vnd.moodle.backup'),
        "$CFG->tempdir/backup/$unzipdirname"
    );

    $categoryid = $DB->get_field_select('course_categories', "MIN(id)", "parent=0");
    // Execute in transaction to prevent course creation if restore fails.
    $transaction = $DB->start_delegated_transaction();

    if ($newcourseid = \restore_dbops::create_new_course($newcoursename, $newcoursename, $categoryid)) {
        $rc = new \restore_controller(
            $unzipdirname,
            $newcourseid,
            \backup::INTERACTIVE_NO, \backup::MODE_GENERAL,
            $adminuser->id, \backup::TARGET_NEW_COURSE);
        $rc->get_plan()->get_setting('users')->set_value(false);
        $rc->execute_precheck();
        $rc->execute_plan();
        $transaction->allow_commit();
        $rc->destroy();
    }
    $transaction->dispose();
    return $newcourseid;
}

/**
 * Helper function to set a legacy photo to a course from image in plugin codebase.
 * @param int $courseid
 * @param int $sectionnumber
 * @param string $filename
 * @return void
 * @throws dml_exception
 * @throws file_exception
 * @throws stored_file_creation_exception
 */
function helper_set_legacy_tilephoto(int $courseid, int $sectionnumber, string $filename) {
    global $DB, $CFG;

    $record = (object)[
        'courseid' => $courseid,
        'format' => 'tiles',
        'sectionid' => $DB->get_field(
            'course_sections', 'id', ['course' => $courseid, 'section' => $sectionnumber]
        ),
        'name' => 'tilephoto',
        'value' => $filename,
    ];
    $record->id = $DB->insert_record('course_format_options', $record);

    $filepath = "$CFG->dirroot/course/format/tiles/tests/fixtures/images";
    $sectionid = $DB->get_field(
        'course_sections', 'id', ['course' => $courseid, 'section' => $sectionnumber]
    );
    $context = context_course::instance($courseid);

    $filerecord = (object)[
        'contextid' => $context->id,
        'component' => 'format_tiles',
        'filearea' => 'tilephoto',
        'filepath' => '/tilephoto/',
        'filename' => $filename,
        'itemid' => $sectionid,
    ];

    $fs = get_file_storage();
    $storedfile = $fs->create_file_from_pathname($filerecord, "$filepath/$filename");
    $filerecord->id = $storedfile->get_id();
}

/**
 * Set an icon to a course using simulated legacy (4.2-) format.
 * @param int $courseid
 * @param int $sectionnumber
 * @param string $icon
 * @return bool|int
 * @throws dml_exception
 */
function helper_set_legacy_tile_icon(int $courseid, int $sectionnumber, string $icon) {
    global $DB;
    $record = (object)[
        'courseid' => $courseid,
        'format' => 'tiles',
        'sectionid' => $DB->get_field(
            'course_sections', 'id', ['course' => $courseid, 'section' => $sectionnumber]
        ),
        'name' => 'tileicon',
        'value' => $icon,
    ];
    return $DB->insert_record('course_format_options', $record);
}
