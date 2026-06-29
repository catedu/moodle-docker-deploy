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
 * Page used to redirect user to a file or URL for a resource course module when direct access is needed.
 * Used to provide embedded src attribute in modal and backup link at bottom of modals for PDFs, HTML files and URL activities.
 * Enables URL to be simpler on modal and view to be logged before user is redirected.
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

$cmid = required_param('cmid', PARAM_INT);
$forcedownload = optional_param('forcedownload', false, PARAM_BOOL);

[$course, $cm] = get_course_and_cm_from_cmid($cmid);
// This will also check that cm is user visible.
require_course_login($course, true, $cm);

// Only doing this for mod_resource and URL.
$modnames = ['resource', 'url'];
if (!in_array($cm->modname, $modnames)) {
    throw new invalid_parameter_exception("Invalid module");
}

if ($course->format !== 'tiles' || !\format_tiles\local\modal_helper::cm_has_modal($course->id, $cm->id)) {
    throw new invalid_parameter_exception("Course module has no modal");
}

// Request and permission validation.
$context = context_module::instance($cm->id);

require_capability('mod/' . $cm->modname . ':view', $context);
$modobject = $DB->get_record($cm->modname, ['id' => $cm->instance], '*', MUST_EXIST);

if ($cm->modname === 'resource') {
    $fs = get_file_storage();
    // We show the file with the highest sort order, like on mod/resource/view.php.
    $files = $fs->get_area_files(
        $context->id,
        'mod_resource',
        'content',
        0,
        'sortorder DESC, id ASC',
        false,
        0,
        0,
        10
    );
    if (!empty($files)) {
        $allowedmimetypes = ['application/pdf', 'text/html'];
        require_once("$CFG->dirroot/mod/resource/lib.php");
        foreach ($files as $file) {
            if (in_array($file->get_mimetype(), $allowedmimetypes) && $file->get_filesize() > 0 && $file->get_filename() !== '.') {
                // This was changed once to address issue 212 (Safari <object> tags).
                // Later, the modal switched to using <iframe>, so this was changed back to address issue 291.
                require_once("$CFG->dirroot/lib/filelib.php");
                resource_view($modobject, $course, $cm, $context);
                $url = new moodle_url(
                    "/pluginfile.php/$context->id/mod_resource/content/$modobject->revision"
                    . $file->get_filepath() . rawurlencode($file->get_filename())
                );
                if ($forcedownload) {
                    $url->param('forcedownload', 1);
                }
                redirect($url);
            }
        }
    }
    throw new \moodle_exception('filenotfound');
} else if ($cm->modname === 'url') {
    url_view($modobject, $course, $cm, $context);
    $redirecturl = $modobject->externalurl;

    // If the URL is a YouTube video etc, it may need converting into an embed URL.
    $modifiedurl = !optional_param('noembed', false, PARAM_BOOL)
        ? \format_tiles\local\video_cm::check_modify_embedded_url($redirecturl)
        : null;

    redirect(new moodle_url($modifiedurl ?: $redirecturl));
}

// Should never reach here.
throw new \Exception("Invalid module");
