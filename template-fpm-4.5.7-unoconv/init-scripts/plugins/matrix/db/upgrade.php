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

// This file keeps track of upgrades to
// the match qtype plugin
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

/**
 * @param int $oldversion
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_qtype_matrix_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2014040800) {
        // Define table matrix to be created.
        $table = new xmldb_table('question_matrix');
        // Adding fields to table matrix.
        $newfield = $table->add_field('shuffleanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $dbman->add_field($table, $newfield);
        upgrade_plugin_savepoint(true, 2014040800, 'qtype', 'matrix');
    }

    if ($oldversion < 2015070100) {
        // Define table matrix to be created.
        $table = new xmldb_table('question_matrix');
        // Adding fields to table matrix.
        $newfield = $table->add_field('use_dnd_ui', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $dbman->add_field($table, $newfield);
        upgrade_plugin_savepoint(true, 2015070100, 'qtype', 'matrix');
    }

    if ($oldversion < 2023010303) {
        // Rename tables and columns to match the coding guidelines.
        $table = new xmldb_table('question_matrix');
        $dbman->rename_table($table, 'qtype_matrix');

        $table = new xmldb_table('question_matrix_cols');
        $dbman->rename_table($table, 'qtype_matrix_cols');

        $table = new xmldb_table('question_matrix_rows');
        $dbman->rename_table($table, 'qtype_matrix_rows');

        $table = new xmldb_table('question_matrix_weights');
        $dbman->rename_table($table, 'qtype_matrix_weights');

        $table = new xmldb_table('qtype_matrix');
        // Rename the field use_dnd_ui to usedndui because direct working with this variable will be hard in php,
        // when the coding standard don't allow '_' in variable names.
        $newfield = $table->add_field('use_dnd_ui', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $dbman->rename_field($table, $newfield, 'usedndui');

        upgrade_plugin_savepoint(true, 2023010303, 'qtype', 'matrix');
    }
    return true;
}
