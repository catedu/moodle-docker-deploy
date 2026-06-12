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
 * Page called by teacher to reset all tiles in a course.
 *
 * @package format_tiles
 * @copyright 2023 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Included because course formats cannot use the callback {mod}_reset_course_form_definition().

require_once('../../../../config.php');

global $PAGE, $DB, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);
require_capability('moodle/site:config', context_system::instance());

if (optional_param('sesskey', '', PARAM_TEXT)) {
    require_sesskey();
    $result = \format_tiles\local\tile_photo::reset_tiles_course($courseid);
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        $result ? get_string('tilesreset', 'format_tiles')
            : get_string('resetincomplete', 'format_tiles') ,
        0,
        $result
            ? \core\output\notification::NOTIFY_SUCCESS
            : \core\output\notification::NOTIFY_WARNING
    );
} else {
    $PAGE->set_url(new moodle_url('/course/format/tiles/editor/reset.php', ['id' => $courseid]));
    $PAGE->set_context(context_course::instance($courseid));
    $course = get_course($courseid);
    $PAGE->set_heading($course->fullname);

    $message = html_writer::div(
    html_writer::div(get_string('resetalltilessure', 'format_tiles'), 'mb-2')
        . html_writer::link(
            new moodle_url('/course/format/tiles/editor/reset.php', ['courseid' => $courseid, 'sesskey' => sesskey()]),
            html_writer::tag('span', get_string('reset'), ['class' => 'btn btn-danger me-2'])
        )
        . html_writer::link(
            new moodle_url('/course/view.php', ['id' => $courseid]),
            html_writer::tag('span', get_string('cancel'), ['class' => 'btn btn-secondary me-2'])
        )
    );
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $message;
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}
