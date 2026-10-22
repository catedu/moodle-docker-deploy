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

namespace qtype_matrix;

use advanced_testcase;
use qtype_matrix;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/matrix/questiontype.php');

/**
 * Unit tests for the matrix question definition class.
 */
class qtype_matrix_test extends advanced_testcase {

    protected $qtype;

    public function setUp(): void {
        $this->qtype = new qtype_matrix();
    }

    public function tearDown(): void {
        $this->qtype = null;
    }

    /**
     * @covers ::get_expected_data
     * @return void
     */

    public function test_name(): void {
        $this->assertEquals($this->qtype->name(), 'matrix');
    }

    /**
     * @covers ::get_expected_data
     * @return void
     */
    public function test_cell_name(): void {
        $id = qtype_matrix::defaut_grading()->cell_name(0, 0, true);
        $match = preg_match('/[a-zA-Z_][a-zA-Z0-9_]*/', $id);
        $this->assertSame(1, $match);

        $id = qtype_matrix::defaut_grading()->cell_name(0, 0, false);
        $match = preg_match('/[a-zA-Z_][a-zA-Z0-9_]*/', $id);
        $this->assertSame(1, $match);
    }
}
