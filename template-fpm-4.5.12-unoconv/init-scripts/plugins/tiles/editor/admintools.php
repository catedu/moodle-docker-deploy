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
 * Page called by administrator to carry out admin functions from plugin settings page.
 *
 * @package format_tiles
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

use format_tiles\local\course_section_manager;

global $PAGE, $DB, $OUTPUT;

require_login();
$systemcontext = context_system::instance();

// Admins only for this page.
if (!has_capability('moodle/site:config', $systemcontext)) {
    throw new moodle_exception('You do not have permission to perform this action.');
}

$action = required_param('action', PARAM_TEXT);
$pageurl = new moodle_url('/course/format/tiles/editor/admintools.php', ['action' => $action]);
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'formatsettingtiles']);

$PAGE->set_url($pageurl);
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('admintools', 'format_tiles'));
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('plugins', 'admin'), new moodle_url('/admin/category.php', ['category' => 'modules']));
$PAGE->navbar->add(get_string('courseformats'), new moodle_url('/admin/category.php', ['category' => 'formatsettings']));
$PAGE->navbar->add(get_string('pluginname', 'format_tiles'), $settingsurl);
$PAGE->navbar->add(get_string('admintools', 'format_tiles'));

$o = '';

switch ($action) {
    case 'resetcolours':
        $o = reset_colours($settingsurl, $pageurl);
        break;
    case 'listproblemcourses':
        $o = list_problem_courses();
        break;
    default:
        break;
}

echo $OUTPUT->header();
echo $o;
echo $OUTPUT->footer();

/**
 * Get an array of all the permitted colour hex values allowed by site admin in plugin settings.
 * @package format_tiles
 * @return array
 * @throws dml_exception
 */
function permitted_colours() {
    global $DB;
    $records = $DB->get_records_select(
        'config_plugins',
        "plugin = 'format_tiles' AND " . $DB->sql_like('name', '?', false),
        ["tilecolour%"]
    );
    $permittedcolours = [];
    foreach ($records as $record) {
        if (hexdec(str_replace('#', '', $record->value)) !== 0) {
            // If the colour is #000 or #000000 we ignore as this means admin has disabled the colour.
            $permittedcolours[] = $record->value;
        }
    }
    return $permittedcolours;
}

/**
 * Function to allow site admin to reset course colours to allowed settings from Site Admin > Plugins page.
 * @package format_tiles
 * @param moodle_url $settingsurl
 * @param moodle_url $pageurl
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function reset_colours($settingsurl, $pageurl) {
    global $DB;
    require_sesskey();
    $permittedcolours = permitted_colours();
    if (count($permittedcolours) === 0) {
        redirect(
            $settingsurl,
            get_string('novaliddefaultcolour', 'format_tiles'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
    // Prepare a "NOT IN" statement for the permitted colours, to find records which have other colours.
    [$permittedcolourssql, $params] = $DB->get_in_or_equal($permittedcolours, SQL_PARAMS_NAMED, 'param', false);

    if (!optional_param('sure', 0, PARAM_INT)) {
        // User has not said they are sure yet so count how many courses are affected and offer user chance to confirm.
        $requiredchangecount = $DB->count_records_sql(
            "SELECT COUNT(courseid) FROM {course_format_options}
                WHERE format = 'tiles' AND name = 'basecolour'
                AND value " . $permittedcolourssql,
            $params
        );

        if ($requiredchangecount === 0) {
            redirect($settingsurl, get_string('allcoursescomplypalette', 'format_tiles'));
        } else {
            $pageurl->param('sure', '1');
            $pageurl->param('sesskey', sesskey());
            $o = html_writer::div(get_string('sureresetcolours', 'format_tiles', $requiredchangecount), 'mb-3 mt-3');
            $o .= html_writer::link($pageurl, get_string('resetcolours', 'format_tiles'), ['class' => 'btn btn-danger']);
            $o .= html_writer::link($settingsurl, get_string('cancel'), ['class' => 'btn btn-secondary']);
            return $o;
        }
    } else {
        // User has said they are sure so go ahead and reset.
        $defaultvalue = get_config('format_tiles', 'tilecolour1');

        // Validate our default value before we apply it to multiple courses.
        if (
            !$defaultvalue || strlen($defaultvalue) > 7 || strpos($defaultvalue, "#") !== 0
            || !ctype_xdigit(substr($defaultvalue, 1)) || hexdec($defaultvalue) === 0
        ) {
            redirect(
                $settingsurl,
                get_string('novaliddefaultcolour', 'format_tiles'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }
        // We don't want to trawl through and update each course record individually as it may take a while.
        // Better to just reset the 'illegal' colours in the DB in one query, given that we know what the permitted colours are.
        $sql = "UPDATE {course_format_options} SET value = :defaultvalue
            WHERE format = 'tiles' AND name = 'basecolour' AND sectionid = '0'
            AND value " . $permittedcolourssql;
        $params['defaultvalue'] = $defaultvalue;
        $DB->execute($sql, $params);

        redirect(
            $settingsurl,
            get_string('tilecolourschanged', 'format_tiles'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    return '';
}


/**
 * Get a HTML table of problem courses (too many / badly numbered sections) for display to admin.
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * @package format_tiles
 */
function list_problem_courses() {
    global $DB;

    $o = html_writer::tag(
        'h2',
        get_string('problemcourses', 'format_tiles')
        . ' (' . get_string('experimental', 'format_tiles') . ')'
    );

    // Moodle 4+ course format options unmigrated options check.
    $countlegacyoptions = $DB->get_field_sql(
        "SELECT COUNT(cfo.id)
                FROM {course_format_options} cfo
                WHERE cfo.format = 'tiles'
                AND cfo.name IN('tilephoto', 'tileicon')"
    );

    $o .= !$countlegacyoptions ? get_string('noproblemsfound', 'format_tiles') :
        html_writer::div(
            html_writer::link(
                new moodle_url('/course/format/tiles/editor/migratecoursedata.php'),
                get_string('migratecoursedata', 'format_tiles'),
                ['class' => 'btn btn-primary']
            ),
            'm-3'
        );
    return $o;
}
