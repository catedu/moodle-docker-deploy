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
namespace qtype_matrix\local;

use coding_exception;
use MoodleQuickForm;
use qtype_matrix_question;

/**
 * Base class for grading types
 *
 * @abstract
 */
abstract class qtype_matrix_grading {

    /**
     * @return array
     * @uses \qtype_matrix\local\grading\kany
     * @uses \qtype_matrix\local\grading\kprime
     * @uses \qtype_matrix\local\grading\all
     */
    public static function gradings(): array {
        static $result = false;
        if ($result !== false) {
            return $result;
        }
        $result = [];
        $classlist = ['all', 'kany', 'kprime', 'difference'];
        $namespace = 'qtype_matrix\\local\\grading\\';
        foreach ($classlist as $class) {
            $classname = $namespace . $class;
            $result[] = new $classname();
        }
        return $result;
    }

    public static function default_grading(): qtype_matrix_grading {
        return self::create('kprime');
    }

    /**
     *
     * @param string $type
     * @return qtype_matrix_grading
     */
    public static function create(string $type): qtype_matrix_grading {
        static $result = [];
        if (isset($result[$type])) {
            return $result[$type];
        }
        $class = 'qtype_matrix\\local\\grading\\' . $type;
        $grading = call_user_func([$class, 'create_grade'], $type);
        $result[$type] = $grading;
        return $grading;
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public static function get_title(): string {
        return lang::get(self::get_name());
    }

    public static function get_name(): string {
        return get_called_class();
    }

    /**
     * Create the form element used to define the weight of the cell
     *
     * @param MoodleQuickForm $form
     * @param int             $row      row number
     * @param int             $col      column number
     * @param bool            $multiple whether the question allows multiple answers
     * @return object
     */
    public function create_cell_element(MoodleQuickForm $form, int $row, int $col, bool $multiple): object {
        $cellname = $this->cell_name($row, $col, $multiple);
        if ($multiple) {
            return $form->createElement('checkbox', $cellname, 'label');
        } else {
            return $form->createElement('radio', $cellname, '', '', $col);
        }
    }

    /**
     * Returns a cell name.
     * Should be a valid php and html identifier
     *
     * @param int  $row      row number
     * @param int  $col      col number
     * @param bool $multiple one answer per row or several
     *
     * @return string
     */
    public static function cell_name(int $row, int $col, bool $multiple): string {
        return $multiple ? "cell{$row}_$col" : "cell$row";
    }

    /**
     * Returns the question's grade. By default, it is the average of correct questions.
     *
     * @param qtype_matrix_question $question
     * @param array                 $answers
     * @return float
     */
    public function grade_question(qtype_matrix_question $question, array $answers): float {
        $grades = [];
        foreach ($question->rows as $row) {
            $grades[] = $this->grade_row($question, $row, $answers);
        }
        $result = array_sum($grades) / count($grades);
        $result = min(1, $result);
        return max(0, $result);
    }

    /**
     * Grade a specific row
     *
     * @param qtype_matrix_question $question
     * @param mixed                 $row
     * @param array                 $responses
     * @return float
     */
    public function grade_row(qtype_matrix_question $question, $row, array $responses): float {
        return 0.0;
    }

    /**
     * validate
     *
     * @param array $data the raw form data
     * @return array of errors
     */
    public function validation(array $data): array {
        return [];
    }

    protected function col_count(array $data): int {
        return count($data['cols_shorttext']);
    }

    protected function row_count(array $data): int {
        return count($data['rows_shorttext']);
    }
}
