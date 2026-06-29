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
 * Course section manager class for format tiles.
 * @package    format_tiles
 * @copyright  2020 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\local;

/**
 * Course section manager class for format tiles.
 * @package    format_tiles
 * @copyright  2020 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_section_manager {
    /**
     * Get the URL the user needs to list problem courses in this environment.
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public static function get_list_problem_courses_url() {
        return new \moodle_url(
            '/course/format/tiles/editor/admintools.php',
            ['action' => 'listproblemcourses', 'sesskey' => sesskey()]
        );
    }

    /**
     * Get the max number of sections allowed in a course.
     * @return int
     * @throws \dml_exception
     */
    public static function get_max_sections(): int {
        $maxsections = get_config('moodlecourse', 'maxsections');
        if (!$maxsections || !is_numeric($maxsections)) {
            $maxsections = 52;
        }
        return (int)$maxsections;
    }

    /**
     * Does course section count (excl section zero and sub-sections) exceed threshold?
     * @param int $courseid the course ID we are checking
     * @param int $threshold the threshold we are checking
     * @return bool whether the section count exceeds the threshold
     */
    public static function course_section_count_exceeds(int $courseid, int $threshold): bool {
        $count = 0;
        $modinfo = get_fast_modinfo($courseid);
        $secsall = $modinfo->get_section_info_all();
        foreach ($secsall as $section) {
            if ($section->section > 0 && !$section->is_delegated()) {
                $count++;
                if ($count > $threshold) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get the section number of the highest non-subsection section in the course.
     * @param int $courseid
     * @return int|null
     * @throws \moodle_exception
     */
    public static function get_max_non_subsection_number(int $courseid): ?int {
        $modinfo = get_fast_modinfo($courseid);
        $secsall = $modinfo->get_section_info_all();
        $returnvalue = null;
        foreach ($secsall as $section) {
            if ($section->section > 0 && !$section->is_delegated()) {
                $returnvalue = $section->section;
            }
        }
        return $returnvalue;
    }
}
