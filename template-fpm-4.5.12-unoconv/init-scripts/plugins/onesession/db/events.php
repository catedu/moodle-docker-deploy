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

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_abandoned',
        'callback'  => '\quizaccess_onesession\observers::unlock_attempt',
    ],

    [
        'eventname' => '\mod_quiz\event\attempt_becameoverdue',
        'callback'  => '\quizaccess_onesession\observers::unlock_attempt',
    ],

    [
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback'  => '\quizaccess_onesession\observers::unlock_attempt',
    ],

    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => '\quizaccess_onesession\observers::unlock_attempt',
    ],

];
