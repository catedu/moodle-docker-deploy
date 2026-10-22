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

/**
 * Function to upgrade quizaccess_seb plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Result.
 */
function xmldb_quizaccess_onesession_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024010802) {

        // Purging previous records, they were md5, and cannot be used anymore.
        // This is tradeoff. Let's hope no one will use major system update to cheat the quiz access rule.
        // If we do not clear the table, then all teachers will have to manually allow students to take the quiz again.
        $DB->delete_records('quizaccess_onesession_sess');

        // Changing precision of field sessionhash on table quizaccess_onesession_sess to (255).
        $table = new xmldb_table('quizaccess_onesession_sess');
        $field = new xmldb_field('sessionhash', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'attemptid');

        // Launch change of precision for field sessionhash.
        $dbman->change_field_precision($table, $field);

        // Onesession savepoint reached.
        upgrade_plugin_savepoint(true, 2024010802, 'quizaccess', 'onesession');
    }

    return true;
}

