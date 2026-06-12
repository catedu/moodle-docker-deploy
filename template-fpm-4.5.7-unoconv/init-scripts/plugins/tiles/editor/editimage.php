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
 * Page called by teacher to upload a photo for a tile background.
 *
 * @package format_tiles
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');

global $PAGE, $DB, $OUTPUT;
use format_tiles\form\upload_image_form;

$sectionid = required_param('sectionid', PARAM_INT);
$deletephoto = optional_param('delete', 0, PARAM_INT);

$section = $DB->get_record('course_sections', ['id' => $sectionid], 'id, name, section, course');
if (!$section || $section->section == 0) {
    throw new invalid_parameter_exception("Invalid section");
}

$courseid = $section->course;
require_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);
require_capability('moodle/course:update', $coursecontext);
if ($course->format !== 'tiles') {
    throw new invalid_parameter_exception("Invalid course");
}
if (!get_config('format_tiles', 'allowphototiles')) {
    throw new moodle_exception('disabledbyadmin', 'format_tiles');
}

$pageargs = ['sectionid' => $sectionid];
$sectionname = get_section_name($courseid, $section->section);
$url = new moodle_url('/course/format/tiles/editor/editimage.php', $pageargs);
$PAGE->set_url($url);
$PAGE->set_context($coursecontext);
$PAGE->set_heading($sectionname);
$PAGE->navbar->add(
    $sectionname,
    new moodle_url('/course/view.php', ['id' => $course->id, 'section' => $section->section])
);

$PAGE->navbar->add(get_string('uploadnewphoto', 'format_tiles'));

$tilephoto = new \format_tiles\local\tile_photo($coursecontext, $sectionid);

if ($deletephoto) {
    require_sesskey();
    $tilephoto->clear();
    // Delete any other stored photo files for this tile.
    \format_tiles\local\tile_photo::delete_files_from_ids($courseid, $sectionid);
    \core\notification::success(get_string('imagedeletedfrom', 'format_tiles', $sectionname));
    redirect(new \moodle_url('/course/view.php', ['id' => $course->id]));
}

// The file will be scaled and compressed so should be able to accept higher than course settings say.
// However in practice this seems to be prevented by core.
// Another limiting factor is our php memory limit - we don't want to exhaust it processing large files.
// So we set 2M if we have less than 160M or memory, or 4M otherwise.
// Course settings may reduce this at course level.
// We do not use raise_memory_limit(MEMORY_EXTRA) though it is available.
$phpmemorylimit = get_real_size(ini_get('memory_limit'));
if ($phpmemorylimit < get_real_size("96M")) {
    $imagemaxbytes = get_real_size("1M");
} else if ($phpmemorylimit < get_real_size("160M")) {
    // Moodle default in 3.6 is 128M so image max is 2MB.
    $imagemaxbytes = get_real_size("2M");
} else {
    $imagemaxbytes = get_real_size("3M");
}

$options = [
    'subdirs' => 0,
    'maxfiles' => 1,
    'accepted_types' => $tilephoto->allowed_file_types(),
    'maxbytes' => $imagemaxbytes,
    'return_types' => FILE_INTERNAL,
];

$formparams = [
    'contextid' => $coursecontext->id,
    'sectionid' => $sectionid,
    'options' => $options,
];
if ($url = $tilephoto->get_image_url()) {
    $formparams['existingurl'] = $url;
    $aspectratiocheck = $tilephoto->verify_aspect_ratio();
    if ($aspectratiocheck['status'] !== true && isset($aspectratiocheck['messageshort'])) {
        $formparams['aspectratiomessage'] = html_writer::span(
            $aspectratiocheck['messageshort'],
            'alert alert-warning d-inline-block'
        );
    } else {
        $formparams['aspectratiomessage'] = html_writer::span(
            $aspectratiocheck['messageshort'] ?? '',
            'alert alert-success d-inline-block'
        );
    }
}

$mform = new upload_image_form(null, $formparams);

$formdata = new stdClass();
if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new \moodle_url('/course/view.php', ['id' => $course->id]));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.
    if ($newfilename = $mform->get_new_filename('tileimagefile')) {
        $fs = get_file_storage();
        $fileapiparams = $tilephoto::file_api_params();
        $tempfile = $mform->save_stored_file(
            'tileimagefile',
            $coursecontext->id,
            $fileapiparams['component'],
            $fileapiparams['tempfilearea'],
            $sectionid,
            $fileapiparams['filepath'],
            $newfilename,
            true
        );
        if (!\format_tiles\local\tile_photo::verify_file_type($tempfile)) {
            debugging('Invalid file type');
            $tempfile->delete();
        } else {
            try {

                // Delete any existing file attached to this section.
                \format_tiles\local\tile_photo::delete_files_from_ids($courseid, $sectionid);

                $newfile = $tilephoto->set_file_from_stored_file($tempfile, $newfilename);
                $verifyaspectratio = $tilephoto->verify_aspect_ratio();
                if ($verifyaspectratio['status'] !== true) {
                    \core\notification::warning(
                        $verifyaspectratio['message'] . ' '
                        . html_writer::link(
                            new moodle_url(
                                '/course/format/tiles/editor/editimage.php',
                                ['sectionid' => $section->id]
                            ),
                            get_string('back')
                        )
                    );
                } else {
                    \core\notification::success(
                        get_string('imagesavedfor', 'format_tiles', "'" . $sectionname . "'")
                    );
                }
                $tempfile->delete();
            } catch (Exception $e) {
                debugging('Cannot set file', DEBUG_DEVELOPER);
                debugging($e->getMessage(), DEBUG_DEVELOPER);
                if (isset($tempfile)) {
                    $tempfile->delete();
                    unset($tempfile);
                }
            }
        }
    }
    redirect(new \moodle_url('/course/view.php', ['id' => $course->id]));
}
$PAGE->requires->js_call_amd('format_tiles/edit_upload_image_helper', 'init');
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
