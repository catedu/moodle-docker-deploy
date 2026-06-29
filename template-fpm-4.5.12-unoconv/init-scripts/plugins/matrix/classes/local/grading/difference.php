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

class difference extends qtype_matrix_grading implements grading {

    const TYPE = 'difference';

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
     * @return difference
     */
    public static function create_grade(): difference {
        static $result = false;
        if ($result) {
            return $result;
        }
        return $result = new self();
    }

    public function grade_question(qtype_matrix_question $question, array $answers): float {
        $grades = [];
        if (count($question->rows) == 0) {
            return 0.0;
        }
        foreach ($question->rows as $row) {
            $grades[] = $this->grade_row($question, $row, $answers);
        }
        return (array_sum($grades) / count($question->rows));
    }

    /**
     * Grade a row
     *
     * @param qtype_matrix_question $question  The question to grade
     * @param integer|object        $row       Row to grade
     * @param array                 $responses User's responses
     * @return float                            The row grade, either 0 or 1
     */
    public function grade_row(qtype_matrix_question $question, $row, array $responses): float {
        $ansid = 1;
        $respid = 1;
        $ansbool = false;
        $resbool = false;
        // Foreach through the elements and count the elements to the response and answer.
        foreach ($question->cols as $col) {
            $answer = $question->answer($row, $col);
            if (!$ansbool && !$answer) {
                $ansid++;
            } else {
                $ansbool = true;
            }
            $response = $question->response($responses, $row, $col);
            if (!$resbool && !$response) {
                $respid++;
            } else {
                $resbool = true;
            }
        }
        if (!$resbool) {
            return 0.0;
        }
        $badleft = $ansid - 1;
        $badright = count($question->cols) - $ansid;
        if ($badleft >= $badright) {
            $maxbadvalue = pow($badleft, 2);
        } else {
            $maxbadvalue = pow($badright, 2);
        }
        return ($maxbadvalue - pow($ansid - $respid, 2)) / $maxbadvalue;
    }
}
