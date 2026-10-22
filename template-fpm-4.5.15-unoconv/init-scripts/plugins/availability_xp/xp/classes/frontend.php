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
 * Frontend file.
 *
 * @package    availability_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_xp;

/**
 * Frontend class.
 *
 * @package    availability_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /**
     * Decides whether this plugin should be available in a given course. The
     * plugin can do this depending on course or system settings.
     *
     * @param stdClass $course Course object
     * @param \cm_info $cm Course-module currently being edited (null if none)
     * @param \section_info $section Section currently being edited (null if none)
     * @return bool False when adding is disabled.
     */
    protected function allow_add($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        $world = \block_xp\di::get('course_world_factory')->get_world($course->id);
        return $world->get_config()->get('enabled');
    }

    /**
     * Gets a list of string identifiers (in the plugin's language file) that
     * are required in JavaScript for this plugin. The default returns nothing.
     *
     * @return array Array of required string identifiers
     */
    protected function get_javascript_strings() {
        return ['levelis', 'levelconditionoperator', 'levelnumber', 'levelgreaterorequalto', 'opequalto', 'opgreaterorequalto'];
    }

    /**
     * Gets additional parameters for the plugin's initInner function.
     *
     * @param stdClass $course Course object
     * @param \cm_info $cm Course-module currently being edited (null if none)
     * @param \section_info $section Section currently being edited (null if none)
     * @return array Array of parameters for the JavaScript function
     */
    protected function get_javascript_init_params($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        $world = \block_xp\di::get('course_world_factory')->get_world($course->id);
        $levelsinfo = $world->get_levels_info();
        return [(object) ['levels' => $levelsinfo->get_count()]];
    }
}
