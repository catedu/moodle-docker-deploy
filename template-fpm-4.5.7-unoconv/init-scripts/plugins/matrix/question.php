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
 * The question type class for the matrix question type.
 *
 */

use qtype_matrix\local\lang;
use qtype_matrix\local\qtype_matrix_grading;

/**
 * Represents a matrix question.
 */
class qtype_matrix_question extends question_graded_automatically_with_countback {

    const KEY_ROWS_ORDER = '_order';

    public $rows;
    public $cols;
    public $weights;
    public $grademethod;
    public $multiple;
    public $shuffleanswers;
    public $usedndui;

    /**
     * Contains the keys of the rows array
     * Used to maintain order when shuffling answers
     *
     * @var array
     */
    protected $order = null;

    /**
     * The user's response of cell at $row, $col. That is if the cell is checked or not.
     * If the user didn't make an answer at all (no response) the method returns false.
     *
     * @param array $response object containing the raw answer data
     * @param mixed $row      matrix row, either an id or an object
     * @param mixed $col      matrix col, either an id or an object
     *
     * @return boolean True if the cell($row, $col) was checked by the user. False otherwise.
     */
    public function response(array $response, $row, $col): bool {
        // A student may respond with a question with the multiple answer turned on.
        // Later the teacher may turn that flag off. The result is that the question
        // and response formats won't match.
        //
        // To fix that problem we don't use the question->multiple flag but instead we
        // use the user's response to detect the correct value.
        //
        // Note
        // A part of the problems come from the fact that we use two representation formats
        // depending on the multiple flags. The cause is the html matrix representation
        // that requires two differents views (checkboxes or radio). This representation
        // then leaks to memory.
        //
        // A better strategy would be to use only one normalized representation in memory.
        // The same way we have only one representation in the DB. For that we
        // would need to transform the html form data after the post.
        // Not sure if we can do it.
        $responsemultiple = $this->multiple;
        foreach ($response as $key => $value) {
            $responsemultiple = (strpos($key, '_') !== false);
            break;
        }

        $key = $this->key($row, $col, $responsemultiple);
        $value = $response[$key] ?? false;
        if ($value === false) {
            return false;
        }

        if ($responsemultiple) {
            return !empty($value);
        }

        return $value == $col->id;
    }

    /**
     *
     * @param mixed        $row
     * @param mixed        $col
     * @param boolean|null $multiple
     * @return string
     */
    public function key($row, $col, bool $multiple = null): string {
        $rowid = is_object($row) ? $row->id : $row;
        $colid = is_object($col) ? $col->id : $col;
        $multiple = (is_null($multiple)) ? $this->multiple : $multiple;
        return qtype_matrix_grading::cell_name($rowid, $colid, $multiple);
    }

    /**
     * Returns the expected answer for the cell at $row, $col.
     *
     * @param integer|object $row
     * @param integer|object $col
     *
     * @return boolean  True if cell($row, $col) is correct, false otherwise.
     */
    public function answer($row = null, $col = null): bool {
        return $this->weight($row, $col) > 0;
    }

    /**
     *
     * @param mixed $row
     * @param mixed $col
     * @return float
     */
    public function weight($row = null, $col = null): float {
        // Todo: What the heck is this? It is used in two ways? Better Split it up then!
        if (is_string($row) && is_null($col)) {
            $key = str_replace('cell', $col, $row);
            [$rowid, $colid] = explode('x', $key);
        } else {
            $rowid = is_object($row) ? $row->id : $row;
            $colid = is_object($col) ? $col->id : $col;
        }
        return (float) $this->weights[$rowid][$colid];
    }

    /**
     * Start a new attempt at this question, storing any information that will
     * be needed later in the step.
     *
     * This is where the question can do any initialisation required on a
     * per-attempt basis. For example, this is where the multiple choice
     * question type randomly shuffles the choices (if that option is set).
     *
     * Any information about how the question has been set up for this attempt
     * should be stored in the $step, by calling $step->set_qt_var(...).
     *
     *
     * @param question_attempt_step $step
     *          The first step of the {@link question_attempt} being started.
     *          Can be used to store state.
     * @param int                   $variant
     *          Which variant of this question to start. Will be between
     *          1 and {@link get_num_variants()} inclusive.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function start_attempt(question_attempt_step $step, $variant): void {
        global $PAGE;
        if ($this->usedndui && !$PAGE->requires->is_head_done()) {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
            $PAGE->requires->js_call_amd('qtype_matrix/dnd', 'init');
        }
        $this->order = array_keys($this->rows);
        if ($this->shuffle_answers()) {
            shuffle($this->order);
        }
        $this->write_data($step);
    }

    /**
     * @return bool True if rows should be shuffled. False otherwise.
     * @throws dml_exception
     */
    public function shuffle_answers(): bool {
        if (!$this->shuffle_authorized()) {
            return false;
        }
        return $this->shuffleanswers;
    }

