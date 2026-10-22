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
 * Tile photo class for format tiles.
 * @package    format_tiles
 * @copyright  2019 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\local;

/**
 * Tile photo class for format tiles.
 * @package    format_tiles
 * @copyright  2019 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tile_photo {
    /**
     * Which type of tile option we have, e.g. format_option::OPTION_SECTION_PHOTO.
     * @var int
     */
    private $tilesoptiontype;

    /**
     * Course id for this course.
     * @var int
     */
    private $courseid;

    /**
     * Section id we are concerned with.
     * @var int
     */
    private $sectionid;

    /**
     * Context we are concerned with (will be course or module context).
     * @var \context
     */
    private $context;

    /**
     * The filename relating to this tile_photo object.
     * Empty string when uninitialised, null if initialised but genuinely null.
     * @var string
     */
    private $filename = '';

    /**
     * The file object to which this tile_photo object relates.
     * @var \stored_file
     */
    private $file;

    /**
     * Creates a new instance of class
     *
     * @param \context $context context for this photo.
     * @param int $sectionid section ID for this photo.
     */
    public function __construct(\context $context, int $sectionid) {
        $this->context = $context;
        if ($this->context->contextlevel === CONTEXT_COURSE) {
            $this->courseid = $this->context->instanceid;
            $this->sectionid = $sectionid;
            $this->tilesoptiontype = format_option::OPTION_SECTION_PHOTO;
        } else {
            debugging(
                'Invalid photo context level: ' . $this->context->contextlevel . ' section ID ' . $sectionid,
                DEBUG_DEVELOPER
            );
        }
    }

    /**
     * Get the filename for this tile photo.
     * @return string|null
     */
    public function get_filename() {
        if ($this->filename === '') {
            // Empty string when uninitialised, null if initialised but genuinely null.
            $this->filename = $this->courseid
                ? format_option::get($this->courseid, $this->tilesoptiontype, $this->get_element_id()) : null;
        }
        // We do not throw an error here if no filename is found as this just means that the item does not have a photo.
        return $this->filename;
    }

    /**
     * Set the filename for this tile photo.
     * @param string $filename
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function set_filename($filename) {
        $this->filename = $filename;
        format_option::set($this->courseid, $this->tilesoptiontype, $this->get_element_id(), $this->filename);
    }

    /**
     * Get the image url associated with this tile_photo object.
     * @return null|string
     */
    public function get_image_url(): ?string {
        if (!$this->get_filename()) {
            return null;
        } else {
            $config = self::file_api_params();
            return \moodle_url::make_pluginfile_url(
                $this->context->id,
                $config['component'],
                $config['filearea'],
                $this->get_element_id(),
                $config['filepath'],
                $this->get_filename(),
                false
            )->out();
        }
    }

    /**
     * Given a course context id, section id and a filename, get the related photo file.
     * @param int $contextid the context id.
     * @param int $elementid the element id.
     * @param string $filename the file name.
     * @return null|\stored_file
     */
    public static function get_file_from_ids($contextid, $elementid, $filename): ?\stored_file {
        $fs = get_file_storage();
        $config = self::file_api_params();
        $file = $fs->get_file(
            $contextid,
            $config['component'],
            $config['filearea'],
            $elementid,
            $config['filepath'],
            $filename
        );
        if ($file && $file->get_filename() != '.' && $file->get_filesize() > 0) {
            return $file;
        }
        return null;
    }

    /**
     * Get the image file associated with this tile_photo object.
     * @return \stored_file|null
     */
    public function get_file() {
        if (!isset($this->file)) {
            $this->file = self::get_file_from_ids($this->context->id, $this->get_element_id(), $this->get_filename());
        }
        return $this->file;
    }

    /**
     * When course_section_deleted / course_module_deleted is trigger we remove related files.
     * @param int $courseid the course id.
     * @param int $sectionid the section id (pass in -1 if not limiting to a section)
     * @param int $cmid
     * @return bool
     */
    public static function delete_files_from_ids(int $courseid, int $sectionid, $cmid = 0) {
        $params = self::file_api_params();
        $fs = get_file_storage();
        if (!$cmid) {
            if (!$sectionid || $sectionid < -1) {
                debugging("Must pass in a positive section ID or -1 if not limiting to a section", DEBUG_DEVELOPER);
                throw new \Exception("Invalid section");
            }
            $context = \context_course::instance($courseid, IGNORE_MISSING);
            if ($context) {
                return $fs->delete_area_files(
                    $context->id,
                    $params['component'],
                    $params['filearea'],
                    $sectionid === -1 ? false : $sectionid
                );
            }
        } else {
            $context = \context_module::instance($cmid, IGNORE_MISSING);
            if ($context) {
                return $fs->delete_area_files(
                    $context->id,
                    $params['component'],
                    $params['filearea']
                );
            }
        }
        return false;
    }

    /**
     * Used if we already have a stored file that we want to set as the file for this object.
     * E.g. we are converting from Grid format and the file is already saved.
     * @param \stored_file $file
     */
    public function set_file($file) {
        global $DB;
        // Ensure that this section/cm really exists.
        if ($this->tilesoptiontype == format_option::OPTION_SECTION_PHOTO) {
            $DB->get_record(
                'course_sections',
                ['course' => $this->courseid, 'id' => $this->sectionid],
                "id",
                MUST_EXIST
            );
        }

        $this->file = $file;
        $this->filename = $file->get_filename();
        format_option::set($this->courseid, $this->tilesoptiontype, $this->get_element_id(), $this->filename);
    }

    /**
     * Get the element id for this object as used in the format_tiles_tile_options table.
     * Element id is either section id (for section) or cmid (for course mod).
     * @return int
     */
    private function get_element_id() {
        return $this->sectionid;
    }

    /**
     * Handle an existing stored file (e.g. a user draft file or a file used in another course).
     * Scale the image to suit this plugin and then save it and update this object.
     * @param \stored_file $sourcefile
     * @param string $newfilename
     * @return bool|\stored_file
     * @throws \file_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws \stored_file_creation_exception
     */
    public function set_file_from_stored_file($sourcefile, $newfilename) {
        if ($sourcefile) {
            $sourceimageinfo = $sourcefile->get_imageinfo();
            $newwidth = self::get_max_image_width();

            $newfilename = self::get_unique_filename($newfilename);

            $newfile = image_processor::adjust_and_copy_file(
                $sourcefile,
                $newfilename,
                $this->context,
                $this->get_element_id(),
                $newwidth,
                floor($sourceimageinfo['height'] * $newwidth / $sourceimageinfo['width'])
            );
            if ($newfile) {
                $this->set_file($newfile);
                return $newfile;
            } else {
                debugging('Failed to set file from details - filename ' . $newfilename, DEBUG_DEVELOPER);

                // Restore the original file name of the original file.
                debugging("New file could not be created", DEBUG_DEVELOPER);
                $this->get_file()->rename(self::file_api_params()['filepath'], $this->filename);
                return false;
            }
        } else {
            debugging('Failed to set file from details - filename ' . $newfilename, DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Check if the aspect ratio is a normal landscape one or not.
     * @return array message as to whether it is or not.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function verify_aspect_ratio() {
        $file = $this->get_file();
        if (!$file) {
            debugging("No stored file found", DEBUG_DEVELOPER);
            $this->clear();
            return ['status' => false];
        }

        $requiredratio = 0.666; // Landscape is 2:3 ratio height:width.
        // We allow 5% error without warning.
        // Beyond 5% we accept incorrect aspect ratios but warn the user.
        $tolerance = 0.05;

        $imageinfo = $file->get_imageinfo();
        if (!$imageinfo) {
            debugging("No stored file found", DEBUG_DEVELOPER);
            $this->clear();
            return ['status' => false];
        }
        $ratio = $imageinfo['height'] / $imageinfo['width'];
        $messageshort = get_string('imagesize', 'format_tiles') . ": ";
        if (abs($ratio - $requiredratio) > $tolerance) {
            if ($ratio > $requiredratio) {
                $tallorwide = ['tallorwide' => get_string('tootall', 'format_tiles')];
                $messageshort .= get_string('tootall', 'format_tiles');
            } else {
                $tallorwide = ['tallorwide' => get_string('toowide', 'format_tiles')];
                $messageshort .= get_string('toowide', 'format_tiles');
            }
            return [
                'status' => false,
                'message' => get_string(
                    'aspectratiotootallorwide',
                    'format_tiles',
                    $tallorwide
                ),
                'messageshort' => $messageshort,
            ];
        }
        $messageshort .= get_string('ok', 'format_tiles');
        return ['status' => true, 'message' => $messageshort, 'messageshort' => $messageshort];
    }

    /**
     * Clear the data associated with this tile_photo object.
     */
    public function clear() {
        $this->delete_stored_file();
        format_option::unset($this->courseid, $this->tilesoptiontype, $this->get_element_id());
    }

    /**
     * Delete all tiles photos and icon choices (can be called by site admin from course nav).
     * @param int $courseid the id for this course.
     * @return bool whether successful.
     */
    public static function reset_tiles_course($courseid) {
        require_capability('moodle/site:config', \context_system::instance());
        $fs = get_file_storage();
        $fileapiparams = self::file_api_params();

        $result = format_option::unset_all_course($courseid);

        // Delete section tile files (photos).
        return $result && $fs->delete_area_files(
            \context_course::instance($courseid)->id,
            $fileapiparams['component'],
            $fileapiparams['filearea']
        );
    }

    /**
     * Delete the file stored for this object from file storage, and from this object.
     * @return bool
     */
    private function delete_stored_file() {
        // If we don't have filename then we have nothing to delete.
        if ($this->get_filename()) {
            $file = $this->get_file();
            if ($file) {
                return $file->delete();
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * Types of files that we allow to be uploaded as tile backgrounds.
     * @return array
     */
    public static function allowed_file_types() {
        return ['image/gif', 'image/jpeg', 'image/png'];
    }

    /**
     * Verify a particular file against allowed types.
     * @param \stored_file $file the file to check
     * @return bool whether file type is allowed.
     */
    public static function verify_file_type($file) {
        $mime = $file->get_mimetype();
        if (!in_array($mime, self::allowed_file_types())) {
            debugging("File type not allowed " . $mime);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the most recent x number of photos ($maxnumberphotos) that I uploaded.
     * Or that exist in this course (even if someone else uploaded).
     * Ignore any more than a certain time old.  Used to populate my photo library.
     * @param int $courseid the id for this course.
     * @param int $maxnumberphotos how many to return maximum.
     * @return array details of photos incl filename and details for path to make URL.
     * @throws \dml_exception
     */
    public static function get_photo_library_photos(int $courseid, int $maxnumberphotos = 20): array {
        // Did not use (new \file_storage())->get_area_files() for this as it requires context id.
        // We want to filter by user id instead.
        global $DB, $USER;
        if (!is_numeric($courseid)) {
            debugging('Course id must be numeric', DEBUG_DEVELOPER);
            return [];
        }

        $contextids = [\context_course::instance($courseid)->id];
        [$contextsql, $params] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);

        $params['userid'] = $USER->id;
        $params['cutofftime'] = strtotime("-36 months");
        $params['filesizecutoff'] = get_real_size("700K"); // Don't want to try to display really large draft files in library.
        $fileapiparams = self::file_api_params();
        $params['component'] = $fileapiparams['component'];
        $params['filearea'] = $fileapiparams['filearea'];
        $params['filepath'] = $fileapiparams['filepath'];

        $sql = "SELECT id, contenthash, component, filearea, contextid, itemid, filepath, filename, filesize, mimetype
            FROM {files}
            WHERE component = :component AND filearea = :filearea AND (userid = :userid OR contextid $contextsql)
            AND filename != '.' AND filepath = :filepath
            AND timemodified > :cutofftime
            AND filesize < :filesizecutoff AND filesize > 0
            ORDER BY timemodified DESC";

        try {
            // We set a max number of records for the query that we don't expect to reach, just in case.
            // We can't use $maxnumberphotos, as query returns duplicate records if same photo used multiple times.
            $maxnumerofrecords = 1000;
            $records = $DB->get_records_sql($sql, $params, 0, $maxnumerofrecords);
        } catch (\Exception $ex) {
            debugging('Failed to run query to get files for library. ' . $ex->getMessage(), DEBUG_DEVELOPER);
            $records = [];
        }

        // If the teacher has nothing in their library, add a sample image.
        if (empty($records)) {
            $params['contextid'] = \context_system::instance()->id;
            $sql = "SELECT id, contenthash, component, filearea, contextid, itemid, filepath, filename, filesize, mimetype
            FROM {files}
            WHERE component = :component AND filearea = :filearea AND contextid = :contextid
            AND filename = 'sample_image.jpg'
            AND filepath = :filepath
            AND filesize > 0";
            $records = $DB->get_records_sql($sql, $params, 0, 1);
        }

        // Reduce to a set (ignore items with same contenthash or same filename and roughly same size).
        $set = [];
        $contenthashes = [];
        $filesizetolerance = 2000; // If file size is within 2kb of another file, we treat that as same size.

        $countadded = 0;
        foreach ($records as $record) {
            $setkey = $record->filename . '|' . $record->mimetype;
            if (
                !in_array($record->contenthash, $contenthashes) &&
                (!isset($set[$setkey]) || abs($set[$setkey]->filesize - $record->filesize) > $filesizetolerance)
            ) {
                // Seems like we don't already have this file in the set - don't have to be precise here given purpose.
                unset($record->mimetype);  // Don't need to keep this.
                $set[$setkey] = $record;
                $contenthashes[] = $record->contenthash;
                $countadded++;
                if ($countadded > $maxnumberphotos) {
                    return array_values($set);
                }
            }
        }
        return array_values($set);
    }

    /**
     * When we store a new tile photo as a file, the config should we use for the Moodle File API.
     * @return array the config data.
     */
    public static function file_api_params() {
        return [
            'component' => 'format_tiles',
            'filearea' => 'tilephoto',
            'filepath' => '/tilephoto/',
            'tempfilearea' => 'temptilephoto',
        ];
    }

    /**
     * The maximum width of photos that we want to save (somewhat larger than tile size).
     * @return int
     */
    public static function get_max_image_width() {
        return 360;
    }

    /**
     * The sample image file in the database for this Moodle instance.
     * There is only one and it is shown to teacher as a sample if their library is empty.
     * @return null|\stored_file
     * @throws \dml_exception
     */
    public static function get_sample_image_file(): ?\stored_file {
        return self::get_file_from_ids(\context_system::instance()->id, 0, 'sample_image.jpg');
    }

    /**
     * Ensure file has a new name (to bust browser cache if name is same but content different).
     * @param string $filename
     * @return string new file name.
     */
    public static function get_unique_filename(string $filename): string {
        $pathinfo = pathinfo($filename);
        $basename = $pathinfo['filename'];
        if ($basename) {
            $newfilename = $basename . '_' . strtolower(random_string(3));
        }
        if (isset($pathinfo['extension'])) {
            $newfilename .= '.' . $pathinfo['extension'];
        }
        return $newfilename ?? '';
    }
}
