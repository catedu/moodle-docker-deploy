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
 * Completion Progress block.
 *
 * @package    block_completion_progress
 * @copyright  2026 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_completion_progress\external;

use block_completion_progress\completion_progress;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Block instance data service.
 */
class get_blockinstance_data extends external_api {
    /**
     * Return the parameters.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'The course id.', VALUE_REQUIRED),
                'instanceid' => new external_value(PARAM_INT, 'The block instance id.', VALUE_REQUIRED),
                'userid' => new external_value(PARAM_INT, 'The user id.', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Return the response layout.
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'courseid' => new external_value(PARAM_INT, 'The course id.', VALUE_REQUIRED),
                'instanceid' => new external_value(PARAM_INT, 'The block instance id.', VALUE_REQUIRED),
                'userid' => new external_value(PARAM_INT, 'The user id.', VALUE_REQUIRED),
                'simple' => new external_value(PARAM_BOOL, 'Whether the block is simple.', VALUE_REQUIRED),
                'useicons' => new external_value(PARAM_BOOL, 'Whether the block cells should show icons.', VALUE_REQUIRED),
                'novisibleactivities' => new external_value(PARAM_BOOL, 'Whether any activities are visible.', VALUE_OPTIONAL),
                'cellsperrow' => new external_value(PARAM_INT, 'The number of cells per row in wrap mode.', VALUE_OPTIONAL),
                'displaynow' => new external_value(PARAM_BOOL, 'Whether to show the "now" indicator.', VALUE_OPTIONAL),
                'barwrap' => new external_value(PARAM_BOOL, 'Whether to render in wrap mode.', VALUE_OPTIONAL),
                'barscroll' => new external_value(PARAM_BOOL, 'Whether to render in scroll mode.', VALUE_OPTIONAL),
                'barsqueeze' => new external_value(PARAM_BOOL, 'Whether to render in squeeze mode.', VALUE_OPTIONAL),
                'cells' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'activityid' => new external_value(PARAM_INT, 'The course module id.', VALUE_REQUIRED),
                            'haslink' => new external_value(
                                PARAM_ALPHANUMEXT,
                                'Whether the course module has a view link (true/false/not-allowed).',
                                VALUE_OPTIONAL
                            ),
                            'firstnow' => new external_value(
                                PARAM_BOOL,
                                'Whether the "now" indicator points to the start of the bar.',
                                VALUE_OPTIONAL
                            ),
                            'firsthalfnow' => new external_value(
                                PARAM_BOOL,
                                'Whether the "now" indicator points to the left half of the bar.',
                                VALUE_OPTIONAL
                            ),
                            'lasthalfnow' => new external_value(
                                PARAM_BOOL,
                                'Whether the "now" indicator points to the right half of the bar.',
                                VALUE_OPTIONAL
                            ),
                            'submittednotcomplete' => new external_value(
                                PARAM_BOOL,
                                'Whether the cell is "submitted, not complete" state.',
                                VALUE_OPTIONAL
                            ),
                            'completed' => new external_value(
                                PARAM_BOOL,
                                'Whether the cell is "completed" state.',
                                VALUE_OPTIONAL
                            ),
                            'notcompleted' => new external_value(
                                PARAM_BOOL,
                                'Whether the cell is "not completed" state.',
                                VALUE_OPTIONAL
                            ),
                            'futurenotcompleted' => new external_value(
                                PARAM_BOOL,
                                'Whether the cell is "future not completed" state.',
                                VALUE_OPTIONAL
                            ),
                            'activitylink' => new external_value(
                                PARAM_URL,
                                'The course module view url.',
                                VALUE_OPTIONAL
                            ),
                            'activityicon' => new external_value(
                                PARAM_LOCALURL,
                                'The course module icon url.',
                                VALUE_REQUIRED
                            ),
                            'activityname' => new external_value(
                                PARAM_RAW,
                                'The course module name',
                                VALUE_REQUIRED
                            ),
                            'activityonclick' => new external_value(
                                PARAM_RAW,
                                'The course module onclick javascript.',
                                VALUE_OPTIONAL
                            ),
                            'infoicon' => new external_value(
                                PARAM_ALPHANUM,
                                'What kind of icon to show.',
                                VALUE_OPTIONAL
                            ),
                            'infocomplete' => new external_value(
                                PARAM_BOOL,
                                'Whether to show "complete" info text.',
                                VALUE_OPTIONAL
                            ),
                            'infopassed' => new external_value(
                                PARAM_BOOL,
                                'Whether to show "passed" info text.',
                                VALUE_OPTIONAL
                            ),
                            'infofailed' => new external_value(
                                PARAM_BOOL,
                                'Whether to show "failed" info text.',
                                VALUE_OPTIONAL
                            ),
                            'infoincomplete' => new external_value(
                                PARAM_BOOL,
                                'Whether to show "incomplete" info text.',
                                VALUE_OPTIONAL
                            ),
                            'infosubmitted' => new external_value(
                                PARAM_BOOL,
                                'Whether to show "submitted" info text.',
                                VALUE_OPTIONAL
                            ),
                        ]
                    )
                ),
                'progresspercentage' => new external_value(PARAM_RAW, 'The course percentage complete value.', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Return the data.
     *
     * @param int $courseid
     * @param int $instanceid
     * @param int $userid
     * @return array
     */
    public static function execute($courseid, $instanceid, $userid) {
        global $DB, $PAGE, $USER;

        [
            'courseid' => $courseid,
            'instanceid' => $instanceid,
            'userid' => $userid,
        ] = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'instanceid' => $instanceid,
                'userid' => $userid,
            ]
        );

        $context = \context_course::instance($courseid);
        self::validate_context($context);

        require_capability('block/completion_progress:showbar', $context);

        $course = get_course($courseid);
        $blockinstance = $DB->get_record('block_instances', ['id' => $instanceid], '*', MUST_EXIST);

        $progress = new completion_progress($course);
        if (!$progress->get_completion_info()->is_enabled()) {
            throw new \moodle_exception('completion_not_enabled_course', 'block_completion_progress');
        }

        $progress->for_user($USER)->for_block_instance($blockinstance);
        if (!$progress->has_visible_activities()) {
            throw new \moodle_exception('no_activities_config_message', 'block_completion_progress');
        }

        $output = $PAGE->get_renderer('block_completion_progress');
        return $progress->export_for_template($output);
    }
}
