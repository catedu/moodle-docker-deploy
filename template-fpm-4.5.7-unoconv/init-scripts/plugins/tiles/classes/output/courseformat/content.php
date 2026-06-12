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
 * Contains the default content output class.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat;


use core_courseformat\output\local\content as content_base;

/**
 * Format tiles class to render course content.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE, $DB, $USER;
        $isediting = $PAGE->user_is_editing();

        $data = parent::export_for_template($output);
        $data->editoradvice = [];

        $moodlerelease = \format_tiles\local\util::get_moodle_release();
        $data->ismoodle42minus = $moodlerelease <= 4.2;
        $data->ismoodle41minus = $moodlerelease <= 4.1;

        // For now this class is only used if user is editing but check anyway as one day it will be used when not editing.
        if ($isediting) {
            $isadmin = is_siteadmin($USER->id);
            $courseformatoptions = $this->format->get_format_options();

            $course = $this->format->get_course();

            if (get_config('format_tiles', 'allowsubtilesview')
                && isset($courseformatoptions['courseusesubtiles']) && $courseformatoptions['courseusesubtiles']) {
                // For now (Beta version) we warn editor about sub tiles only appearing in non-edit view.
                $messgage = get_string('editoradvicesubtiles', 'format_tiles');
                if (has_capability('moodle/site:config', \context_system::instance())) {
                    $messgage .= ' ('
                        . get_string('version', 'format_tiles', \format_tiles\local\util::get_tiles_plugin_release()) . ')';
                }
                $data->editoradvice[] = ['text' => $messgage, 'icon' => 'info-circle', 'class' => 'secondary'];

                // Inline folders will not display in sub-tiles view so warn if present.
                $folderwarning = self::get_inline_folder_warning($course->id);
                if ($folderwarning) {
                    $data->editoradvice[] = ['text' => $folderwarning, 'icon' => 'exclamation-triangle', 'class' => 'warning'];
                }
            }
            // If completion tracking is on but nothing to track at activity level, display help to teacher.
            $warneditorcompletion = $course->enablecompletion
                && $DB->record_exists('course_modules', ['course' => $course->id, 'visible' => 1])
                && !$DB->record_exists_sql(
                "SELECT id FROM {course_modules} WHERE course = ? AND visible = 1 AND completion != 0",
                [$course->id]
            );

            if ($warneditorcompletion) {
                $bulklink = \html_writer::link(
                  new \moodle_url('/course/bulkcompletion.php', ['id' => $course->id]),
                  get_string('completionwarning_changeinbulk', 'format_tiles')
                );
                $helplink = \html_writer::link(
                    get_docs_url('Activity_completion_settings#Changing_activity_completion_settings_in_bulk'),
                    $output->pix_icon('help', '', 'core')
                );
                $data->editoradvice[] = [
                    'text' => get_string('completionwarning', 'format_tiles') . ' '  . $bulklink . ' ' . $helplink,
                    'icon' => 'info-circle', 'class' => 'secondary',
                ];
            }

            $hasbulkedittools = \format_tiles\local\util::get_moodle_release() >= 4.2
                && isset($this->bulkedittoolsclass)
                && class_exists($this->bulkedittoolsclass);
            if ($hasbulkedittools) {
                $bulkedittools = new $this->bulkedittoolsclass($this->format);
                $data->bulkedittools = $bulkedittools->export_for_template($output);
            }

            // Check if the course photos and icons have not yet finished migrating (4.3 upgrade) and alert if so.
            if ($moodlerelease >= 4.0 && \format_tiles\local\format_option::needs_migration_incomplete_warning($course->id)) {
                $message = get_string('coursephotomigrationincomplete', 'format_tiles');
                if ($isadmin) {
                    $message .= \html_writer::link(
                        new \moodle_url('/course/format/tiles/editor/migratecoursedata.php'),
                        '<i class="icon fa fa-cog me-1"></i>' . get_string('fixproblems', 'format_tiles'),
                        ['class' => 'ms-1']
                    );
                }
                $data->editoradvice[] = [
                   'text' => $message,
                   'icon' => 'exclamation-triangle', 'class' => 'warning',
                ];

                // Migration depends on cron so warning admin if not running.
                if ($isadmin) {
                    $check = new \tool_task\check\cronrunning();
                    $result = $check->get_result();
                    if ($result->get_status() !== $result::OK) {
                        $data->editoradvice[] =
                            ['text' => $result->get_summary(), 'icon' => 'exclamation-triangle', 'class' => 'warning'];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Folders set to display inline will not work this format (if using subtiles).
     * Find any instance so we can warn editing user.
     * @param int $courseid the course ID.
     */
    private function get_inline_folder_warning(int $courseid): ?string {
        global $DB, $CFG;
        require_once("$CFG->dirroot/mod/folder/lib.php");
        $inlinefolder = $DB->get_record_sql(
            "SELECT cm.id, f.name
            FROM {folder} f
            JOIN {course_modules} cm ON cm.instance = f.id
            JOIN {modules} m ON m.id = cm.module and m.name = 'folder'
            WHERE f.course = :courseid and f.display = :display LIMIT 1",
            ['courseid' => $courseid, 'display' => FOLDER_DISPLAY_INLINE]
        );
        if ($inlinefolder) {
            $editurl = new \moodle_url('/course/modedit.php', ['update' => $inlinefolder->id]);
            $link = \html_writer::link($editurl, $inlinefolder->name);
            return get_string('folderdisplayerror', 'format_tiles', $link);
        }
        return null;
    }
}
