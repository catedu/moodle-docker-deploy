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
 * Condition file.
 *
 * @package    availability_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_xp;

/**
 * Condition class.
 *
 * @package    availability_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** Greater or equal to (default). */
    const OPERATOR_GTE = 0;
    /** Stricly equal to. */
    const OPERATOR_EQ = 1;

    /** @var int An operator constant. */
    protected $operator = self::OPERATOR_GTE;

    /** @var int The level required. */
    protected $requiredlvl = 0;

    /** @var array Static cache for user level. */
    protected static $lvlcache = [];

    /**
     * Constructor.
     *
     * @param stdClass $structure Saved data.
     */
    public function __construct($structure) {
        if (isset($structure->requiredlvl)) {
            $this->requiredlvl = $structure->requiredlvl;
        }
        if (isset($structure->operator)) {
            $this->operator = $structure->operator;
        }
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * @param bool $not Set true if we are inverting the condition.
     * @param info $info Item we're checking.
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user).
     * @param int $userid User ID to check availability for.
     * @return bool True if available.
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $available = false;

        if (!$userid || isguestuser($userid) || !\core_user::is_real_user($userid)) {
            return false;
        }

        if ($this->requiredlvl > 0) {
            $currentlvl = $this->get_user_level($info->get_course()->id, $userid);

            $available = false;
            if ($this->operator == static::OPERATOR_GTE) {
                $available = $currentlvl >= $this->requiredlvl;
            } else if ($this->operator == static::OPERATOR_EQ) {
                $available = $currentlvl == $this->requiredlvl;
            }

            if ($not) {
                $available = !$available;
            }
        }

        return $available;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * @param bool $full Set true if this is the 'full information' view.
     * @param bool $not Set true if we are inverting the condition.
     * @param info $info Item we're checking.
     * @return string Information string (for admin) about all restrictions on
     *   this item.
     */
    public function get_description($full, $not, \core_availability\info $info) {
        $message = '';
        if ($this->operator == static::OPERATOR_GTE) {
            if ($not) {
                $message = get_string('levelnnotrequiredtoaccess', 'availability_xp', $this->requiredlvl);
            } else {
                $message = get_string('levelnrequiredtoaccess', 'availability_xp', $this->requiredlvl);
            }
        } else if ($this->operator == static::OPERATOR_EQ) {
            if ($not) {
                $message = get_string('levelnexactlynotrequiredtoaccess', 'availability_xp', $this->requiredlvl);
            } else {
                $message = get_string('levelnexactlyrequiredtoaccess', 'availability_xp', $this->requiredlvl);
            }
        }
        return $message;
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters.
     */
    protected function get_debug_string() {
        return $this->operator . ':' . $this->requiredlvl;
    }

    /**
     * Return the current level of the user.
     *
     * @param  int $courseid The course ID.
     * @param  int $userid The user ID.
     * @return int The user level.
     */
    protected function get_user_level($courseid, $userid) {
        // Basic static cache to improve performance.
        $cachekey = $courseid . ':' . $userid;
        if (!isset(self::$lvlcache[$cachekey])) {
            $world = \block_xp\di::get('course_world_factory')->get_world($courseid);
            $store = $world->get_store();
            $state = $store->get_state($userid);
            self::$lvlcache[$cachekey] = $state->get_level()->get_level();
        }
        return self::$lvlcache[$cachekey];
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object) ['type' => $this->get_type(), 'requiredlvl' => $this->requiredlvl, 'operator' => $this->operator];
    }
}
