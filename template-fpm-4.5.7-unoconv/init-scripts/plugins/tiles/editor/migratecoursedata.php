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
 * Page called by administrator to migrate course data (for addressing any issues on 4.3 upgrade).
 *
 * @package format_tiles
 * @copyright 2023 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

global $PAGE, $DB, $OUTPUT;

require_login();
$systemcontext = context_system::instance();
$pagecontext = null;

$issiteadmin = has_capability('moodle/site:config', $systemcontext);
if ($issiteadmin) {
    // Site admins can see overview page with all courses.
    $courseid = optional_param('courseid', 0, PARAM_INT);
    $pagecontext = $courseid ? context_course::instance($courseid) : $systemcontext;
} else {
    // Teacher must pass a course ID to fix one course to which they have access.
    $courseid = required_param('courseid', PARAM_INT);
    $pagecontext = context_course::instance($courseid);
    require_capability('moodle/course:update', $pagecontext);
}

$pageurlparams = $courseid ? ['courseid' => $courseid] : null;
$pageurl = new moodle_url('/course/format/tiles/editor/migratecoursedata.php', $pageurlparams);
$PAGE->set_url($pageurl);
$PAGE->set_context($pagecontext);

if ($courseid) {
    $courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
    $course = $DB->get_record('course', ['id' => $courseid, 'format' => 'tiles']);
    if (!$course) {
        debugging("Course $courseid is not found or not a tiles course");
        redirect($courseurl, get_string('error'), null, \core\output\notification::NOTIFY_ERROR);
    }
    $PAGE->set_heading($course->fullname);
    $PAGE->set_course($course);

    $countlegacyoptions = $DB->get_field_sql(
        "SELECT COUNT(cfo.id) FROM {course_format_options} cfo
                WHERE cfo.courseid = ? AND  cfo.format = 'tiles'
                AND cfo.name IN('tilephoto', 'tileicon')",
        [$courseid]
    );
    if (!$countlegacyoptions) {
        debugging("No legacy options found for course $courseid");
        redirect($courseurl, get_string('error'), null, \core\output\notification::NOTIFY_ERROR);
    }

    // In this case we need to process the course now, if sesskey is present.
    $sesskey = optional_param('sesskey', '', PARAM_TEXT);
    if ($sesskey) {
        require_sesskey();
        \format_tiles\local\format_option::migrate_legacy_format_options($courseid);
        \core\notification::success(
            get_string('migratedcourseid', 'format_tiles', $courseid)
            . '&nbsp;' . html_writer::link($courseurl, $course->fullname)
        );
        redirect($issiteadmin ? $pageurl->out_omit_querystring() : $courseurl);
    } else {
        // We need to ask the user if they are sure.
        echo $OUTPUT->header();
        echo html_writer::tag(
            'h4', get_string('suremigratelegacyoptions', 'format_tiles', $countlegacyoptions),
            ['class' => 'mb-3']
        );
        $continueurl = new moodle_url($pageurl, ['sesskey' => sesskey()]);
        echo html_writer::link($continueurl, get_string('continue'), ['class' => 'btn btn-danger']);
        echo html_writer::link($courseurl, get_string('cancel'), ['class' => 'btn btn-secondary ms-2']);
        echo $OUTPUT->footer();
        die();
    }
}

if (!$issiteadmin) {
    throw new \Exception("Not allowed");
}

// If we reach here we are not looking at a specific course - show admin full list of courses.
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'formatsettingtiles']);
$PAGE->set_heading(get_string('admintools', 'format_tiles'));
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('plugins', 'admin'), new moodle_url('/admin/category.php', ['category' => 'modules']));
$PAGE->navbar->add(get_string('courseformats'), new moodle_url('/admin/category.php', ['category' => 'formatsettings']));
$PAGE->navbar->add(get_string('pluginname', 'format_tiles'), $settingsurl);
$PAGE->navbar->add(get_string('migratecoursedata', 'format_tiles'));

$legacycourses = $DB->get_records_sql(
"SELECT * FROM
        (SELECT c.id as courseid, c.fullname,
            (SELECT COUNT(cfo.id) FROM {course_format_options} cfo
                WHERE cfo.courseid = c.id AND  cfo.format = 'tiles' AND cfo.name IN('tilephoto', 'tileicon')) as legacyoptions,
            (SELECT COUNT(tfo.id) FROM {format_tiles_tile_options} tfo
                WHERE tfo.courseid = c.id AND tfo.optiontype IN (?, ?)) as newoptions
        FROM {course} c
         ) counts
    WHERE counts.legacyoptions > 0",
    [\format_tiles\local\format_option::OPTION_SECTION_PHOTO, \format_tiles\local\format_option::OPTION_SECTION_ICON]
);
$table = new html_table();
$table->head = [
    get_string('course'),
    get_string('legacytiledata', 'format_tiles'),
    get_string('newtiledata', 'format_tiles'),
    get_string('migratenow', 'format_tiles'),
];
$table->data = [];
foreach ($legacycourses as $legacycourse) {
    $table->data[] = [
        html_writer::link(
            new moodle_url('/course/view.php', ['id' => $legacycourse->courseid]),
            $legacycourse->fullname
        ),
        $legacycourse->legacyoptions,
        $legacycourse->newoptions,
        html_writer::link(
            new moodle_url('/course/format/tiles/editor/migratecoursedata.php',
                ['courseid' => $legacycourse->courseid]),
            get_string('migratenow', 'format_tiles'),
            ['class' => 'btn btn-primary']
        ),
    ];
}
if (empty($table->data)) {
    $table->data[] = [get_string('none'), '', '', ''];
}
$croncheck = new \tool_task\check\cronrunning();
$cronresult = $croncheck->get_result();

echo $OUTPUT->header();
if ($cronresult->get_status() !== $cronresult::OK) {
    \core\notification::warning($cronresult->get_summary());
}
echo html_writer::div(get_string('unmigratedcoursesintro', 'format_tiles',  count($legacycourses)), 'mb-2');
echo html_writer::table($table);
echo $OUTPUT->footer();
