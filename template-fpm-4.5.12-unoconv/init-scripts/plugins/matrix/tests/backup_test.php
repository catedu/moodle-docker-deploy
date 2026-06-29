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

use question_bank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/course/externallib.php');

/**
 * Tests for the matrix question type backup and restore logic.
 *
 * @package   qtype_matrix
 * @copyright 2020 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
final class backup_test extends \advanced_testcase {

    /**
     * Duplicate quiz with a matrix question, and check it worked.
     */
    public function test_duplicate_matrix_question(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $coregenerator = $this->getDataGenerator();
        $questiongenerator = $coregenerator->get_plugin_generator('core_question');

        // Create a course with a page that embeds a question.
        $course = $coregenerator->create_course();
        $quiz = $coregenerator->create_module('quiz', ['course' => $course->id]);
        $quizcontext = \context_module::instance($quiz->cmid);

        $cat = $questiongenerator->create_question_category(['contextid' => $quizcontext->id]);
        $question = $questiongenerator->create_question('matrix', 'test_qtype_matrix', ['category' => $cat->id]);

        // Store some counts.
        $numquizzes = count(get_fast_modinfo($course)->instances['quiz']);
        $nummatrixquestions = $DB->count_records('question', ['qtype' => 'matrix']);

        // Duplicate the page.
        duplicate_module($course, get_fast_modinfo($course)->get_cm($quiz->cmid));

        // Verify the copied quiz exists.
        $this->assertCount($numquizzes + 1, get_fast_modinfo($course)->instances['quiz']);

        // Verify the copied question.
        $this->assertEquals($nummatrixquestions + 1, $DB->count_records('question', ['qtype' => 'matrix']));
        $newmatrixid = $DB->get_field_sql("
                SELECT MAX(id)
                  FROM {question}
                 WHERE qtype = ?
                ", ['matrix']);
        $matrixdata = question_bank::load_question_data($newmatrixid);

        $subquestions = array_values($matrixdata->options->subquestions);

        $this->assertSame('System.out.println(0);', $subquestions[0]->questiontext);
        $this->assertSame('0', $subquestions[0]->answertext);

        $this->assertSame('System.out.println(0.0);', $subquestions[1]->questiontext);
        $this->assertSame('0.0', $subquestions[1]->answertext);

        $this->assertSame('', $subquestions[2]->questiontext);
        $this->assertSame('NULL', $subquestions[2]->answertext);
    }
}
