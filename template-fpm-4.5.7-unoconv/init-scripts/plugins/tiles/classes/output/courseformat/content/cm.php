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
 * Contains the default activity list from a section.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content;

use core_courseformat\output\local\content\cm as core_cm;

/**
 * Class to render a course module inside a Tiles course format.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends core_cm {

    /**
     * Add activity information to the data structure.
     *
     * @param \stdClass $data the current cm data reference
     * @param bool[] $haspartials the result of loading partial data elements
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has format data
     */
    protected function add_format_data(\stdClass &$data, array $haspartials, \renderer_base $output): bool {

        $parentadded = parent::add_format_data($data, $haspartials, $output);
        $data->cmtitle = $this->mod->get_formatted_name();

        // See also the higher level section where moodle release info is added e.g. ismoodle42minus.
        // I.e. format_tiles\output\courseformat\content\section.
        // However we can't rely on that here.
        // The cm templates inherit from that in edit view, but not when we use fragment API to get a cm list.
        $moodlerelease = \format_tiles\local\util::get_moodle_release();
        $data->ismoodle42minus = $moodlerelease <= 4.2;
        $data->ismoodle41minus = $moodlerelease <= 4.1;
        $data->ismoodle44 = $moodlerelease === 4.4;
        $data->ismoodle40 = $moodlerelease === 4.0;
        $data->modcontextid = $this->mod->context->id;
        $data->cmtitle = $this->mod->get_formatted_name();
        $childadded = true; // We did add some data above.
        return $parentadded || $childadded;
    }
}
