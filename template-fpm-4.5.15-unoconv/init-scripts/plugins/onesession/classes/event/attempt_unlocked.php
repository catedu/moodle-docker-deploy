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
 * Rule that blocks attempt to open same quiz attempt in other session
 *
 * @package    quizaccess_onesession
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_onesession\event;

use coding_exception;
use core\event\base;
use moodle_url;

/**
 * Attempt unlocked event.
 *
 * @package    quizaccess_onesession
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_unlocked extends base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventattemptunlocked', 'quizaccess_onesession');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' allowed student with id '$this->relateduserid' to change device for "
                . "attempt with id '$this->objectid' for the quiz with course module id '$this->contextinstanceid'.";
    }

    /**
     * Get URL related to the action
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/mod/quiz/review.php', ['attempt' => $this->objectid]);
    }

    /**
     * Custom validation.
     *
     * @throws coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['quizid'])) {
            throw new coding_exception('The \'quizid\' value must be set in other.');
        }
    }

    /**
     * This is used when restoring course logs where it is required that we
     * map the objectid to it's new value in the new course.
     *
     * Does nothing in the base class except display a debugging message warning
     * the user that the event does not contain the required functionality to
     * map this information. For events that do not store an objectid this won't
     * be called, so no debugging message will be displayed.
     *
     * Example of usage:
     *
     * return array('db' => 'assign_submissions', 'restore' => 'submission');
     *
     * If the objectid can not be mapped during restore set the value to \core\event\base::NOT_MAPPED, example -
     *
     * return array('db' => 'some_table', 'restore' => \core\event\base::NOT_MAPPED);
     *
     * Note - it isn't necessary to specify the 'db' and 'restore' values in this case, so you can also use -
     *
     * return \core\event\base::NOT_MAPPED;
     *
     * The 'db' key refers to the database table and the 'restore' key refers to
     * the name of the restore element the objectid is associated with. In many
     * cases these will be the same.
     *
     * @return string the name of the restore mapping the objectid links to
     */
    public static function get_objectid_mapping() {
        return ['db' => 'quiz_attempts', 'restore' => 'quiz_attempt'];
    }

    /**
     * This is used when restoring course logs where it is required that we
     * map the information in 'other' to it's new value in the new course.
     *
     * Does nothing in the base class except display a debugging message warning
     * the user that the event does not contain the required functionality to
     * map this information. For events that do not store any other information this
     * won't be called, so no debugging message will be displayed.
     *
     * Example of usage:
     *
     * $othermapped = array();
     * $othermapped['discussionid'] = array('db' => 'forum_discussions', 'restore' => 'forum_discussion');
     * $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');
     * return $othermapped;
     *
     * If an id can not be mapped during restore we set it to \core\event\base::NOT_MAPPED, example -
     *
     * $othermapped = array();
     * $othermapped['someid'] = array('db' => 'some_table', 'restore' => \core\event\base::NOT_MAPPED);
     * return $othermapped;
     *
     * Note - it isn't necessary to specify the 'db' and 'restore' values in this case, so you can also use -
     *
     * $othermapped = array();
     * $othermapped['someid'] = \core\event\base::NOT_MAPPED;
     * return $othermapped;
     *
     * The 'db' key refers to the database table and the 'restore' key refers to
     * the name of the restore element the other value is associated with. In many
     * cases these will be the same.
     *
     * @return array an array of other values and their corresponding mapping
     */
    public static function get_other_mapping() {
        $othermapped = [];
        $othermapped['quizid'] = ['db' => 'quiz', 'restore' => 'quiz'];

        return $othermapped;
    }
}