    /**
     * Question shuffle can be disabled at the Quiz level. If false then the
     * question parts are not shuffled. If true then the question's shuffle parameter
     * decide wheter the question's parts are actually shuffled.
     *
     * If the question is executed outside of a Quiz (for example in preview)
     * returns true.
     *
     * @return boolean          True if shuffling is authorized. False otherwise.
     * @throws dml_exception
     * @global object $DB   Database object
     * @global object $PAGE Page object
     */
    public function shuffle_authorized(): bool {
        global $DB, $PAGE;
        $cm = $PAGE->cm;
        if (!is_object($cm)) {
            return true;
        }
        // There is no API for activities to detect whether they use questions or may shuffle them
        // So we just allow shuffling for any other activity than quiz
        if ($cm->modname != 'quiz') {
            return true;
        }

        return $DB->get_record('quiz', ['id' => $cm->instance])->shuffleanswers ?? $this->shuffleanswers;
    }

    /**
     * Write persistent data to a step for further retrieval
     *
     * @param question_attempt_step $step
     * @return void
     * @throws coding_exception
     */
    protected function write_data(question_attempt_step $step): void {
        $step->set_qt_var(self::KEY_ROWS_ORDER, implode(',', $this->order));
    }

    /**
     * When an in-progress {@link question_attempt} is re-loaded from the
     * database, this method is called so that the question can re-initialise
     * its internal state as needed by this attempt.
     *
     * For example, the multiple choice question type needs to set the order
     * of the choices to the order that was set up when start_attempt was called
     * originally. All the information required to do this should be in the
     * $step object, which is the first step of the question_attempt being loaded.
     *
     *
     * @param question_attempt_step $step The first step of the {@link question_attempt}
     *                                    being loaded.
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public function apply_attempt_state(question_attempt_step $step): void {
        if ($this->usedndui) {
            global $PAGE;
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
            $PAGE->requires->js_call_amd('qtype_matrix/dnd', 'init');
        }
        $this->load_data($step);
    }

    /**
     * Load persistent data from a step.
     *
     * @param question_attempt_step $step Storage
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    protected function load_data(question_attempt_step $step): void {
        $order = $step->get_qt_var(self::KEY_ROWS_ORDER);
        if ($order !== null) {
            $this->order = explode(',', $order);
        } else {
            // The order doesn't exist in the database.
            // This can happen because the question is old and doesn't have the shuffling possibility yet.
            $this->order = array_keys($this->rows);
            if ($this->shuffle_answers()) {
                shuffle($this->order);
            }
            $this->write_data($step); // Todo: Does this solves https://github.com/ndunand/moodle-qtype_matrix/issues/31 ?
        }

        // Rows can be deleted between attempts. We need therefore to remove
        // those that were stored in the step but are not present anymore.

        $rowsremoved = [];
        foreach ($this->order as $rowkey) {
            if (!isset($this->rows[$rowkey])) {
                $rowsremoved[] = $rowkey;
            }
        }
        $this->order = array_diff($this->order, $rowsremoved);

        // Rows can be added between attempts. We need therefore to add those
        // rows that were not stored in the step.

        $rowsadded = [];
        $rowskeys = array_keys($this->rows);
        foreach ($rowskeys as $rowkey) {
            if (!in_array($rowkey, $this->order)) {
                $rowsadded[] = $rowkey;
            }
        }
        if ($this->shuffle_answers()) {
            shuffle($rowsadded);
        }
        foreach ($rowsadded as $rowkey) {
            $this->order[] = $rowkey;
        }
    }

    /**
     * @param question_attempt $qa
     * @return array
     * @throws coding_exception
     */
    public function get_order(question_attempt $qa): array {
        $this->init_order($qa);
        return $this->order;
    }

