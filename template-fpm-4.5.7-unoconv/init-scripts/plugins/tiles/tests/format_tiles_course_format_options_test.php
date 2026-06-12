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
 * Course format option related unit tests for format tiles
 *
 * @package    format_tiles
 * @copyright  2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles;

use format_tiles\local\format_option;

/**
 * Class format_tiles_course_format_options_testcase
 * @copyright  2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class format_tiles_course_format_options_test extends \advanced_testcase {

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once("$CFG->dirroot/backup/util/includes/backup_includes.php");
        \backup_controller_dbops::apply_version_and_release();
        \phpunit_util::bootstrap_init();
        parent::setUpBeforeClass();
    }

    /**
     * Create a mock course with legacy format options and test migration.
     * @covers \format_tiles\local\format_option::migrate_legacy_format_options
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_course_format_option_migration(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/format/tiles/tests/helperlib.php');

        // Create test course old format and migrate it.
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'tiles', 'numsections' => 15]);
        $context = \context_course::instance($course->id);

        $fs = get_file_storage();
        for ($sectionnumber = 1; $sectionnumber <= 10; $sectionnumber++) {
            $filename = "placeholder_$sectionnumber.jpg";
            helper_set_legacy_tilephoto($course->id, $sectionnumber, $filename);
        }

        $icons = [
            11 => 'asterisk', 12 => 'clone', 13 => 'cloud-download', 14 => 'film', 15 => 'star-o',
        ];
        foreach ($icons as $sectionnumber => $icon) {
            helper_set_legacy_tile_icon($course->id, $sectionnumber, $icon);
        }

        // This is the method we are testing so run it.
        format_option::migrate_legacy_format_options($course->id);

        // Now check it worked as expected - entries now appear in new table.  Files stay where they are.
        for ($sectionnumber = 1; $sectionnumber <= 9; $sectionnumber++) {
            $expectedfilename = "placeholder_$sectionnumber.jpg";
            $sectionid = $DB->get_field(
                'course_sections', 'id', ['course' => $course->id, 'section' => $sectionnumber]
            );
            $optionvalue = format_option::get($course->id, format_option::OPTION_SECTION_PHOTO, $sectionid);
            $this->assertEquals($optionvalue, $expectedfilename);

            $file = $fs->get_file(
                $context->id, 'format_tiles', 'tilephoto', $sectionid, '/tilephoto/', $expectedfilename
            );
            $this->assertEquals($file->get_filename() ?? null, $expectedfilename);
            $this->assertTrue(($file->get_filesize() ?? 0) > 0);
        }

        for ($sectionnumber = 11; $sectionnumber <= 15; $sectionnumber++) {
            $expectedicon = $icons[$sectionnumber];
            $sectionid = $DB->get_field(
                'course_sections', 'id', ['course' => $course->id, 'section' => $sectionnumber]
            );
            $optionvalue = format_option::get($course->id, format_option::OPTION_SECTION_ICON, $sectionid);
            $this->assertEquals(
                $optionvalue, $expectedicon, "Section ID $sectionid, number $sectionnumber, option value '$optionvalue'"
            );
        }
    }

    /**
     * Data provider for test_restore_from_old_format_mbz.
     * @see test_restore_from_old_format_mbz which this provides data for.
     * @return array
     */
    public static function restore_from_old_format_mbz_provider(): array {
        return [
            'mbzfiles' => [
                "moodle-311-sample.mbz",
                "moodle-42-pre-2024.mbz",
                "moodle-43-early-beta.mbz",
                "moodle-43-late-beta.mbz",
            ],
        ];
    }

    /**
     * Take old course MBZ files and restore then check photos.
     * @dataProvider restore_from_old_format_mbz_provider
     * @covers \backup_format_tiles_plugin
     * @covers \restore_format_tiles_plugin
     * @param string $mbzfile
     * @return void
     * @throws \dml_exception
     */
    public function test_restore_from_old_format_mbz(string $mbzfile): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/course/format/tiles/tests/helperlib.php');
        $expectedphotos = [
            1 => 'placeholder_1.jpg',
            2 => 'placeholder_2.jpg',
            3 => 'placeholder_3.jpg',
            4 => 'placeholder_4.jpg',
            5 => 'placeholder_5.jpg',
            7 => 'placeholder_7.jpg',
        ];
        for ($i = 1; $i <= 5; $i++) {
            $newcoursename = str_replace('.mbz', '', $mbzfile);
            $restoredcourseid = helper_restore_test_course($mbzfile, $newcoursename);
            $actualphotos = $DB->get_records_sql(
                "SELECT cs.section as sectionnumber, cs.id AS sectionid, fo.optionvalue as value
                        FROM {format_tiles_tile_options} fo
                        JOIN {course_sections} cs ON cs.id = fo.elementid AND cs.course = fo.courseid
                        WHERE fo.optiontype = :optiontype AND fo.courseid = :courseid",
                ['courseid' => $restoredcourseid, 'optiontype' => format_option::OPTION_SECTION_PHOTO]
            );

            $context = \context_course::instance($restoredcourseid);
            $actualfiles = $DB->get_records_sql(
                "SELECT cs.section, f.filename
                    FROM {files} f
                    JOIN {course_sections} cs ON cs.id = f.itemid AND cs.course = :courseid
                    WHERE f.contextid = :contextid
                    AND f.component = 'format_tiles' AND f.filearea = 'tilephoto'
                    AND f.filename != '' AND f.filesize > 0",
                ['contextid' => $context->id, 'courseid' => $restoredcourseid]
            );

            foreach ($expectedphotos as $sectionnumber => $filename) {
                $this->assertEquals(
                    $filename,
                    $actualphotos[$sectionnumber]->value ?? null,
                    "Incorrect photo option for course ID $restoredcourseid section $sectionnumber - all photos "
                        . json_encode($actualphotos)
                );
                $this->assertEquals(
                    $filename,
                    $actualfiles[$sectionnumber]->filename ?? null,
                    "Missing file $filename for section number $sectionnumber - all files " . json_encode($actualfiles)
                );
            }

            $sections = $DB->get_records('course_sections', ['course' => $restoredcourseid], 'section');
            foreach ($sections as $section) {
                if (isset($expectedphotos[$section->section])) {
                    continue;
                }
                $photo = format_option::get_db_record(
                    $restoredcourseid, format_option::OPTION_SECTION_PHOTO, $section->id
                );
                $this->assertFalse(
                    $photo,
                    "Unexpected photo found for section $section->section"
                );
            }
        }
    }
}
