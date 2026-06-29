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
 * @copyright 2017 David Watson {@link http://evolutioncode.uk}, Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use format_tiles\local\format_option;

/**
 * Specialised restore for format_tiles
 *
 * Processes 'numsections' from the old backup files and hides sections that used to be "orphaned".
 * Also handles restoring tile background image files from the backup archive to the tiles.
 *
 * @package   format_tiles
 * @category  backup
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}, Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_format_tiles_plugin extends restore_format_plugin {
    /** @var int */
    protected $originalnumsections = 0;

    /**
     * Checks if backup file was made on Moodle before 3.3 and we should respect the 'numsections'
     * and potential "orphaned" sections in the end of the course.
     *
     * @return bool
     */
    protected function need_restore_numsections() {
        $backupinfo = $this->step->get_task()->get_info();
        $backuprelease = $backupinfo->backup_release;
        return version_compare($backuprelease, '3.3', 'lt');
    }

    /**
     * Carries out some checks at start of course restore.
     *
     * @return restore_path_element[]
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function define_course_plugin_structure() {
        global $DB;
        if ($this->is_tiles_course()) {
            // Since this method is executed before the restore we can do some pre-checks here.
            $this->fail_if_course_includes_excess_sections();

            // In case of merging backup into existing course find the current number of sections.
            $target = $this->step->get_task()->get_target();
            if (
                ($target == backup::TARGET_CURRENT_ADDING || $target == backup::TARGET_EXISTING_ADDING) &&
                $this->need_restore_numsections()
            ) {
                $maxsection = $DB->get_field_sql(
                    'SELECT max(section) FROM {course_sections} WHERE course = ?',
                    [$this->step->get_task()->get_courseid()]
                );
                $this->originalnumsections = (int)$maxsection;
            }

            // Dummy path element is needed in order for after_restore_course() to be called.
            return [new restore_path_element('dummy_course', $this->get_pathfor('/dummycourse'))];
        }
        return [];
    }

    /**
     * Ensure that we include photo background images in our restore structure.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function define_section_plugin_structure() {
        if ($this->is_tiles_course()) {
            // We put this here as otherwise we don't seem to have a way of making sure it is done on import as well as restore.
            $this->check_destination_course_section_count();
        }

        // This defines 'process_tiles_section' below.
        return [new restore_path_element('tiles_section', $this->get_pathfor('/'))];
    }

    /**
     * Dummy process method
     */
    public function process_dummy_course() {
    }

    /**
     * Process the restored section.
     * @param array $data
     * @throws moodle_exception
     */
    public function process_tiles_section($data) {
        global $DB;
        if ($this->is_tiles_course()) {
            $this->set_format_opt(
                $this->task->get_courseid(),
                $this->task->get_sectionid(),
                $data['optiontype'],
                $data['optionvalue']
            );

            // Now add the mapping for the files (old section ID to new).
            $fileapiparams = \format_tiles\local\tile_photo::file_api_params();
            $oldsectionid = $data['sectionid'] ?? null;
            $newsectionid = $this->task->get_sectionid();

            if (!$oldsectionid) {
                // This should rarely happen - only if backup was prepared with early Tiles 4.3 beta.
                $dbman = $DB->get_manager();
                $temptable = 'backup_ids_temp';
                if ($dbman->table_exists($temptable)) {
                    try {
                        $oldsectionid = $DB->get_field_sql(
                            "SELECT itemid FROM {{$temptable}} WHERE itemname = 'course_section' AND newitemid = ?",
                            [$newsectionid]
                        );
                    } catch (\Exception $e) {
                        debugging("Could not get old section ID " . $e->getMessage(), DEBUG_DEVELOPER);
                    }
                }
            }

            if ($oldsectionid) {
                // Map the old section ID to the new one for the files.
                $this->set_mapping('itemid', $oldsectionid, $newsectionid, true);
                $this->add_related_files(
                    $fileapiparams['component'],
                    $fileapiparams['filearea'],
                    'itemid'
                );
            }
        }
    }

    /**
     * Executed after section restore is complete
     *
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception|moodle_exception
     */
    public function after_restore_section() {
        global $DB;
        if ($this->is_tiles_course()) {
            $data = $this->connectionpoint->get_data();
            if (isset($data['path']) && $data['path'] = '/section' && isset($data['tags']['id'])) {
                $oldsectionid = $data['tags']['id'];
                $oldsectionnum = $data['tags']['number'];

                $newcourseid = $this->step->get_task()->get_courseid();
                $newsectionid = $DB->get_field(
                    'course_sections',
                    'id',
                    ['course' => $newcourseid, 'section' => $oldsectionnum]
                );
                if ($newsectionid) {
                    // This won't be needed if the backup was done with tiles plugin version higher than 2024021400.
                    // Higher than that version, it's mapped in $this->set_mapping('itemid ... in process_tiles_section().
                    // The method itself will check if needed and do nothing if not.
                    self::update_file_records_sections(
                        $newcourseid,
                        context_course::instance($newcourseid)->id,
                        $oldsectionid,
                        $newsectionid
                    );
                    format_option::migrate_legacy_format_options($newcourseid, $newsectionid);

                    // Delete references to tile outcomes under section format options (now incorrect in restored course).
                    // Users will have to set out up outcomes in new course for now if they want to.
                    $DB->delete_records(
                        'course_format_options',
                        [
                            'name' => 'tileoutcomeid',
                            'format' => 'tiles',
                            'courseid' => $newcourseid,
                            'sectionid' => $newsectionid,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Executed after course restore is complete
     *
     * This method is only executed if course configuration was overridden
     * @throws dml_exception
     */
    public function after_restore_course() {
        global $DB;
        if ($this->is_tiles_course()) {
            $newcourseid = $this->step->get_task()->get_courseid();

            $backupinfo = $this->get_task()->get_info();
            $backuprelease = $backupinfo->backup_release;

            $needsunmappedfilesadded = version_compare($backuprelease, '4.0', '<');
            if (!$needsunmappedfilesadded && version_compare($backuprelease, '4.3', '<=')) {
                // For MBZs from Moodle 4.1, 4.2 and early beta 4.3, we may need unmapped files added.
                // If there are any already mapped files then we assume all is ok.
                $context = context_course::instance($newcourseid);
                $hasmappedfiles = $DB->record_exists_sql(
                    "SELECT fo.id
                        FROM {format_tiles_tile_options} fo
                        JOIN {files} f ON f.itemid = fo.elementid AND f.contextid = :coursecontextid
                        AND f.component = 'format_tiles' AND f.filearea = 'tilephoto' AND f.filepath = '/tilephoto/'
                        AND f.filesize > 0 AND f.filename != '.'
                        WHERE fo.optiontype = :optiontype",
                    ['coursecontextid' => $context->id, 'optiontype' => format_option::OPTION_SECTION_PHOTO]
                );
                $needsunmappedfilesadded = !$hasmappedfiles;
            }
            if ($needsunmappedfilesadded) {
                // Moodle 3.x archives just use the course format options table with no image mapping.
                // Add these unmapped here for old plugin versions which are not mapped (null mappingitemname).
                $fileapiparams = \format_tiles\local\tile_photo::file_api_params();
                $this->add_related_files(
                    $fileapiparams['component'],
                    $fileapiparams['filearea'],
                    null
                );
            }

            $currentfilterbarsetting = $DB->get_record(
                'course_format_options',
                ['name' => 'displayfilterbar', 'format' => 'tiles', 'courseid' => $newcourseid]
            );

            $isoutcomesfilter = $currentfilterbarsetting &&
                in_array(
                    $currentfilterbarsetting->value,
                    [format_option::FILTER_OUTCOMES_ONLY, format_option::FILTER_OUTCOMES_AND_NUMBERS]
                );
            if ($isoutcomesfilter) {
                // If the new course has the filter bar set to use outcomes then switch it.
                // Tile outcomes will not work correctly in the new course as they include ids from the old course.
                // This is a temporary solution until the tile outcomes code can be refactored not to use outcome ids.
                $newrecord = new stdClass();
                $newrecord->id = $currentfilterbarsetting->id;
                if ($currentfilterbarsetting->value == format_option::FILTER_OUTCOMES_ONLY) {
                    $newrecord->value = format_option::FILTER_NONE;
                    $DB->update_record('course_format_options', $newrecord);
                } else {
                    $newrecord->value = format_option::FILTER_NUMBERS_ONLY;
                    $DB->update_record('course_format_options', $newrecord);
                }

                // Delete references to tile outcomes under section format options (now incorrect in restored course).
                // Users will have to set out up outcomes in new course for now if they want to.
                $DB->delete_records(
                    'course_format_options',
                    ['name' => 'tileoutcomeid', 'format' => 'tiles', 'courseid' => $newcourseid]
                );
            }

            // The name of course format option "defaulttileicon" for a course used to be "defaulttiletopleftdisplay".
            // Before this was changed for clarity in summer 2018 release, so change it if present in the backup if present.
            // Same for the topic level option "tiletopleftthistile" which becomes "tileicon".
            $DB->set_field(
                'course_format_options',
                'name',
                'defaulttileicon',
                ['format' => 'tiles', 'name' => 'defaulttiletopleftdisplay', 'courseid' => $newcourseid]
            );
            $DB->set_field(
                'course_format_options',
                'name',
                'tileicon',
                ['format' => 'tiles', 'name' => 'tiletopleftthistile', 'courseid' => $newcourseid]
            );

            // Old versions of this plugin used to refer to "course default" for each icon if the user had not selected one.
            // This no longer applies so delete them if present.
            $DB->delete_records_select(
                'course_format_options',
                "format  = 'tiles' AND name = 'tileicon' AND value = 'course default' AND courseid = :courseid",
                ["courseid" => $newcourseid]
            );

            $data = $this->connectionpoint->get_data();
            if (!isset($data['tags']['numsections']) || !$this->need_restore_numsections()) {
                // Backup file does not even have 'numsections' or was made in Moodle 3.3+, we don't need to process 'numsections'.
                return;
            }

            $numsections = (int)$data['tags']['numsections'];
            // Check each section from the backup file.
            // If it was "orphaned" in the original course, mark it as hidden.
            // This will leave all activities in it visible and available just as it was in the original course.
            // Exception is when we restore with merging and the course already had a section with this section number.
            // In this case we don't modify the visibility.
            $backupinfo = $this->step->get_task()->get_info();
            foreach ($backupinfo->sections as $key => $section) {
                if ($this->step->get_task()->get_setting_value($key . '_included')) {
                    $sectionnum = (int)$section->title;
                    if ($sectionnum > $numsections && $sectionnum > $this->originalnumsections) {
                        $DB->execute(
                            "UPDATE {course_sections} SET visible = 0 WHERE course = ? AND section = ?",
                            [$newcourseid, $sectionnum]
                        );
                    }
                }
            }

            // While we are here, delete any temp tile photo files (we don't expect any but just in case).
            $fs = get_file_storage();
            $fs->delete_area_files(context_course::instance($newcourseid)->id, 'format_tiles', 'temptilephoto');
        }
    }

    /**
     * Tile image file record needs updating to have section ids from new section not old.
     * Restore process will have created the file in files table but given it old section id.
     * This handles it and section ids from the new sections end up in {files} table.
     * @param int $newcourseid
     * @param int $contextid
     * @param int $olditemid the old itemid (is used to contain section id for section tiles)
     * @param int $newitemid the new itemid (is used to contain section id for section tiles)
     * @return void
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private static function update_file_records_sections($newcourseid, $contextid, $olditemid, $newitemid) {
        global $DB;
        $fileids = $DB->get_fieldset_select(
            'files',
            'id',
            "contextid = :coursecontextid AND component = 'format_tiles'
            AND filearea = 'tilephoto' AND filepath = '/tilephoto/'
            AND itemid = :olditemid AND filesize > 0 AND filename != '.'",
            ['coursecontextid' => $contextid, 'olditemid' => $olditemid]
        );
        if (!empty($fileids)) {
            // This won't be executed if the backup was done with tiles plugin version higher than 2024021400.
            // In that case $fileids should be empty as nothing needs updating.
            $fs = get_file_storage();
            foreach ($fileids as $fileid) {
                $oldfile = $fs->get_file_by_id($fileid);
                if ($oldfile && $oldfile->get_itemid() != $newitemid) {
                    // We have a file in the table with the old section id.
                    // However if we are merging a backup into an existing course, the new section may already have a photo too.
                    // We have to delete it if it does, before we give new photos the new section uid.
                    \format_tiles\local\tile_photo::delete_files_from_ids($newcourseid, $newitemid);

                    // Issue #165 only pass in needed fields (avoid core converting a null referencefileid to zero MDL-80938).
                    $newfilerecord = (object)[
                        'contextid' => $oldfile->get_contextid(),
                        'component' => $oldfile->get_component(),
                        'filearea' => $oldfile->get_filearea(),
                        'filepath' => $oldfile->get_filepath(),
                        'itemid' => $newitemid, // The new file needs the new section id.
                        'filename' => $oldfile->get_filename(),
                    ];
                    $fs->create_file_from_storedfile($newfilerecord, $oldfile);

                    // And finally delete the old file.
                    $oldfile->delete();
                }
            }
        }
    }

    /**
     * Process an item for format_tiles_tile_options table.
     * @param int $courseid
     * @param int $sectionid
     * @param int $optiontype
     * @param string $optionvalue
     * @throws moodle_exception
     */
    private function set_format_opt(int $courseid, int $sectionid, int $optiontype, string $optionvalue) {
        try {
            // It shouldn't happen but, if there was both a photo and an icon in the backup, only set the photo.
            if ($optiontype == format_option::OPTION_SECTION_ICON) {
                $photoexists = format_option::get($courseid, format_option::OPTION_SECTION_PHOTO, $sectionid);
                if ($photoexists) {
                    return false;
                }
            }
            $res = format_option::set(
                $courseid,
                $optiontype,
                $sectionid,
                $optionvalue
            );
            if (!$res) {
                debugging('Error could not set file/icon name ' . $optionvalue, DEBUG_DEVELOPER);
            }
            return $res;
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
            throw new moodle_exception(
                'invalidrecordid',
                'format_tiles',
                '',
                'Could not insert photo. Database table format_tiles_tile_options is not ready.' .
                '  An administrator must visit the notifications section.'
            );
        }
    }

    /**
     * Issue 45.
     * If incompatible Moodle 3.7 version of Tiles plugin was used in Moodle 3.9, incorrectly numbered sections may exist.
     * To avoid creating a empty sections on import or restore, check for incorrect sections and throw error if found.
     * @throws moodle_exception
     */
    private function fail_if_course_includes_excess_sections() {
        $backupinfo = $this->step->get_task()->get_info();
        $maxallowed = \format_tiles\local\course_section_manager::get_max_sections();

        // Get the sections from the backup and check them one by one.
        $totalincluded = 0;
        if (empty($backupinfo->sections)) {
            return;
        }
        foreach ($backupinfo->sections as $section) {
            $isrealsection = ($section->modname ?? null) !== 'subsection';
            if (!$isrealsection) {
                // We are not counting subsections.
                continue;
            }

            // Is the section included or has the user excluded it (unchecked box)?  Ignore if excluded.
            $sectionid = $section->sectionid;
            $included = $this->get_setting_value('section_' . $sectionid . '_included');
            if ($included) {
                $totalincluded++;
                if ($totalincluded > ($maxallowed + 1) * 5) {
                    // Allowing this section would mean we have too many secs - disallow.
                    // Legacy check, included in 2020 to protect against issue 45 and should be very unlikely to happen now.
                    // Check can probably be removed soon.
                    \core\notification::error(get_string('restoretoomanysections', 'format_tiles', $maxallowed));
                    throw new moodle_exception('restoretoomanysections', 'format_tiles', '', $maxallowed);
                }
            }
        }
    }

    /**
     * Check the destination course does not have a section number more than the max.
     * If it does, we cannot allow the restore.
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function check_destination_course_section_count() {
        global $SESSION;
        $maxallowed = \format_tiles\local\course_section_manager::get_max_sections();
        $courseid = $this->step->get_task()->get_courseid();
        $sessionvar = 'restore_dest_check_' . $courseid;
        if (isset($SESSION->$sessionvar) && $SESSION->$sessionvar > strtotime('2 minutes ago')) {
            // We've already done this very recently (probably in the same restore process) so don't need to do it now.
            return true;
        }

        // If we are importing into a course we want to check it doesn't have too many sections already.
        // This is a legacy check which was included in 2020 to protect against issue 45 and should be very unlikely to happen now.
        // Check can probably be removed soon.
        if (\format_tiles\local\course_section_manager::course_section_count_exceeds($courseid, $maxallowed * 5)) {
            $a = new stdClass();
            $a->sectionnum = \format_tiles\local\course_section_manager::get_max_non_subsection_number($courseid);
            $a->maxallowed = $maxallowed;
            \core\notification::error(get_string('restoreincorrectsections', 'format_tiles', $a));
            throw new moodle_exception('restorefailed', 'format_tiles', '', $a);
        }

        if (!defined('BEHAT_SITE_RUNNING')) {
            // Don't set this if running behat tests, as it produces error which does not appear in regular use.
            // "Script /moodle/backup/restore.php mutated the session after it was closed ...".
            $SESSION->$sessionvar = time();
        }

        return true;
    }

    /**
     * Most of the public methods in this class are called on restore of all courses, not just Tiles format.
     * In some cases we check that the format we are restoring is Tiles and only act if it is.
     * @return bool
     */
    private function is_tiles_course(): bool {
        $backupinfo = $this->step->get_task()->get_info();
        return isset($backupinfo->original_course_format) && $backupinfo->original_course_format == 'tiles';
    }
}
