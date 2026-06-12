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
 * CLI script for course data migration on upgrade to tiles 4.3 version.
 * @see \format_tiles\task\migrate_legacy_data;
 * @package    format_tiles
 * @copyright  2023 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Developer should not need to run this script in most cases as it will be handled by upgrade.php.
// It is left in for now since it may be useful for troubleshooting.

define('CLI_SCRIPT', true);
require_once(__DIR__ . "/../../../../config.php");

$courseid = getopt('c:')['c'] ?? null;

if (!$courseid) {
    mtrace("Must provide course ID e.g. -c 123");
    die();
}

$coursename = $DB->get_field('course', 'fullname', ['id' => $courseid]);
if (!$coursename) {
    mtrace("Course $courseid not found");
    die();
}
$counttomigratesql = "SELECT COUNT(id) FROM {course_format_options}
    WHERE courseid = ? and format = 'tiles' AND name IN ('tilephoto', 'tileicon')";

$count = $DB->get_field_sql($counttomigratesql, [$courseid]);
if ($count == 0) {
    mtrace("Nothing to migrate");
    die();
}
mtrace("Migrating $count items for course ID $courseid");
mtrace("Course name: $coursename");
\format_tiles\local\format_option::migrate_legacy_format_options($courseid);

$countleft = $DB->get_field_sql($counttomigratesql, [$courseid]);
mtrace("Done - count left $countleft");
