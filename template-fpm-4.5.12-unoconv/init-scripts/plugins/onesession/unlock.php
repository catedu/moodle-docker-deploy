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

use mod_quiz\quiz_attempt;
use quizaccess_onesession\event\attempt_unlocked;

require_once('../../../../config.php');

require_login();
require_sesskey();

$attemptid = required_param('attempt', PARAM_INT);
$attemptobj = quiz_attempt::create($attemptid);
$context = $attemptobj->get_quizobj()->get_context();
require_capability('quizaccess/onesession:unlockattempt', $context);

$DB->delete_records('quizaccess_onesession_sess', ['attemptid' => $attemptid]);

$params = [
    'objectid' => $attemptobj->get_attemptid(),
    'relateduserid' => $attemptobj->get_userid(),
    'courseid' => $attemptobj->get_courseid(),
    'context' => $context,
    'other' => [
        'quizid' => $attemptobj->get_quizid(),
    ],
];
$event = attempt_unlocked::create($params);
$event->trigger();

$url = $attemptobj->get_quizobj()->review_url($attemptid);
redirect($url);
