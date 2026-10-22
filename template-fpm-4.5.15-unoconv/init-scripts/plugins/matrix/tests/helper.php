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
 * Test helpers for the truefalse question type.
 */

/**
 * Test helper class for the matrix question type.
 */
class qtype_matrix_test_helper extends question_test_helper {

    public function get_test_questions(): array {
        return ['kprime', 'all', 'any', 'none', 'weighted', 'multiple', 'single'];
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_multiple(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->multiple = true;
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_single(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->multiple = false;
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_kprime(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->grademethod = 'kprime';
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_all(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->grademethod = 'all';
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_any(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->grademethod = 'any';
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_none(): qtype_matrix_question {
        $result = $this->make_matrix_question();
        $result->grademethod = 'none';
        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    public function make_matrix_question_weighted() {
        $result = $this->init_matrix_question();

        for ($r = 0; $r < 4; $r++) {
            $row = (object) [];
            $row->id = $r;
            $row->shorttext = "Row $r";
            $row->description = "Description $r";
            $row->feedback = "Feedback $r";
            $result->rows[$r] = $row;
            for ($c = 0; $c < 4; $c++) {
                $col = (object) [];
                $col->id = $c;
                $col->shorttext = "Column $c";
                $col->description = "Description $c";
                $result->cols[$c] = $col;

                $result->weights[$r][$c] = ($c < 2) ? 0.5 : 0;
            }
        }

        $result->grademethod = 'weighted';
        $result->multiple = true;

        return $result;
    }

    /**
     *
     * @return qtype_matrix_question
     * @throws coding_exception
     */
    protected function make_matrix_question() {
        $result = $this->init_matrix_question();

        for ($r = 0; $r < 4; $r++) {
            $row = (object) [];
            $row->id = $r;
            $row->shorttext = "Row $r";
            $row->description = "Description $r";
            $row->feedback = "Feedback $r";
            $result->rows[$r] = $row;
            for ($c = 0; $c < 4; $c++) {
                $col = (object) [];
                $col->id = $c;
                $col->shorttext = "Column $c";
                $col->description = "Description $c";
                $result->cols[$c] = $col;

                $result->weights[$r][$c] = ($c == 0) ? 1 : 0;
            }
        }

        $result->grademethod = 'kprime';
        $result->multiple = true;

        return $result;
    }

    /**
     * @return \qtype_matrix_question
     * @throws \coding_exception
     */
    public function init_matrix_question(): qtype_matrix_question {
        question_bank::load_question_definition_classes('matrix');
        $result = new qtype_matrix_question();
        test_question_maker::initialise_a_question($result);
        $result->name = 'Matrix question';
        $result->questiontext = 'K prime graded question.';
        $result->generalfeedback = 'First column is true.';
        $result->penalty = 1;
        $result->qtype = question_bank::get_qtype('matrix');

        $result->rows = [];
        $result->cols = [];
        $result->weights = [];
        return $result;
    }
}
