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
 * Section renderer for tiles format.
 *
 * @package   format_tiles
 * @copyright 2023 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output;

/**
 * Section renderer for tiles format.
 *
 * @package   format_tiles
 * @copyright 2023 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_renderer extends \core_courseformat\output\section_renderer {

    /**
     * When a teacher uses bulk editing to hide a section, fragment API re-renders the section using this method.
     * Also used when teacher uses show/hide on whole section using JS and it is re-rendered with icon or photo.
     * @param \renderable $widget
     * @see \format_tiles\output\renderer::course_section_updated()
     * @return bool|string
     * @throws \moodle_exception
     */
    public function render_section(\renderable $widget) {
        return $this->render_from_template(
            'format_tiles/local/content/section',
            $widget->export_for_template($this)
        );
    }
}