    /**
     * @param question_attempt $qa
     * @return void
     * @throws coding_exception
     */
    protected function init_order(question_attempt $qa): void {
        if ($this->order) {
            return;
        }
        $this->order = explode(',', $qa->get_step(0)->get_qt_var(self::KEY_ROWS_ORDER));
    }

    /**
     * Work out a final grade for this attempt, taking into account all the
     * tries the student made.
     *
     * @param array $responses  the response for each try. Each element of this
     *                          array is a response array, as would be passed to {@link grade_response()}.
     *                          There may be between 1 and $totaltries responses.
     *
     * @param int   $totaltries The maximum number of tries allowed.
     *
     * @return numeric the fraction that should be awarded for this
     * sequence of response.
     */
    public function compute_final_grade($responses, $totaltries): float {
        $gradevalue = 0;
        foreach ($responses as $response) {
            $x = $this->grade_response($response);
            $gradevalue += $x[0];
        }
        return $gradevalue;
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and 1.0, and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *                        {@link question_attempt_step::get_qt_data()}.
     * @return array (number, integer) the fraction, and the state.
     */
    public function grade_response(array $response): array {
        $grade = $this->grading()->grade_question($this, $response);
        $state = question_state::graded_state_for_fraction($grade);
        return [$grade, $state];
    }

    /**
     *
     * @return qtype_matrix_grading
     */
    public function grading(): qtype_matrix_grading {
        return qtype_matrix::grading($this->grademethod);
    }

    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete. That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *                        {@link question_attempt_step::get_qt_data()}.
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response): bool {
        if ($this->multiple) {
            return true;
        }
        $count = 0;
        foreach ($this->rows as $row) {
            $key = $this->key($row, 0);
            if (isset($response[$key])) {
                $count++;
            }
        }

        // Always return false when not at least one row is answered, since this is not considered partial.
        if ($count == 0) {
            return false;
        }
        // We know that the count is unequal to 0, so we only need to check if its complete and if we have not a partial type.
        if ($count != count($this->rows)) {
            return false;
        }
        return true;
    }

