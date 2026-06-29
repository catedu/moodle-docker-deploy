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
 * Contains the section header class.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content\section;

/**
 * Class to render a section header inside a Tiles course format.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class header extends \core_courseformat\output\local\content\section\header {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return object data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): \stdClass {

        $format = $this->format;
        $course = $format->get_course();

        $section = $this->section;
        $data = (object)['num' => $section->section, 'id' => $section->id, 'issectionzero' => $section->section == 0];

        $data->title = $output->section_title_without_link($section, $course);

        $data->editing = $format->show_editor();
        $coursedisplay = $format->get_course_display();
        $data->headerdisplaymultipage = false;
        if ($coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
            $data->headerdisplaymultipage = true;
            $data->title = $output->section_title($section, $course);
        }

        if ($section->section > $format->get_last_section_number()) {
            // Stealth sections (orphaned) has special title.
            $data->title = get_string('orphanedactivitiesinsectionno', '', $section->section);
        }

        if (!$section->visible) {
            $data->ishidden = true;
        }

        if ($course->id == SITEID) {
            $data->sitehome = true;
        }

        $data->editing = $format->show_editor();

        if (!$format->show_editor() && $coursedisplay == COURSE_DISPLAY_MULTIPAGE && empty($data->issinglesection)) {
            if ($section->uservisible) {
                $data->url = course_get_url($course, $section->section);
            }
        }
        $data->name = get_section_name($course, $section);
        $data->selecttext = $format->get_format_string('selectsection', $data->name);
        if (!$format->get_sectionnum()) {
            $data->sectionbulk = true;
        }
        $data->isdelegatedsection = $section->is_delegated() ?? false;
        return $data;
    }
}
