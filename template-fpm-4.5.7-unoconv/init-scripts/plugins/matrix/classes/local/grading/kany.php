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
namespace qtype_matrix\local\grading;

use coding_exception;
use qtype_matrix\local\interfaces\grading;
use qtype_matrix\local\lang;
use qtype_matrix\local\qtype_matrix_grading;
use qtype_matrix_question;

/**
 * Per row grading. The total grade is the average of grading received
 * for reach one of the rows.
 *
 * Any correct and no wrong answer to get 100% otherwise 0
 */
class kany extends qtype_matrix_grading implements grading {

    const TYPE = 'kany';

    public static function get_name(): string {
        return self::TYPE;
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public static function get_title(): string {
        return lang::get(self::TYPE);
    }

    /**
     * Factory
     *
     * @return kany
     */
    public static function create_grade(): kany {
        static $result = false;
        if ($result) {
            return $result;
        }
        return $result = new self();
    }

    public function grade_question(qtype_matrix_question $question, array $answers): float {
        $numberofcorrectrows = 0.0;
        foreach ($question->rows as $row) {
            $grade = $this->grade_row($question, $row, $answers);
            if ($grade >= 1) {
                $numberofcorrectrows++;
            }
        }
        if ($numberofcorrectrows == count($question->rows)) {
            return 1.0;
        } else if ((count($question->rows) - $numberofcorrectrows) == 1) {
            return 0.5;
        }
        return 0.0;
    }

    /**
     * Grade a row
     *
     * @param qtype_matrix_question $question  The question to grade
     * @param integer|object         $row       Row to grade
     * @param array                  $responses User's responses
     * @return float                            The row grade, either 0 or 1
     */
    public function grade_row(qtype_matrix_question $question, $row, array $responses): float {
        $onecorrectanswer = false;
        foreach ($question->cols as $col) {
            $answer = $question->answer($row, $col);
            $response = $question->response($responses, $row, $col);
            if (!$answer && $response) {
                return 0;
            }
            if ($answer && $response) {
                $onecorrectanswer = true;
            }
        }
        return ($onecorrectanswer) ? 1.0 : 0.0;
    }
}