    public function is_question_partial_gradable(): bool {
        return $this->grademethod == 'all' || $this->grademethod == 'difference';
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     *
     * @param array $response
     * @return string the message.
     * @throws coding_exception
     */
    public function get_validation_error(array $response): ?string {
        if (!$this->is_complete_response($response)) {
            return lang::one_answer_per_row();
        }
        return null;
    }

    /**
     * Get the number of selected options
     *
     * @param array $response responses, as returned by
     *                        {@see question_attempt_step::get_qt_data()}.
     * @return int the number of choices that were selected. in this response.
     */
    public function get_num_selected_choices(array $response): int {
        $numselected = 0;
        foreach ($response as $key => $value) {
            if (!empty($value) && $key[0] != '_') {
                $numselected += 1;
            }
        }
        return $numselected;
    }

    /**
     * Use by many of the behaviours to determine whether the student
     * has provided enough of an answer for the question to be graded automatically,
     * or whether it must be considered aborted.
     *
     * @param array $response responses, as returned by
     *                        {@see question_attempt_step::get_qt_data()}.
     * @return bool whether this response can be graded.
     */
    public function is_gradable_response(array $response): bool {
        if ($this->is_question_partial_gradable()) {
            if ($this->get_num_selected_choices($response) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            foreach ($this->rows as $row) {
                foreach ($this->cols as $col) {
                    $key = $this->key($row, $col);
                    if (!empty($response[$key])) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    /**
     * Produce a plain text summary of a response.
     *
     * @param array $response A response, as might be passed to {@link grade_response()}.
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response): string {
        $result = [];
        foreach ($this->order ?? array_keys($this->rows) as $rowid) {
            $row = $this->rows[$rowid];
            foreach ($this->cols as $col) {
                $key = $this->key($row, $col);
                $value = $response[$key] ?? false;
                if ($value === $col->id || $value === 'on') {
                    $result[] = "$row->shorttext: $col->shorttext";
                }
            }
        }
        return implode("; ", $result);
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed. This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *                            as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse  the new responses, in the same format.
     * @return bool whether the two sets of responses are the same - that is
     *                            whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse): bool {
        if (count($prevresponse) != count($newresponse)) {
            return false;
        }
        foreach ($prevresponse as $key => $previousvalue) {
            if (!isset($newresponse[$key])) {
                return false;
            }
            $newvalue = $newresponse[$key];
            if ($newvalue != $previousvalue) {
                return false;
            }
        }

        return true;
    }

    /**
     * What data would need to be submitted to get this question correct.
     * If there is more than one correct answer, this method should just
     * return one possibility.
     *
     * @return array parameter name => value.
     */
    public function get_correct_response(): array {
        $result = [];
        foreach ($this->order ?? array_keys($this->rows) as $rowid) {
            $row = $this->rows[$rowid];
            foreach ($this->cols as $col) {
                $weight = $this->weight($row, $col);
                $key = $this->key($row, $col);
                if ($weight > 0) {
                    $result[$key] = $this->multiple ? 'on' : $col->id;
                }
            }
        }

        return $result;
    }

    /**
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *      that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *      meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data(): array {
        $result = [];
        $cells = $this->cells();
        foreach ($cells as $key => $weight) {
            $result[$key] = $this->multiple ? PARAM_BOOL : PARAM_INT;
        }
        return $result;
    }

    /**
     * Returns an array where keys are the weights' cell names and the values
     * are the weights
     *
     * @return array
     */
    public function cells(): array {
        $result = [];
        foreach ($this->order as $rowid) {
            $row = $this->rows[$rowid];
            foreach ($this->cols as $col) {
                $result[$this->key($row, $col)] = $this->weight($row, $col);
            }
        }
        return $result;
    }

    /**
     * Returns the name field name for input cells in the questiondisplay.
     * The column parameter is ignored for now since we don't use multiple answers.
     * @param int $key
     * @return string
     */
    public function field($key) {
        return 'cell' . $key;
    }

    /**
     * Categorise the student's response according to the categories defined by get_possible_responses.
     * @param array $response a response, as might be passed to  grade_response().
     * @return array subpartid => question_classified_response objects.
     *      returns an empty array if no analysis is possible.
     */
    public function classify_response(array $response) {
        // See which column numbers have been selected.
        $selectedcolumns = $this->get_selected_columns($response);

        return $this->multiple ? $this->classify_multi_response($selectedcolumns) : $this->classify_simple_response($selectedcolumns);
    }

    protected function classify_multi_response($selectedcolumns) {
        $parts = [];
        foreach ($this->rows as $rowid => $row) {
            $subparts = [];
            foreach ($this->cols as $colid => $col) {
                if (isset($selectedcolumns['cell'.$rowid.'_'.$colid])) {
                    $partialcredit = 0;
                    if ($this->weights[$rowid][$colid] > 0) {
                        $partialcredit = $this->grademethod == 'all' ? (1 / count($this->rows)) : 1;
                    }
                    $subparts[$colid] = new question_classified_response($colid, $col->shorttext, $partialcredit);
                }
            }

            if (empty($subparts)) {
                $parts[$rowid] = question_classified_response::no_response();
            } else {
                $parts[$rowid] = $subparts;
            }
        }
        return $parts;
    }

    protected function classify_simple_response($selectedcolumns) {
        $parts = [];
        foreach ($this->rows as $rowid => $row) {

            if (empty($selectedcolumns[$rowid])) {
                $parts[$rowid] = question_classified_response::no_response();
                continue;
            }

            // Find the chosen column by columnnumber.
            $column = null;
            foreach ($this->cols as $colid => $col) {
                if ($colid == $selectedcolumns[$rowid]) {
                    $column = $col;
                    break;
                }
            }

            $partialcredit = 0;

            if ($this->weights[$rowid][$column->id] > 0) {
                $partialcredit = $this->grademethod == 'all' ? (1 / count($this->rows)) : 1;
            }

            $parts[$rowid] = new question_classified_response($column->id, $column->shorttext, $partialcredit);
        }

        return $parts;
    }

    protected function get_selected_columns(array $response): array {
        $selectedcolumns = [];
        foreach ($this->order as $rowid) {
            foreach ($this->cols as $colid => $col) {
                $field = $this->multiple ? $this->key($rowid, $colid, true) : $this->field($rowid);
                if (property_exists((object) $response, $field) && $response[$field]) {
                    $selectedcolumns[$this->multiple ? $field : $rowid] = $this->multiple ? $colid : $response[$field];
                }
            }
        }

        return $selectedcolumns;
    }
}
