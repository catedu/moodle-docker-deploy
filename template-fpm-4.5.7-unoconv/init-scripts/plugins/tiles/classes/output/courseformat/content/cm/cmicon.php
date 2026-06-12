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
 * Contains the default activity icon.
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content\cm;

/**
 * Class to render a course module icon.
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmicon extends \core_courseformat\output\local\content\cm\cmicon {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): array {
        $data = parent::export_for_template($output);
        if ($this->mod->modname == 'url') {
            if (\format_tiles\local\video_cm::is_video_cm($this->mod->course, $this->mod->id)) {
                $data['icon'] = $output->image_url('play', 'format_tiles');
                $data['pluginname'] = get_string('displaytitle_mod_mp4', 'format_tiles');
                $data['formattilesclass'] = 'format-tiles-video';
            }
        } else if (!\format_tiles\local\util::has_monologo_icon('mod', $this->mod->modname)) {
            $data['iconclass'] .= 'nofilter';
        }
        return $data;
    }
}
