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
 * Adhoc task handling migrating legacy course format data to the new table.
 *
 * @package    format_tiles
 * @copyright  2023 David Watson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\task;

/**
 * Adhoc task handling migrating legacy course format data to the new table.
 *
 * @package    format_tiles
 * @copyright  2023 David Watson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migrate_legacy_data extends \core\task\adhoc_task {

    /**
     * Run the migration task.
     */
    public function execute() {
        $courseid = $this->get_custom_data()->courseid;
        mtrace("format_tiles migrate_legacy_data course ID '$courseid'");
        if ($courseid) {
            \format_tiles\local\format_option::migrate_legacy_format_options($courseid);
        }
    }
}
