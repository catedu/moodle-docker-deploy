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
 * Course related unit tests for format tiles
 *
 * @package    format_tiles
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles;

use format_tiles\local\format_option;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class format_tiles_testcase
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class format_tiles_test extends \advanced_testcase {

    /**
     * The format options to use when setting up a course in tiles format.
     * @var array
     */
    private $tilescourseformatoptions = [
        'shortname' => 'GrowingCourse',
        'fullname' => 'Growing Course',
        'numsections' => 5,
        'format' => 'tiles',
        'defaulttileicon' => 'user',
        'basecolour' => '#700000',
        'courseusesubtiles' => 1,
        'courseshowtileprogress' => 0,
        'displayfilterbar' => 1,
        'usesubtilesseczero' => 0,
        'courseusebarforheadings' => 1,
    ];

    /**
     * Test updating the section format options e.g. changing the tile icon for a tile.
     * @covers \format_tiles\local\format_option::set
     * @throws \moodle_exception
     */
    public function test_update_section_format_options(): void {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        $sectionscreated = get_fast_modinfo($course)->get_section_info_all();

        $toseticons = [
            1 => 'smile-o',
            2 => 'asterisk',
        ];
        foreach ($sectionscreated as $section) {
            $icon = $toseticons[$section->section] ?? null;
            if ($icon) {
                format_option::set(
                    $course->id, format_option::OPTION_SECTION_ICON, $section->section, $icon
                );
                $this->assertEquals(
                    $icon,
                    format_option::get($course->id, format_option::OPTION_SECTION_ICON, $section->section)
                );
            }
        }
    }

    /**
     * Test updating the course format options e.g. change the tile for a course.
     * @covers \format_tiles::update_course_format_options
     * @throws \dml_exception
     */
    public function test_update_course_format_options(): void {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);
        set_config('followthemecolour', 0, 'format_tiles');
        set_config('allowsubtilesview', 0, 'format_tiles');

        $pushedvalues = [
            'id' => $course->id,
            'defaulttileicon' => 'book',
            'courseusesubtiles' => '0',
            'courseshowtileprogress' => '1',
            'displayfilterbar' => '0',
            'usesubtilesseczero' => '0',
            'courseusebarforheadings' => '0',
        ];

        $format = course_get_format($course);
        $format->update_course_format_options($pushedvalues);

        $dbdata = $DB->get_records(
            'course_format_options',
            ['format' => 'tiles', 'courseid' => $course->id, 'sectionid' => 0]
        );
        $newvalues = [];
        foreach ($dbdata as $k => $v) {
            $newvalues[$v->name] = $v->value;
        }
        foreach ($pushedvalues as $name => $pushedvalue) {
            if ($name !== 'id') {
                // Id is course ID and will not be in new db values.
                $this->assertEquals($pushedvalue, $newvalues[$name], 'Item not updated as expected: ' . $name);
            }
        }

        // Now repeat the above with different values, and check again.
        $pushedvalues = [
            'id' => $course->id,
            'defaulttileicon' => 'television',
            'courseusesubtiles' => '1',
            'courseshowtileprogress' => '0',
            'displayfilterbar' => '1',
            'usesubtilesseczero' => '1',
            'courseusebarforheadings' => '1',
        ];

        $format = course_get_format($course);
        $format->update_course_format_options($pushedvalues);

        $dbdata = $DB->get_records(
            'course_format_options',
            ['format' => 'tiles', 'courseid' => $course->id, 'sectionid' => 0]
        );
        $newvalues = [];
        foreach ($dbdata as $k => $v) {
            $newvalues[$v->name] = $v->value;
        }
        foreach ($pushedvalues as $name => $pushedvalue) {
            if ($name !== 'id') {
                // Id is course ID and will not be in new db values.
                $this->assertEquals($pushedvalue, $newvalues[$name], 'No match on name ' . $name);
            }

        }
    }


    /**
     * Test web service updating section name
     * Function copied from format_topics with format changed to tiles.
     * @covers \format_tiles_inplace_editable
     */
    public function test_update_inplace_editable(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/lib/external/externallib.php');
        require_once($CFG->dirroot . '/lib/external/classes/external_api.php');

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course = $this->getDataGenerator()->create_course($this->tilescourseformatoptions, ['createsections' => true]);
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);

        // Call webservice without necessary permissions.
        try {
            \core_external::update_inplace_editable('format_tiles', 'sectionname', $section->id, 'New section name');
            $this->fail('Exception expected');
        } catch (\moodle_exception $e) {
            $this->assertEquals('Course or activity not accessible. (Not enrolled)',
                $e->getMessage());
        }

        // Change to teacher and make sure that section name can be updated using web service update_inplace_editable().
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $teacherrole->id);

        $res = \core_external::update_inplace_editable('format_tiles', 'sectionname', $section->id, 'New section name');
        $res = \core_external\external_api::clean_returnvalue(\core_external::update_inplace_editable_returns(), $res);
        $this->assertEquals('New section name', $res['value']);
        $this->assertEquals('New section name', $DB->get_field('course_sections', 'name', ['id' => $section->id]));
    }

    /**
     * Test callback updating section name
     * Function copied from format_topics with format changed to tiles.
     * @covers \format_tiles_inplace_editable
     */
    public function test_inplace_editable(): void {
        global $DB, $PAGE;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course($this->tilescourseformatoptions, ['createsections' => true]);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $teacherrole->id);
        $this->setUser($user);

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);

        // Call callback format_tiles_inplace_editable() directly.
        $tmpl = component_callback('format_tiles', 'inplace_editable', ['sectionname', $section->id, 'Rename me again']);
        $this->assertInstanceOf('core\output\inplace_editable', $tmpl);
        $res = $tmpl->export_for_template($PAGE->get_renderer('core'));
        $this->assertEquals('Rename me again', $res['value']);
        $this->assertEquals('Rename me again', $DB->get_field('course_sections', 'name', ['id' => $section->id]));

        // Try updating using callback from mismatching course format.
        try {
            component_callback('format_weeks', 'inplace_editable', ['sectionname', $section->id, 'New name']);
            $this->fail('Exception expected');
        } catch (\moodle_exception $e) {
            $this->assertEquals(1, preg_match('/^Can\'t find data record in database/', $e->getMessage()));
        }
    }

    /**
     * Test video embed URL replacement
     * @covers \format_tiles\local\video_cm::check_modify_embedded_url
     */
    public function test_video_urls(): void {
        $this->resetAfterTest();
        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\local\video_cm::check_modify_embedded_url('https://www.youtube.com/watch?v=abcdefghijk')
        );

        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\local\video_cm::check_modify_embedded_url('https://youtu.be/abcdefghijk')
        );

        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\local\video_cm::check_modify_embedded_url('https://www.youtube.com/shorts/abcdefghijk')
        );

        $this->assertEquals(
            'https://player.vimeo.com/video/347119375',
            \format_tiles\local\video_cm::check_modify_embedded_url('https://vimeo.com/347119375')
        );

        $this->assertTrue(
            \format_tiles\local\video_cm::is_video_url('https://www.youtube.com/embed/abcdefghijk?t=123')
        );

        $this->assertTrue(
            \format_tiles\local\video_cm::is_video_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        $this->assertTrue(
            \format_tiles\local\video_cm::is_video_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        // If the URL contains a param that we're unsure how to handle, we don't modify (i.e. return null).
        $this->assertEquals(
            null,
            \format_tiles\local\video_cm::check_modify_embedded_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        $this->assertTrue(
            \format_tiles\local\video_cm::is_video_url(
                'https://youtu.be/abcdefghijk?t=49'
            )
        );

        // If the URL contains a param that we're unsure how to handle, we don't modify (i.e. return null).
        $this->assertEquals(
            null,
            \format_tiles\local\video_cm::check_modify_embedded_url(
                'https://youtu.be/abcdefghijk?t=49'
            )
        );
    }

    /**
     * Test modal helper CM ID getter.
     * @covers \format_tiles\local\modal_helper::get_resource_modal_cmids
     * @covers \format_tiles\local\modal_helper::cm_modal_type
     */
    public function test_modal_resource_cmids(): void {
        global $CFG, $DB;

        // To import RESOURCELIB_DISPLAY_XXX etc.
        require_once("$CFG->libdir/resourcelib.php");

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        // Must be a non-guest user to create resources.
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_resource');

        $component = 'mod_resource';
        $filearea = 'content';
        $textfilepath = 'mod/resource/tests/fixtures/samplefile.txt';
        $pdffilepath = 'course/format/tiles/tests/fixtures/test.pdf';

        // A resource with a txt file attached should not result in a modal.
        $instance = $generator->create_instance(['course' => $course->id, 'uploaded' => true, 'defaultfilename' => $textfilepath]);
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );

        // A resource with a PDF file attached should result in a modal.
        $instance = $generator->create_instance([
            'course' => $course->id, 'uploaded' => true,
            'defaultfilename' => $pdffilepath, 'display' => RESOURCELIB_DISPLAY_EMBED,
        ]);
        $this->assertEquals(
            'pdf', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        // Add a text file to the PDF existing resource activity.
        $pdfcmcontext = \context_module::instance($instance->cmid);
        $filerecord = [
            'component' => $component, 'filearea' => $filearea,
            'contextid' => $pdfcmcontext->id, 'itemid' => 0,
            'filename' => basename('test.txt'), 'filepath' => '/',
        ];
        $fs = get_file_storage();
        $fs->create_file_from_pathname($filerecord, $textfilepath);

        // At this point we added the HTML file so the PDF should still be the main file so it's still a modal activity.
        $this->assertEquals(
            'pdf', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        // Add another PDF to make sure code is not confused by having multiple PDFs.
        $filerecord = [
            'component' => $component, 'filearea' => $filearea,
            'contextid' => $pdfcmcontext->id, 'itemid' => 0,
            'filename' => basename('test2.pdf'), 'filepath' => '/',
        ];
        $fs = get_file_storage();
        $fs->create_file_from_pathname($filerecord, $pdffilepath);

        // At this point we added a text file and another PDF.
        // The original PDF should still be the main file so it's still a modal activity.
        $this->assertEquals(
            'pdf', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        // Now set the newly added text file as the main file so this is no longer a modal activity.
        file_reset_sortorder($pdfcmcontext->id, $component, $filearea, 0);
        file_set_sortorder($pdfcmcontext->id, $component, $filearea, 0, '/', 'test.txt', 1);
        rebuild_course_cache($course->id, true);
        \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, 'resource');
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );

        // Now set the PDF back to main file so it's a modal activity again.
        file_reset_sortorder($pdfcmcontext->id, $component, $filearea, 0);
        file_set_sortorder($pdfcmcontext->id, $component, $filearea, 0, '/', 'test.pdf', 1);
        rebuild_course_cache($course->id, true);
        \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, 'resource');
        $this->assertEquals(
            'pdf', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        // Now set an excluded display types to the resource activity and check that it has no modal.
        $DB->set_field('resource', 'display', RESOURCELIB_DISPLAY_POPUP, ['id' => $instance->id]);
        rebuild_course_cache($course->id, true);
        \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, 'resource');
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );

        // A resource with an HTML file attached should also result in a modal.
        $instance = $generator->create_instance([
            'course' => $course->id,
            'uploaded' => true,
            'defaultfilename' => 'course/format/tiles/tests/fixtures/test.html',
        ]);
        $this->assertEquals(
            'html', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );
    }

    /**
     * Test modal helper CM ID getter for URLs.
     * @covers \format_tiles\local\modal_helper::get_modal_allowed_cm_ids
     * @covers \format_tiles\local\modal_helper::cm_modal_type
     */
    public function test_modal_url_cmids(): void {
        global $CFG, $DB;

        // To import RESOURCELIB_DISPLAY_XXX etc.
        require_once("$CFG->libdir/resourcelib.php");

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        // Must be a non-guest user to create resources.
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_url');

        $instance = $generator->create_instance([
            'course' => $course->id,
            'name' => 'URL A',
            'externalurl' => 'https://moodle.org/',
            'display' => RESOURCELIB_DISPLAY_AUTO,
        ]);
        $this->assertEquals(
            'url', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        $DB->set_field('url', 'display', RESOURCELIB_DISPLAY_POPUP, ['id' => $instance->id]);
        rebuild_course_cache($course->id, true);
        \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, 'url');
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );

        $DB->set_field('url', 'display', RESOURCELIB_DISPLAY_AUTO, ['id' => $instance->id]);
        rebuild_course_cache($course->id, true);
        // Disallow all resource modals as site admin.
        set_config('modalresources', '', 'format_tiles');
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );

    }

    /**
     * Test modal helper CM ID getter for pages.
     * @covers \format_tiles\local\modal_helper::get_modal_allowed_cm_ids
     * @covers \format_tiles\local\modal_helper::cm_modal_type
     */
    public function test_modal_page_cmids(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        // Must be a non-guest user to create resources.
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_page');

        $instance = $generator->create_instance(['course' => $course->id]);

        $this->assertEquals(
            'page', \format_tiles\local\modal_helper::cm_modal_type($course->id, $instance->cmid)
        );

        set_config('modalmodules', '', 'format_tiles');
        $this->assertFalse(
            \format_tiles\local\modal_helper::cm_has_modal($course->id, $instance->cmid)
        );
    }

    /**
     * Test modal helper CM ID clear.
     * @covers \format_tiles\local\modal_helper::get_modal_allowed_cm_ids
     * @covers \format_tiles\local\modal_helper::cm_modal_type
     */
    public function test_modal_cmids_cache_clear(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        // We never store data for label.
        $this->assertFalse(
            \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, 'label')
        );
        // We do store data for others.
        foreach (['resource', 'url', 'page'] as $modname) {
            $this->assertTrue(
                \format_tiles\local\modal_helper::clear_cache_modal_cmids($course->id, $modname)
            );
        }
    }
}
