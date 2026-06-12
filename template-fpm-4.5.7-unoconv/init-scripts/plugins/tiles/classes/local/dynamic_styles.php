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
 * Prepares CSS for Tiles dynamic styles (e.g. course specific colours).
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles\local;

/**
 * Prepares CSS for Tiles dynamic styles (e.g. course specific colours).
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dynamic_styles {

    /**
     * Default hex tile colour if none other found.
     */
    const DEFAULT_COLOUR = '#1670CC';

    /**
     * Get the tiles dynamic course CSS to be added to <head>.
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_tiles_dynamic_css(): string {
        global $CFG, $PAGE, $DB;
        require_once("$CFG->dirroot/course/format/lib.php");
        // Only need dynamic CSS if we are on /course/view.php or /course/section.php.
        // (Will only be using /course/section.php in Moodle 4.4+ and if not using JS nav).
        $allowedpagetypes = ['course-view-tiles', 'section-view-tiles', 'course-view-section-tiles'];
        if (!in_array($PAGE->pagetype, $allowedpagetypes)) {
            return '';
        }
        $iscourseviewpage = $PAGE->url->compare(new \moodle_url('/course/view.php'), URL_MATCH_BASE);
        $issectionpage = $PAGE->url->compare(new \moodle_url('/course/section.php'), URL_MATCH_BASE);
        if (!$iscourseviewpage && !$issectionpage) {
            return '';
        }
        $idparam = optional_param('id', 0, PARAM_INT);
        if ($idparam) {
            $courseid = $iscourseviewpage ? $idparam : $DB->get_field('course_sections', 'course', ['id' => $idparam]);
            if ($courseid) {
                $data = self::data_for_template($courseid);
                $m = new \Mustache_Engine;
                return $m->render(
                    file_get_contents("$CFG->dirroot/course/format/tiles/templates/dynamic_styles.mustache"),
                    $data
                );
            }
        }
        return '';
    }

    /**
     * Export the data for the mustache template.
     * @see \format_tiles\local\util::width_template_data()
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function data_for_template(int $courseid): array {
        $format = course_get_format($courseid);
        $course = $courseid ? $format->get_course() : null;
        $basecolourhex = !$course ? self::DEFAULT_COLOUR : self::get_tile_base_colour($course->basecolour ?? '');
        $tilestyle = get_config('format_tiles', 'tilestyle') ?? \format_tiles\output\course_output::TILE_STYLE_STANDARD;
        $usinghighcontrast = \format_tiles\local\util::using_high_contrast();

        $outputdata = [
            'courseid' => $courseid,
            "isstyle-$tilestyle" => true,
            'isstyle1or2' => $tilestyle == 1 || $tilestyle == 2,
            'base_colour_rgb' => $usinghighcontrast ? "0,0,0" : ($basecolourhex ? self::rgbcolour($basecolourhex) : null),
            'high_contrast_black' => '#000',
            'usesubtiles' => $course->courseusesubtiles ?? false,
            // Shade heading bar will be 1 (used) or 0 (not used) now.
            // (Legacy values could be 'standard' for not used, or a colour for used, but in that case treat as 'used').
            'shade_heading_bar' => $course->courseusebarforheadings != 0 && $course->courseusebarforheadings != 'standard'
                ? 1 : 0,
            // Site admin may have added additional CSS via the plugin settings.
            'pluginconfigcss' => trim(get_config('format_tiles', 'customcss') ?? ''),
        ];

        if (get_config('format_tiles', 'allowphototiles')) {
            $outputdata['allowphototiles'] = 1;
            $outputdata['photo_tile_text_bg_opacity'] = $usinghighcontrast ? 1 :
                1.0 - (float)get_config('format_tiles', 'phototiletitletransarency');

            // The best values here vary by theme and browser, so mostly come from admin setting.
            // If the site admin sets background opacity to solid then it doesn't matter if the lines overlap.
            $outputdata['phototilefontsize'] = 20;
            $outputdata['phototiletextpadding'] = number_format(
                (float)get_config('format_tiles', 'phototitletitlepadding') / 10, 1
            );
            $outputdata['phototiletextlineheight'] = number_format(
                (float)get_config('format_tiles', 'phototitletitlelineheight') / 10, 1
            );
        }

        // Tile fitter if used.
        $outputdata['usingtilefitter'] = \format_tiles\local\util::using_tile_fitter();
        $outputdata['tilefittermaxwidth'] = $outputdata['usingtilefitter']
            ? \format_tiles\local\util::get_tile_fitter_max_width($courseid) : 0;

        return $outputdata;
    }

    /**
     * Convert hex colour from plugin settings admin page to RGB
     * so that can add transparency to it when used as background
     * @param string $hex the colour in hex form e.g. #979797
     * @return string rgb colour
     */
    public static function rgbcolour(string $hex): string {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return "$r,$g,$b";
    }

    /**
     * Get the colour which should be used as the base course for this course
     * (Can depend on theme, plugin and/or course settings).
     * @param string $coursebasecolour the course base colour which we may use unless this overrides it.
     * @return string the hex colour
     * @throws \dml_exception
     */
    public static function get_tile_base_colour($coursebasecolour): string {
        global $PAGE;

        $hexpattern = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';

        if (!(get_config('format_tiles', 'followthemecolour'))) {
            if (!$coursebasecolour) {
                // If no course tile colour is set, use plugin default colour.
                $result = get_config('format_tiles', 'tilecolour1');
            } else {
                $result = $coursebasecolour;
            }
        } else {
            // We are following theme's main colour so find out what it is.
            // Many themes including boost theme and Moove use "brandcolor" so try to get that if current theme has it.
            $result = get_config('theme_' . $PAGE->theme->name, 'brandcolor');
            if (!$result) {
                // If not got a colour yet, look where essential theme stores its brand color and try that.
                $result = get_config('theme_' . $PAGE->theme->name, 'themecolor');
            }
        }

        if (!$result || !preg_match($hexpattern, $result)) {
            // If still no colour set, use a default colour.
            $result = get_config('format_tiles', 'tilecolour1') ?? self::DEFAULT_COLOUR;
        }
        return $result;
    }

    /**
     * Does the course main page need to show the loading icon while correct width is calculated?
     * @param int $courseid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function page_needs_loading_icon(int $courseid): bool {
        if (!\format_tiles\local\util::using_tile_fitter()) {
            return false;
        }
        return !\format_tiles\local\util::get_tile_fitter_max_width($courseid);
    }
}
