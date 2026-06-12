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
 * Tiles course format, before course header content output class to prepare data for mustache templates
 *
 * @package format_tiles
 * @copyright 2025 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles\output;

/**
 * Tiles course format, before course header content output class to prepare data for mustache templates
 * @copyright 2025 David Watson
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_header_content extends \format_tiles\output\course_output {

    /**
     * Export the course data for the mustache template.
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(\renderer_base $output) {
        $data = parent::export_for_template($output);
        $thissection = $this->modinfo->get_section_info($this->format->get_sectionnum());
        $data['isdelegatedsection'] = $thissection->is_delegated() ?? false;
        if ($data['isdelegatedsection']) {
            $parentcm = $thissection->get_component_instance();
            $parentsection = $parentcm->get_parent_section();
            $data['parent_tile'] = [
                'id' => $parentsection->id,
                'title' => $this->format->get_section_name($parentsection),
            ];
        } else {
            $previousnext = $this->get_previous_next_section_ids($thissection->section);
            $data['previous_tile_id'] = $previousnext['previous'];
            $data['next_tile_id'] = $previousnext['next'];
        }
        return $data;
    }
}
