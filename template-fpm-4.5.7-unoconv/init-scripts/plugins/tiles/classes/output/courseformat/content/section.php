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
 * Contains the default section course format output class.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content;

use core_courseformat\output\local\content\section as section_base;
use format_tiles\local\tile_photo;
use format_tiles\local\util;

/**
 * Base class to render a course section.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): \stdClass {
        $data = parent::export_for_template($output);

        $data->hasphoto = 0;
        // If photo tile backgrounds are allowed by site admin, prepare the image for this section.
        if (get_config('format_tiles', 'allowphototiles')) {
            $coursecontext = $this->format->get_context();
            // Is getting course context the most efficient way?
            $tilephoto = new tile_photo($coursecontext, $this->section->id);
            $tilephotourl = $tilephoto->get_image_url();
            if ($tilephotourl) {
                $data->hasphoto = 1;
                $data->phototileinlinestyle = 'style = "background-image: url(' . $tilephotourl . ');"';
                $data->hastilephoto = $tilephotourl ? 1 : 0;
                $data->phototileurl = $tilephotourl;
                $data->phototileediturl = new \moodle_url(
                    '/course/format/tiles/editor/editimage.php',
                    ['sectionid' => $this->section->id]
                );
            }

        }
        if (!$data->hasphoto) {
            $data->tileicon = \format_tiles\local\format_option::get(
                $this->section->course, \format_tiles\local\format_option::OPTION_SECTION_ICON, $this->section->id
            );
            if (!$data->tileicon) {
                $formatoptions = $this->format->get_format_options();
                $data->tileicon = $formatoptions['defaulttileicon'];
            } else {
                $data->tilenumber = $data->tileicon ? util::get_tile_number_from_icon_name($data->tileicon) : null;
            }
        }

        if (!$this->format->get_sectionnum()) {
            $addsectionclass = $this->format->get_output_classname('content\\addsection');
            $addsection = new $addsectionclass($this->format);
            $data->numsections = $addsection->export_for_template($output);
            $data->insertafter = true;
        }
        if ($data->num === 0) {
            $data->collapsemenu = true;
        }

        $moodlerelease = \format_tiles\local\util::get_moodle_release();
        $data->ismoodle42minus = $moodlerelease <= 4.2;
        $data->ismoodle41minus = $moodlerelease <= 4.1;
        $data->tilestyle = get_config('format_tiles', 'tilestyle') ?? 1;

        return $data;
    }
}
