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
 * Tiles course format filters output class (for filter bar at top of course if used).
 *
 * @package format_tiles
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles\local;

/**
 * Tiles course format filters output class (for filter bar at top of course if used).
 *
 * @package format_tiles
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters {


    /**
     * Get the details of the filter buttons to be displayed at the top of this course
     * where the teacher has selected to use OUTCOME filter buttons e.g. button 1 might
     * filter to outcome 1, button 2 to outcome 2 etc
     * @param array $tiles the tiles output object showing the outcome ID for each tile
     * @param array $outcomenames the course outcome names to display
     * @param int $firstbuttonid first button id so it follows on from last one
     * @see get_filter_numbered_buttons()
     * @return array the button details
     */
    public static function get_filter_outcome_buttons_data(array $tiles, $outcomenames, $firstbuttonid = 1) {
        $outcomebuttons = [];
        if ($outcomenames) {
            // Build array showing, for each outcome, which sections of the course use it.
            $outcomesections = [];
            foreach ($tiles as $tile) {
                if (isset($tile['tileoutcomeid']) && $tile['tileoutcomeid']) {
                    // This tile has an outcome attached, so add it to the array of tiles for that outcome.
                    $outcomesections[$tile['tileoutcomeid']][] = $tile['tileid'];
                }
            }

            // For each outcome found on tiles, add its outcome name and all tiles found for it to return array.
            $buttonid = $firstbuttonid;
            foreach ($outcomesections as $outcomeid => $outcomesectionsthisoutcome) {
                if (array_key_exists($outcomeid, $outcomenames)) {
                    $outcomebuttons[] = [
                        'id' => 'filterbutton' . $buttonid,
                        'title' => $outcomenames[$outcomeid],
                        'sections' => json_encode(array_values($outcomesectionsthisoutcome)),
                        'buttonnum' => $buttonid,
                    ];
                }
                $buttonid++;
            }
        }
        return $outcomebuttons;
    }


    /**
     * Get the details of the filter buttons to be displayed at the top of this course
     * where the teacher has selected to use numbered filter buttons e.g. button 1 might
     * filter to tiles 1-3, button 2 to tiles 4-6 etc
     * @see get_button_map() which calls this function
     * @param array $tiles the tiles which relate to filters
     * @return array the button details
     */
    public static function get_filter_numbered_buttons_data(array $tiles) {
        $numberoftiles = count($tiles);
        if ($numberoftiles == 0) {
            return [];
        }

        // Find out the number to use for each tile from its title e.g. "1 Introduction" filters to "1".
        $tilenumbers = [];
        foreach ($tiles as $tile) {
            if ($statednum = self::get_stated_tile_num($tile)) {
                $tilenumbers[$statednum] = $tile['tileid'];
            }
        }
        ksort($tilenumbers);

        // Break the tiles down into chunks - one chunk per button.

        if ($numberoftiles <= 15) {
            $tilesperbutton = 3;
        } else if ($numberoftiles <= 30) {
            $tilesperbutton = 4;
        } else {
            $tilesperbutton = 6;
        }

        $buttons = array_chunk($tilenumbers, $tilesperbutton, true);

        // Now populate each button and map the tile details to it.
        $buttonmap = [];
        $buttonid = 1;
        foreach ($buttons as $tilesthisbutton) {
            if (!empty($tiles)) {
                $tilestatednumers = array_keys($tilesthisbutton);
                if ($tilestatednumers[0] == end($tilestatednumers)) {
                    $title = $tilestatednumers[0];
                } else {
                    $title = $tilestatednumers[0] . '-' . end($tilestatednumers);
                }
                $buttonmap[] = [
                    'id' => 'filterbutton' . $buttonid,
                    'title' => $title,
                    'sections' => json_encode(array_values($tilesthisbutton)),
                    'buttonnum' => $buttonid,
                ];
            }
            $buttonid++;
        }
        return $buttonmap;
    }

    /**
     * Get the number which the author has stated for this tile so that it can
     * be used for filter buttons.  e.g. "1 Introduction" or "Week 1 Introduction" give
     * a filtering number of 1
     *
     * @param array $tile the tile output data
     * @return string HTML to output.
     */
    private static function get_stated_tile_num($tile) {
        if (!$tile['title']) {
            return $tile['tileid'];
        } else {
            // If title for example starts "16.2" or "16)" treat it as "16".
            $title = str_replace(')', ' ', str_replace('.', ' ', $tile['title']));
            $title = explode(' ', $title);
            for ($i = 0; $i <= count($title) - 1; $i++) {
                // Iterate through each word in the title and see if it's a number - if it is, we have what we want.
                $statednumber = preg_replace('/[^0-9]/', '', $title[$i]);
                if ($statednumber && ctype_digit($statednumber)) {
                    return intval($statednumber);
                }
            }
        }
        return null;
    }

    /**
     * Get an array of all the Outcomes set for this course by the teacher, so that they can
     * be attached to individual Tiles, and then used to filter tiles by Outcome
     * @see get_filter_outcome_buttons()
     * @see course_format_options() and the displayfilterbar option
     * @param int $courseid
     * @return array
     */
    public static function get_course_outcomes(int $courseid): array {
        global $CFG;
        if (!empty($CFG->enableoutcomes)) {
            require_once($CFG->libdir . '/gradelib.php');
            $outcomes = [];
            $outcomesfull = \grade_outcome::fetch_all_available($courseid);
            foreach ($outcomesfull as $outcome) {
                $outcomes[$outcome->id] = $outcome->fullname;
            }
            asort($outcomes);
            return $outcomes;
        } else {
            return [];
        }
    }
}
