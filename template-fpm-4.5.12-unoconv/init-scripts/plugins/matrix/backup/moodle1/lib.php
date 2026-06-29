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
 * Matrix question type conversion handler
 */
class moodle1_qtype_matrix_handler extends moodle1_qtype_handler {

    /**
     * Returns the list of paths within one <QUESTION> that this qtype needs to have included
     * in the grouped question structure
     *
     * @return array of strings
     */
    public function get_question_subpaths(): array {
        return [
            'MATRIX',
            'MATRIX/ROWS/ROW',
            'MATRIX/COLS/COL',
            'MATRIX/WEIGHTS/WEIGHT'
        ];
    }

    /**
     * Gives the qtype handler a chance to write converted data into questions.xml
     *
     * @param array $data grouped question data
     * @param array $raw  grouped raw QUESTION data
     * @throws moodle1_convert_exception|xml_writer_exception
     */
    public function process_question(array $data, array $raw): void {
        $matrix = $data['matrix'][0];
        $matrix['id'] = $matrix['id'] ?? self::create_id();

        $this->xmlwriter->begin_tag('matrix');
        $this->xmlwriter->full_tag('id', $matrix['id']);
        $this->xmlwriter->full_tag('grademethod', $matrix['grademethod']);
        $this->xmlwriter->full_tag('multiple', $matrix['multiple']);
        $this->xmlwriter->full_tag('renderer', $matrix['grademethod']);

        $this->xmlwriter->begin_tag('cols');
        foreach ($matrix['cols']['col'] as $col) {
            $this->write_xml('col', $col);
        }
        $this->xmlwriter->end_tag('cols');

        $this->xmlwriter->begin_tag('rows');
        foreach ($matrix['rows']['row'] as $row) {
            $this->write_xml('row', $row);
        }
        $this->xmlwriter->end_tag('rows');

        $this->xmlwriter->begin_tag('weights');
        foreach ($matrix['weights']['weight'] as $weight) {
            $this->write_xml('weight', $weight);
        }
        $this->xmlwriter->end_tag('weights');

        $this->xmlwriter->end_tag('matrix');
    }

    public static function create_id(): int {
        static $result = 0;
        return $result++;
    }

}
