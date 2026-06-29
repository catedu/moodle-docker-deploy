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
 * Provides the information to backup matrix questions
 */
class backup_qtype_matrix_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     *
     * @return backup_plugin_element
     * @throws base_element_struct_exception
     */
    protected function define_question_plugin_structure(): backup_plugin_element {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'matrix');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures.
        $matrix = new backup_nested_element('matrix',
            ['id'],
            ['grademethod', 'multiple', 'shuffleanswers', 'renderer']);

        $matrixcols = new backup_nested_element('cols');
        $matrixcol = new backup_nested_element('col', ['id'], ['shorttext', 'description']);

        $matrixrows = new backup_nested_element('rows');
        $matrixrow = new backup_nested_element('row', ['id'], ['shorttext', 'description', 'feedback']);

        $matrixweights = new backup_nested_element('weights');
        $matrixweight = new backup_nested_element('weight', ['id'], ['rowid', 'colid', 'weight']);

        // Now the own qtype tree.
        $pluginwrapper->add_child($matrix);

        $matrix->add_child($matrixcols);
        $matrixcols->add_child($matrixcol);

        $matrix->add_child($matrixrows);
        $matrixrows->add_child($matrixrow);

        $matrix->add_child($matrixweights);
        $matrixweights->add_child($matrixweight);

        // Set source to populate the data.
        $matrix->set_source_table('qtype_matrix', ['questionid' => backup::VAR_PARENTID]);
        $matrixcol->set_source_table('qtype_matrix_cols', ['matrixid' => backup::VAR_PARENTID]);
        $matrixrow->set_source_table('qtype_matrix_rows', ['matrixid' => backup::VAR_PARENTID]);

        $sql = 'SELECT w.* FROM {qtype_matrix_weights} w,' .
            ' {qtype_matrix_cols} c,' .
            ' {qtype_matrix_rows} r,' .
            ' {qtype_matrix} m WHERE m.id=:matrixid AND w.rowid=r.id AND w.colid=c.id AND c.matrixid=m.id AND r.matrixid=m.id';
        $matrixweight->set_source_sql($sql, ['matrixid' => backup::VAR_PARENTID]);
        // Don't need to annotate ids nor files.
        return $plugin;
    }
}
