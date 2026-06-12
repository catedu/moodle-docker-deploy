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
 * Contains the activity item from a section.
 *
 * @package   format_tiles
 * @copyright 2024 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content\section;

use renderer_base;
use stdClass;

/**
 * Class to render a section activity in the activities list.
 *
 * @package   format_tiles
 * @copyright 2024 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmitem extends \core_courseformat\output\local\content\section\cmitem {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        $data = parent::export_for_template($output);

        $data->modname = $this->mod->modname;
        $data->isdelegatedsection = $this->mod->modname == 'subsection';
        $data->modalType = \format_tiles\local\modal_helper::cm_modal_type($this->mod->course, $this->mod->id);
        $data->cmid = $this->mod->id;
        $data->modcontextid = $this->mod->context->id;
        $data->modinstance = $this->mod->instance;
        $data->title = $this->mod->get_formatted_name();
        $data->completion = $this->mod->completion;
        return $data;
    }
}
