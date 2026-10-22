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

namespace format_tiles\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_value;
use core_external\external_description;
use core_external\external_single_structure;
use core_external\external_warnings;
use format_tiles\local\format_option;
use invalid_parameter_exception;
use dml_exception;
use file_exception;
use coding_exception;
use moodle_exception;
use required_capability_exception;
use restricted_context_exception;
use stored_file_creation_exception;
use context_module;
use context_course;
use format_tiles\local\tile_photo;

/**
 * Format tiles external functions
 *
 * @package    format_tiles
 * @category   external
 * @copyright  2023 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.0
 */
class external extends external_api {
    /**
     * Teacher is changing the icon/photo for a course section or whole course using AJAX
     * @param Integer $courseid the id of this course
     * @param Integer $sectionid the number of the section in this course - zero if whole course
     * @param String $filename the icon filename or photo filename for this tile.
     * @param string $imagetype whether it's a tile icon or a background photo.
     * @param int $sourcecontextid the context id of the source photo or icon.
     * @param int $sourceitemid the item id of the course photo or icon.
     * @return array status and image URL if applicable.
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @throws stored_file_creation_exception
     */
    public static function set_image(
        $courseid,
        $sectionid,
        $filename,
        $imagetype = 'tileicon',
        $sourcecontextid = 0,
        $sourceitemid = 0
    ) {
        global $DB, $OUTPUT;

        $data = self::validate_parameters(
            self::set_image_parameters(),
            [
                'courseid' => $courseid,
                'sectionid' => $sectionid,
                'image' => $filename,
                'sourcecontextid' => $sourcecontextid,
                'sourceitemid' => $sourceitemid,
                'imagetype' => $imagetype,
            ]
        );

        // Section id of zero means we are changing the course icon.  Otherwise check sec id is valid.
        if (
            $data['sectionid']
            && $DB->get_record(
                'course_sections',
                ['course' => $data['courseid'], 'id' => $data['sectionid']]
            ) === false
        ) {
            throw new invalid_parameter_exception('Invalid course and section id combination');
        }

        // Both section ID and cm ID can validly be zero.
        // This is allowed where we are setting course default icon from the course edit settings page.
        if ($data['sectionid'] == 0 && $data['courseid'] == 0) {
            throw new invalid_parameter_exception('At least course ID must be provided');
        }

        $context = context_course::instance($data['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:viewhiddenactivities', $context); // This allows non-editing teachers for the course.

        switch ($data['imagetype']) {
            case 'tileicon':
                if ($data['sectionid'] === 0) {
                    // A default icon for whole course - use core course format options.
                    $result = self::set_default_tileicon($data);
                    return  ['status' => (bool)$result];
                } else {
                    $result = self::set_tile_icon($data, $context);
                    return [
                        'status' => (bool)$result,
                        'iconurl' => $result ? $OUTPUT->image_url('tileicon/' . $data['image'], 'format_tiles')->out() : '',
                    ];
                }
            case 'tilephoto':
                if (!get_config('format_tiles', 'allowphototiles')) {
                    throw new invalid_parameter_exception("Photo tiles are disabled by site admin");
                }
                return self::set_tile_photo($data, $context);
            case 'draftfile':
                return self::set_tile_photo_from_draftfile($data);
            default:
                throw new invalid_parameter_exception('Image type is invalid ' . $data['imagetype']);
        }
    }

    /**
     * Given a draft file uploaded by user, save to this plugin's file area.
     * @param [] $data
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws stored_file_creation_exception
     */
    private static function set_tile_photo_from_draftfile($data) {
        $ctx = \context::instance_by_id($data['sourcecontextid']);
        if (!$data['sourcecontextid'] || !is_numeric($data['sourceitemid']) || !$ctx) {
            throw new invalid_parameter_exception(
                "Invalid source context id (" . $data['sourcecontextid']
                . ") or source item id (" . $data['sourceitemid'] . ") or context"
            );
        }
        $tilephoto = new tile_photo($ctx, $data['sectionid']);
        $fs = get_file_storage();
        $sourcefile = $fs->get_file(
            $data['sourcecontextid'],
            'user',
            'draft',
            $data['sourceitemid'],
            '/',
            $data['image']
        );
        if (!$sourcefile) {
            throw new invalid_parameter_exception(
                'Source file not found: ' . $data['sourcecontextid'] . '|' . $data['sourceitemid']
            );
        }
        $newfile = $tilephoto->set_file_from_stored_file($sourcefile, $data['image']);
        if ($newfile) {
            return ['status' => true, 'imageurl' => $tilephoto->get_image_url()];
        } else {
            return ['status' => false, 'imageurl' => ''];
        }
    }

    /**
     * Given the data describing the photo we want and the tile to apply it to, set the tile to use that photo.
     * @param [] $data
     * @param \context $context context id for which we are setting the photo.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws stored_file_creation_exception
     */
    private static function set_tile_photo($data, $context): array {
        $sectionid = $data['sectionid'];

        if (!$data['image']) {
            // If image is empty we are trying to delete the photo.
            $tilephoto = new tile_photo(
                $context,
                $context->contextlevel === CONTEXT_COURSE ? $sectionid : 0
            );
            $tilephoto->clear();
            // Delete all files associated with this section.
            \format_tiles\local\tile_photo::delete_files_from_ids($data['courseid'], $sectionid);

            // If there is an icon attached to this element, clear it (as in this function we are setting a photo).
            \format_tiles\local\format_option::unset(
                $data['courseid'],
                \format_tiles\local\format_option::OPTION_SECTION_ICON,
                $sectionid
            );

            return ['status' => true, 'imageurl' => ''];
        }
        $sourcecontext = \context::instance_by_id($data['sourcecontextid']);
        $issettingsampleimage =
            $sourcecontext->contextlevel == CONTEXT_SYSTEM && $data['sourceitemid'] == 0 & $data['image'] == 'sample_image.jpg';

        if (!$data['sourcecontextid'] || (!is_numeric($data['sourceitemid']) && !$issettingsampleimage)) {
            throw new invalid_parameter_exception(
                "Invalid source context id (" . $data['sourcecontextid']
                . ") or source item id (" . $data['sourceitemid'] . ")"
            );
        }

        if ($sourcecontext->contextlevel !== CONTEXT_COURSE && !$issettingsampleimage) {
            throw new invalid_parameter_exception("Invalid context level");
        }

        if ($data['sourcecontextid'] && !$issettingsampleimage) {
            // Arguably we don't need to do this as the only files the user will see are those they posted themselves.
            // This is thanks to the database query which generates the files list. So they could see them once.
            require_capability('moodle/course:viewhiddenactivities', $sourcecontext);
        }

        if ($issettingsampleimage) {
            $sourcefile = tile_photo::get_sample_image_file();
        } else {
            $sourcephoto = new tile_photo($sourcecontext, $data['sourceitemid']);
            $sourcephoto->set_filename($data['image']); // Just in case it's not found - set directly from source.
            $sourcefile = $sourcephoto->get_file();
        }
        if (!$sourcefile) {
            throw new invalid_parameter_exception(
                'Source file not found: sourcecontextid: ' . $data['sourcecontextid']
                . ' | sourceitemid' . $data['sourceitemid']
            );
        }
        $tilephoto = new tile_photo($context, $data['sectionid']);
        $file = $tilephoto->set_file_from_stored_file($sourcefile, $data['image']);
        if ($file) {
            // If there is an icon attached to this element, clear it (as here we are setting a photo).
            \format_tiles\local\format_option::unset(
                $data['courseid'],
                \format_tiles\local\format_option::OPTION_SECTION_ICON,
                $sectionid
            );
            return ['status' => true, 'imageurl' => $tilephoto->get_image_url()];
        } else {
            return ['status' => false, 'imageurl' => ''];
        }
    }

    /**
     * Given the data describing the icon we want and the tile to apply it to, set the tile to use that icon
     * @param array $data
     * @param \context $context context id for which we are setting the photo.
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function set_tile_icon($data, $context) {
        global $DB;

        // If the tile icon is a number icon, it's not a real image so we don't need to validate.
        $tilenumber = \format_tiles\local\util::get_tile_number_from_icon_name($data['image']);
        if (!$tilenumber) {
            $availableicons = (new \format_tiles\local\icon_set())->available_tile_icons($data['courseid']);
            if (!isset($availableicons[$data['image']])) {
                throw new invalid_parameter_exception('Icon is invalid');
            }
        }

        $optiontype = \format_tiles\local\format_option::OPTION_SECTION_ICON;
        $sectionid = $data['sectionid'];

        // We are dealing with a tile icon for one particular section, so check if user has picked the course default.
        $defaulticonthiscourse = $DB->get_field(
            'course_format_options',
            'value',
            ['courseid' => $data['courseid'], 'format' => 'tiles', 'sectionid' => 0, 'name' => 'defaulttileicon']
        );
        if ($data['image'] == $defaulticonthiscourse) {
            // Using default icon for a tile do don't store anything in database = default.
            // Unset any icon.
            format_option::unset($data['courseid'], format_option::OPTION_SECTION_ICON, $sectionid);
            // Also unset any photo.
            format_option::unset($data['courseid'], format_option::OPTION_SECTION_PHOTO, $sectionid);
            // Delete any related photo files.
            \format_tiles\local\tile_photo::delete_files_from_ids($data['courseid'], $sectionid);
            return true;
        } else {
            $result = format_option::set($data['courseid'], $optiontype, $sectionid, $data['image']);
        }

        if ($result) {
            // If there is a photo attached to this element, clear it (as here we are setting an icon).
            $tilephoto = new tile_photo($context, $data['sectionid']);
            $tilephoto->clear();
            // Delete any related photo files.
            \format_tiles\local\tile_photo::delete_files_from_ids($data['courseid'], $sectionid);
        }
        return $result;
    }

    /**
     * Set the default icon for this course (set from course edit page).
     * @param array $data
     * @return bool|int
     * @throws dml_exception
     */
    public static function set_default_tileicon($data) {
        global $DB;
        $optionname = 'defaulttileicon';
        $existingicon = $DB->get_record(
            'course_format_options',
            ['courseid' => $data['courseid'], 'format' => 'tiles', 'sectionid' => $data['sectionid'], 'name' => $optionname]
        );
        if (!isset($existingicon->value)) {
            // No icon is presently stored for this so we need to insert new record.
            $record = new \stdClass();
            $record->courseid = $data['courseid'];
            $record->format = 'tiles';
            $record->sectionid = $data['sectionid'];
            $record->name = $optionname;
            $record->value = $data['image'];
            $result = $DB->insert_record('course_format_options', $record);
        } else {
            // Updating existing course icon record.
            $existingicon->value = $data['image'];
            $result = $DB->update_record('course_format_options', $existingicon);
        }
        return $result;
    }

    /**
     * Returns description of get_instance_info() parameters.
     *
     * @return external_function_parameters
     */
    public static function set_image_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course id whose icon/image we are setting'),
                'sectionid' => new external_value(
                    PARAM_INT,
                    'Section id whose icon/image we are setting (zero means whole course not just one section)'
                ),
                'image' => new external_value(PARAM_RAW, 'File name for the image picked'),
                'imagetype' => new external_value(PARAM_RAW, 'Image type for image picked (tileicon, tilephoto, draftfile)'),
                'sourcecontextid' => new external_value(
                    PARAM_INT,
                    'File table context id for the photo file picked (0 if unused)',
                    VALUE_DEFAULT,
                    0
                ),
                'sourceitemid' => new external_value(
                    PARAM_INT,
                    'File table item id for the photo file picked (0 if unused)',
                    VALUE_DEFAULT,
                    0
                ),
            ]
        );
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function set_image_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether the image was set'),
            'imageurl' => new external_value(PARAM_RAW, 'Image URL if background photo set (not used for icons)', VALUE_OPTIONAL),
            'iconurl' => new external_value(PARAM_RAW, 'Icon URL if icon set (not used for photos)', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Log that fact that the user clicked a tile
     * @param int $coursecontextid
     * @param int $sectionnumber we are viewing
     * @param int $sectionid we are viewing
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function log_tile_click(int $coursecontextid, int $sectionnumber, int $sectionid) {
        global $SESSION;
        $params = self::validate_parameters(
            self::log_tile_click_parameters(),
            ['coursecontextid' => $coursecontextid, 'sectionnumber' => $sectionnumber, 'sectionid' => $sectionid]
        );
        // Request and permission validation.
        $coursecontext = \core\context::instance_by_id($params['coursecontextid']);
        self::validate_context($coursecontext);

        course_view($coursecontext, $sectionnumber);

        // This session var is used later, when user revisits main course page, or a single section, for a course using this format.
        // If set to true, the page can safely be rendered from PHP in the javascript friendly format.
        // (A <noscript> box will be displayed only to users who have JS disabled with a link to switch to non JS format).
        $SESSION->format_tiles_jssuccessfullyused = 1;

        return ['status' => true, 'warnings' => []];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function log_tile_click_parameters() {
        return new external_function_parameters(
            [
                'coursecontextid' => new external_value(PARAM_INT, 'Course context id'),
                'sectionnumber' => new external_value(PARAM_INT, 'Section number viewed'),
                'sectionid' => new external_value(PARAM_INT, 'Section id viewed'),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function log_tile_click_returns() {
        return new external_single_structure(
            ['status' => new external_value(PARAM_BOOL, 'status: true if success')]
        );
    }

    /**
     * Get the available icon set
     * @param int $courseid
     * @return array of warnings and status result
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function get_icon_set($courseid) {
        $params = self::validate_parameters(
            self::get_icon_set_parameters(),
            ['courseid' => $courseid]
        );
        // Request and permission validation.
        // Note course id could be zero if creating new course.

        if ($params['courseid'] != 0) {
            $context = context_course::instance($params['courseid']);
        } else {
            $context = \context_coursecat::instance(optional_param('category', 0, PARAM_INT));
        }
        self::validate_context($context);
        if (!has_capability('moodle/course:update', $context) && !has_capability('moodle/course:create', $context)) {
            if (!has_capability('moodle/course:update', $context)) {
                throw new required_capability_exception(
                    $context,
                    'moodle/course:update',
                    "nopermissions",
                    ""
                );
            } else {
                throw new required_capability_exception(
                    $context,
                    'moodle/course:create',
                    "nopermissions",
                    ""
                );
            }
        }
        $data = [
            'status' => true,
            'warnings' => [],
            'icons' => json_encode((new \format_tiles\local\icon_set())->available_tile_icons($params['courseid'])),
            'photos' => '',
        ];
        if (get_config('format_tiles', 'allowphototiles')) {
            $data['photos'] = json_encode(tile_photo::get_photo_library_photos($params['courseid']));
        }
        return $data;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_icon_set_parameters() {
        return new external_function_parameters(
            ['courseid' => new external_value(PARAM_INT, 'Course id')]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.3
     */
    public static function get_icon_set_returns() {
        return new external_single_structure(
            [
                'icons' => new external_value(PARAM_RAW, 'Icon set available for use on tile icons (JSON array)'),
                'photos' => new external_value(PARAM_RAW, 'Recent photos set for teacher photo library (JSON array)'),
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Set the result of the JS calculation of the optimal width of the main tiles window for a course.
     * This has to be by course as they have different numbers of tiles.
     * We can then use this to render the page from PHP at the correct width initially next time.
     * @param int $courseid the course id we are in
     * @param int $width the JS calculated width
     * @see \format_tiles\local\util::width_template_data() for where this is used.
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function set_session_width($courseid, $width) {
        global $SESSION;
        $params = self::validate_parameters(
            self::set_session_width_parameters(),
            ['courseid' => $courseid, 'width' => $width]
        );
        // Request and permission validation - validate_context() includes require_login() check.
        $coursecontext = context_course::instance($params['courseid']);
        self::validate_context($coursecontext);
        $sessionvar = 'format_tiles_width_' . $params['courseid'];

        if (!get_config('format_tiles', 'fittilestowidth')) {
            throw new invalid_parameter_exception("Setting tiles width is disabled by site admin");
        }

        if ($params['width'] < 300 || $params['width'] > 3000) {
            // Value passed is out of bounds, so unset as something has gone wrong.
            unset($SESSION->$sessionvar);
            return ['status' => false, 'warnings' => ['Session width out bounds']];
        }

        $SESSION->$sessionvar = $params['width'];
        return ['status' => true, 'warnings' => []];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function set_session_width_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course id'),
                'width' => new external_value(
                    PARAM_INT,
                    'The JS calculated width optimal width for tiles window (used to render from PHP next time)',
                    VALUE_DEFAULT,
                    0,
                    true
                ),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function set_session_width_returns() {
        return new external_single_structure(
            ['status' => new external_value(PARAM_BOOL, 'status: true if success')]
        );
    }

    /**
     * Return some information about a section or a set of sections in a course.
     * This may be called as a user progresses through course activities (with course completion).
     * The data provided enable the tiles to be updated client side with progress info and availability.
     * @param int $courseid
     * @param array $sectionnums
     * @return array of warnings and status result
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     * @since Moodle 3.8
     */
    public static function get_section_information($courseid, $sectionnums) {
        global $PAGE;
        $params = self::validate_parameters(
            self::get_section_information_parameters(),
            ['courseid' => $courseid, 'sectionnums' => $sectionnums]
        );

        // Request and permission validation.
        // Ensure user has access to course context.
        // validate_context() below ends up calling require_login($courseid).
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        $isrtl = right_to_left();

        $sections = [];
        $warnings = [];

        $course = get_course($params['courseid']);
        $modinfo = get_fast_modinfo($course);
        $sectioninfo = $modinfo->get_section_info_all();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        $renderer = $PAGE->get_renderer('format_tiles');
        $format = course_get_format($course);
        $templateable = new \format_tiles\output\course_output($course, true);
        $showprogressaspercent = $templateable->courseformatoptions['courseshowtileprogress'] == 2;
        $overall = ['complete' => 0, 'outof' => 0];
        // First add the info about the section and its availability.
        foreach ($sectionnums as $sectionnum) {
            if (isset($sectioninfo[$sectionnum]) && ($sectioninfo[$sectionnum]->visible || $canviewhidden)) {
                $section = $sectioninfo[$sectionnum];
                $availabilitywidgetclass = $format->get_output_classname('content\\section\\availability');
                $availabilitywidget = new $availabilitywidgetclass($format, $section);
                $sections[$sectionnum] = [
                    'sectionid' => $section->id,
                    'sectionnum' => $sectionnum,
                    'isavailable' => $section->available,
                    'isclickable' => $section->available || $section->uservisible,
                    'availabilitymessage' => $renderer->render($availabilitywidget),
                    'numcomplete' => -1, // If we have data, we replace this below.
                    'numoutof' => -1, // If we have data, we replace this below.
                    'isrtl' => $isrtl,
                ];
            } else {
                $warnings[] = [
                    'item' => $sectionnum,
                    'warningcode' => 'errorrequestnotfound',
                    'message' => 'No section information available to user for section number ' . $sectionnum,
                ];
            }
        }

        // Next, if completion is enabled, add info about this user's progress.
        $completionenabled = $course->enablecompletion && !isguestuser();
        if ($completionenabled) {
            foreach ($sections as $section) {
                if (isset($modinfo->sections[$section['sectionnum']])) {
                    $completionthistile = $templateable->section_progress($section['sectionnum']);
                } else {
                    $completionthistile = ['completed' => 0, 'outof' => 0];
                }
                $completiondata = $templateable->completion_indicator(
                    $completionthistile['completed'],
                    $completionthistile['outof'],
                    $showprogressaspercent,
                    false
                );
                foreach ($completiondata as $k => $v) {
                    // Add percent, percentcircumf, percentoffset, issingledigit.
                    $sections[$section['sectionnum']][strtolower($k)] = $v;
                }
                $overall['complete'] += $completionthistile['completed'];
                $overall['outof'] += $completionthistile['outof'];
            }
        }
        return [
            'sections' => array_values($sections),
            'overall' => $overall,
            'status' => true,
            'warnings' => $warnings,
        ];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.8
     */
    public static function get_section_information_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course id'),
                'sectionnums' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Section number to get info for', VALUE_REQUIRED, null, NULL_ALLOWED),
                    'Section numbers in this course to get info for',
                    VALUE_REQUIRED,
                    []
                ),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.8
     */
    public static function get_section_information_returns() {
        return new external_single_structure(
            [
                'sections' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'sectionid' => new external_value(PARAM_INT, 'Section id'),
                            'sectionnum' => new external_value(PARAM_INT, 'Section number in course'),
                            'numcomplete' => new external_value(
                                PARAM_INT,
                                'Number of activities completed in this section by this user'
                            ),
                            'numoutof' => new external_value(
                                PARAM_INT,
                                'Number of possible activities in this section for this user'
                            ),
                            'percent' => new external_value(PARAM_INT, 'Percent complete', VALUE_OPTIONAL, 0),
                            'percentcircumf' => new external_value(
                                PARAM_FLOAT,
                                'Circumference of radial indicator',
                                VALUE_OPTIONAL,
                                0
                            ),
                            'percentoffset' => new external_value(
                                PARAM_INT,
                                'Percent offset for radial indicator' . VALUE_OPTIONAL,
                                0
                            ),
                            'iscomplete' => new external_value(PARAM_BOOL, 'Is the section complete', VALUE_OPTIONAL, false),
                            'isavailable' => new external_value(PARAM_BOOL, 'Is the section available (not restricted)'),
                            'isclickable' => new external_value(PARAM_BOOL, 'Is the section clickable / expandable'),
                            'availabilitymessage' => new external_value(PARAM_RAW, 'If the section is restricted, explains why'),
                        ]
                    )
                ),
                'overall' => new external_single_structure(
                    [
                        'complete' => new external_value(PARAM_INT, 'How many activities complete overall'),
                        'outof' => new external_value(PARAM_INT, 'How many activities out of overall'),
                    ]
                ),
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Parameters for external function.
     * @return external_function_parameters
     */
    public static function get_course_mod_info_parameters() {
        return new external_function_parameters(
            ['cmid' => new external_value(PARAM_INT, 'The course module id')]
        );
    }

    /**
     * Get information used by tiles format about a course module.
     * @param int $cmid
     * @return object
     * @throws \core_external\restricted_context_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_course_mod_info(int $cmid) {
        $params = self::validate_parameters(self::get_course_mod_info_parameters(), ['cmid' => $cmid]);
        $context = context_module::instance($params['cmid']);
        self::validate_context($context);

        // The method below includes a capability check for the module view.
        $data = \format_tiles\local\util::get_course_mod_info($context->get_course_context()->instanceid, $params['cmid']);
        if ($data) {
            return $data;
        }

        throw new invalid_parameter_exception('Invalid or inaccessible course module ID');
    }

    /**
     *
     * Returns description of method result value.
     * @return external_description
     */
    public static function get_course_mod_info_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The course module ID'),
            'courseid' => new external_value(PARAM_INT, 'The course ID where this cm exists'),
            'modulecontextid' => new external_value(PARAM_INT, 'The course module context ID'),
            'coursecontextid' => new external_value(PARAM_INT, 'The course context ID where this cm exists'),
            'name' => new external_value(PARAM_TEXT, 'The course module display name'),
            'modname' => new external_value(PARAM_TEXT, 'The course module name'),
            'sectionnumber' => new external_value(PARAM_INT, 'Course section number'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'completionenabled' => new external_value(PARAM_BOOL, 'Whether completion is enabled for this cm'),
            'completionstate' => new external_value(PARAM_INT, 'Completion state for current user'),
            'iscomplete' => new external_value(PARAM_BOOL, 'Whether complete for current user'),
            'ismanualcompletion' => new external_value(PARAM_BOOL, 'Whether is manual completion'),
            'resourcetype' => new external_value(PARAM_TEXT, 'The resource type e.g. PDF if modname is resource'),
            'modalallowed' => new external_value(PARAM_BOOL, 'Whether the UI can launch this in a modal'),
            'modaltype' => new external_value(PARAM_TEXT, 'Type of modal the UI can launch for this e.g. pdf'),
            'description' => new external_value(PARAM_RAW, 'The formatted description/intro field for the course module'),
        ]);
    }
}
