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
 * Specialised restore for format_tiles (based on the equivalent for format_topics
 *
 * @package   format_tiles
 * @category  backup
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup for format_tiles
 *
 * Ensure that photo background images are included in course backups.
 *
 * @package   format_tiles
 * @category  backup
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_format_tiles_plugin extends backup_format_plugin {

    /**
     * Carries out some checks at start of course backup.
     *
     * @throws moodle_exception
     */
    public function define_course_plugin_structure() {
        $this->pre_backup_steps();
    }

    /**
     * Returns the format information to attach to section element.
     * @return backup_plugin_element
     * @throws base_element_struct_exception
     */
    protected function define_section_plugin_structure() {
        $fileapiparams = \format_tiles\local\tile_photo::file_api_params();

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'tiles');

        // Define each element separated.
        // Adds tag <plugin_format_tiles_section> within section.xml files of backup file with filename / icon name.
        // The "id" field below is the mdl_format_tiles_tile_options.id in the old course.
        // It will appear in section.xml as <plugin_format_tiles_section sectionid="{sectionid}">
        // This is then used in the restore section as $data['sectionid'] in process_tiles_section();
        // The optiontype and optionvalue fields will be included in each <plugin_format_tiles_section> tag.
        $sectionoptions = new backup_nested_element(
            $this->get_recommended_name(), // Expect 'plugin_format_tiles_section'.
            ['sectionid'],
            ['optiontype', 'optionvalue', 'tilesversion']
        );

        // Define sources.
        $const1 = \format_tiles\local\format_option::OPTION_SECTION_PHOTO;
        $const2 = \format_tiles\local\format_option::OPTION_SECTION_ICON;

        // Include tiles version so we can use it on restore.
        $tilesversion  = filter_var(get_config('format_tiles', 'version') ?? 0, FILTER_SANITIZE_NUMBER_INT);

        $sectionoptions->set_source_sql(
            "SELECT elementid as sectionid, optiontype, optionvalue, $tilesversion AS tilesversion
                FROM {format_tiles_tile_options}
                WHERE courseid = :courseid
                AND (optiontype = $const1 OR optiontype = $const2)
                AND elementid = :elementid",
            ['courseid' => backup::VAR_COURSEID, 'elementid' => backup::VAR_SECTIONID]
        );

        // Define file annotations - include tile photos.
        // Some explanation of annotate_files() is at: https://docs.moodle.org/dev/Backup_2.0_for_developers.
        // E.g. "The third parameter, if it is needed, must be the name of one of the attributes or fields of [$sectionoptions]".
        // We use 'sectionid' from the nested element above which is the old section ID.
        $sectionoptions->annotate_files($fileapiparams['component'], $fileapiparams['filearea'], 'sectionid');

        $plugin->add_child($sectionoptions);

        return $plugin;
    }

    /**
     * Carry out some initial steps before we start backup.
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function pre_backup_steps() {
        global $DB;
        $courseid = $this->step->get_task()->get_courseid();
        $format = $DB->get_field('course', 'format', ['id' => $courseid]);
        if ($format !== 'tiles') {
            return;
        }

        $this->pre_step_fail_if_course_includes_excess_sections($courseid);

        // Starting tiles plugin version 4.3, the course_format_options table is not used for tile photos/icons.
        // For backwards compatibility of course exports with prior plugin versions, we include such data in backups.
        // So we temporarily set course format options to the course so that they will be included.
        // There is an observer \format_tiles\observer\course_backup_created() which reverses this once backup is complete.
        \format_tiles\local\format_option::set_legacy_format_options($courseid);
    }

    /**
     * Issue 45.
     * If incompatible Moodle 3.7 version of Tiles plugin was used in Moodle 3.9, incorrectly numbered sections may exist.
     * To avoid creating a empty sections on import or restore, check for incorrect sections and throw error if found.
     * @param int $courseid
     * @throws moodle_exception
     * @throws dml_exception
     */
    private function pre_step_fail_if_course_includes_excess_sections(int $courseid) {
        global $DB;

        $maxsectionsconfig = \format_tiles\local\course_section_manager::get_max_sections();
        $maxallowed = $maxsectionsconfig + 1;// We +1 as sec zero not counted.

        // If user is admin, when we throw error, we offer them a button to delete excess sections.
        $isadmin = has_capability('moodle/site:config', \context_system::instance());
        if ($isadmin) {
            $admintoolsurl = \format_tiles\local\course_section_manager::get_list_problem_courses_url();
            $admintoolsbutton = \html_writer::link(
                $admintoolsurl,
                get_string('checkforproblemcourses', 'format_tiles'),
                ['class' => 'btn btn-secondary ms-2']
            );
        } else {
            $admintoolsurl = '';
            $admintoolsbutton = '';
        }

        if (\format_tiles\local\course_section_manager::course_section_count_exceeds($courseid, $maxallowed * 5)) {
            // Course has a very high number of sections, so fail early, as probably en error and we avoid further work.
            // Legacy check, included in 2020 to protect against issue 45 and should be very unlikely to happen now.
            // Check can probably be removed soon.
            \core\notification::error(get_string('restoretoomanysections', 'format_tiles', $maxallowed) . $admintoolsbutton);
            throw new moodle_exception('backupfailed', 'format_tiles', $admintoolsurl);
        }

        $totalincluded = 0;
        $modinfo = get_fast_modinfo($courseid);
        $sections = $modinfo->get_section_info_all();
        foreach ($sections as $section) {
            if ($section->is_delegated()) {
                // This is a subsection so not counted for this purpose.
                continue;
            }

            // Is the section to be included in the backup or has the user excluded it (unchecked box)?  Ignore if excluded.
            $settingname = 'section_' . $section->id . '_included';
            $included = $this->get_setting_value($settingname);
            if ($included) {
                $totalincluded++;
                if ($totalincluded > $maxallowed * 5) {
                    // Allowing this section to go in the backup would mean we have too many secs - disallow.
                    // Legacy check, included in 2020 to protect against issue 45 and should be very unlikely to happen now.
                    // Check can probably be removed soon.
                    \core\notification::error(
                        get_string('restoretoomanysections', 'format_tiles', $maxsectionsconfig) . $admintoolsbutton
                    );
                    throw new moodle_exception('backupfailed', 'format_tiles', $admintoolsurl);
                }
            }
        }
    }
}
