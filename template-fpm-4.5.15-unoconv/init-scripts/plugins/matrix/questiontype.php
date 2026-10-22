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

use qtype_matrix\local\qtype_matrix_grading;
use qtype_matrix\local\question_matrix_store;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * The matrix question class
 *
 * Pretty simple concept - a matrix with a number of different grading methods and options.
 */
class qtype_matrix extends question_type {

    public static function gradings(): array {
        return qtype_matrix_grading::gradings();
    }

    public static function grading(string $type): qtype_matrix_grading {
        return qtype_matrix_grading::create($type);
    }

    /**
     * Deletes question from the question-type specific tables
     *
     * @param int $questionid The question being deleted
     * @return boolean to indicate success of failure.
     * @throws dml_exception
     */
    public function delete_question_options(int $questionid): bool {
        if (empty($questionid)) {
            return false;
        }
        $store = new question_matrix_store();
        $store->delete_question($questionid);
        return true;
    }

    /**
     * Deletes question from the question-type specific tables
     *
     * @param integer $questionid The question being deleted
     * @param integer $contextid
     * @throws dml_exception
     */
    public function delete_question($questionid, $contextid = null) {
        if (empty($questionid)) {
            return;
        }
        $this->delete_question_options($questionid);
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return boolean true if this question type sometimes requires manual grading.
     */
    public function is_manual_graded(): bool {
        return false;
    }

    /**
     *
     * @param object $question
     * @return boolean
     * @throws dml_exception
     */
    public function get_question_options($question): bool {
        parent::get_question_options($question);
        $matrix = self::retrieve_matrix($question->id);
        if ($matrix) {
            $question->options->rows = $matrix->rows;
            $question->options->cols = $matrix->cols;
            $question->options->weights = $matrix->weights;
            $question->options->grademethod = $matrix->grademethod;
            // Allow for old versions which don't have this field.
            $question->options->shuffleanswers = $matrix->shuffleanswers ?? true;
            $question->options->usedndui = $matrix->usedndui;
            $question->options->multiple = $matrix->multiple;
            $question->options->renderer = $matrix->renderer;
        } else {
            $question->options->rows = [];
            $question->options->cols = [];
            $question->options->weights = [[]];
            $question->options->grademethod = self::defaut_grading()->get_name();
            $question->options->shuffleanswers = true;
            $question->options->usedndui = false;
            $question->options->multiple = true;
        }
        return true;
    }

    /**
     * cant type this function -> to many returning options!
     *
     * @param int $questionid
     * @return mixed|stdClass|null
     * @throws dml_exception
     */
    public static function retrieve_matrix(int $questionid) {
        $store = new question_matrix_store();

        if (empty($questionid)) {
            return null;
        }

        $matrix = $store->get_matrix_by_question_id($questionid);
        if (empty($matrix)) {
            return null;
        }
        $matrixid = $matrix->id;
        $matrix->rows = $store->get_matrix_rows_by_matrix_id($matrixid);
        $matrix->cols = $store->get_matrix_cols_by_matrix_id($matrixid);
        $rawweights = $store->get_matrix_weights_by_question_id($questionid);

        // Initialize weights.
        $matrix->weights = [];
        foreach ($matrix->rows as $row) {
            $matrix->weights[$row->id] = [];
            foreach ($matrix->cols as $col) {
                $matrix->weights[$row->id][$col->id] = 0;
            }
        }
        // Set non zero weights.
        foreach ($rawweights as $weight) {
            $matrix->weights[$weight->rowid][$weight->colid] = (float) $weight->weight;
        }
        return $matrix;
    }

    public static function defaut_grading(): qtype_matrix_grading {
        return qtype_matrix_grading::default_grading();
    }

    /**
     * Saves question-type specific options.
     * This is called by {@link save_question()} to save the question-type specific data.
     *
     * @param object $question This holds the information from the editing form, it is not a standard question object.
     * @return object $result->error or $result->noticeyesno or $result->notice
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function save_question_options($question): object {
        global $DB;
        $store = new question_matrix_store();

        $transaction = $DB->start_delegated_transaction();

        $questionid = $question->id;
        $makecopy = (property_exists($question, 'makecopy') && $question->makecopy == '1');

        // The variable $questionid is not equal to matrix->id.
        $matrix = (object) $store->get_matrix_by_question_id($questionid);

        $isnew = empty($matrix->id);

        $matrix->questionid = $questionid;
        $matrix->multiple = $question->multiple;
        $matrix->grademethod = $question->grademethod;
        $matrix->shuffleanswers = $question->shuffleanswers;
        $matrix->usedndui = isset($question->usedndui) ? ($question->usedndui) : (0);

        if ($isnew || $makecopy) {
            $store->insert_matrix($matrix);
        } else {
            $store->update_matrix($matrix);
        }

        $matrixid = $matrix->id;

        // Rows.
        // Mapping for indexes to db ids.
        $rowids = [];
        foreach ($question->rows_shorttext as $i => $short) {
            $rowid = $question->rowid[$i];
            //$isnew = !$rowid;
            $row = (object) [
                'id' => $rowid,
                'matrixid' => $matrixid,
                'shorttext' => $question->rows_shorttext[$i],
                'description' => $question->rows_description[$i],
                'feedback' => $question->rows_feedback[$i]
            ];

            $delete = empty($row->shorttext);

            if ($delete && $isnew) {
                // Noop?
                continue;
            } else if ($delete) {
                $store->delete_matrix_row($row);
            } else if ($isnew || $makecopy) {
                $store->insert_matrix_row($row);
                $rowids[] = $row->id;
            } else {
                $store->update_matrix_row($row);
                $rowids[] = $row->id;
            }
        }

        // Cols.
        // Mapping for indexes to db ids.
        $colids = [];
        foreach ($question->cols_shorttext as $i => $short) {
            $colid = $question->colid[$i];
            //$isnew = !$colid;
            $col = (object) [
                'id' => $colid,
                'matrixid' => $matrixid,
                'shorttext' => $question->cols_shorttext[$i],
                'description' => $question->cols_description[$i]
            ];

            $delete = empty($col->shorttext);

            if ($delete && $isnew) {
                // Noop?
                continue;
            } else if ($delete) {
                $store->delete_matrix_col($col);
            } else if ($isnew || $makecopy) {
                $store->insert_matrix_col($col);
                $colids[] = $col->id;
            } else {
                $store->update_matrix_col($col);
                $colids[] = $question->colid[$i];
            }
        }

        // Weights.
        // First we delete all weights. (There is no danger of deleting the original weights when making a copy,
        // because we are anyway deleting only weights associated with our newly created question ID).
        // Then we recreate them. (Because updating is too much of a pain).
        $store->delete_matrix_weights($questionid);

        // When we switch from multiple answers to single answers (or the other
        // way around) we loose answers.
        //
        // To avoid loosing information when we switch, we test if the weight matrix is empty.
        // If the weight matrix is empty we try to read from the other
        // representation directly from POST data.
        //
        // We read from the POST because post data are not read into the question
        // object because there is no corresponding field.
        //
        // This is bit hacky but it is safe. The to_weight_matrix returns only
        // 0 or 1.
        if ($question->multiple) {
            $weights = $this->to_weigth_matrix($question, true);
            if ($this->is_matrix_empty($weights)) {
                $weights = $this->to_weigth_matrix((object) $_POST, false); // Todo: remove unsafe $_POST.
            }
        } else {
            $weights = $this->to_weigth_matrix($question, false);
            if ($this->is_matrix_empty($weights)) {
                $weights = $this->to_weigth_matrix((object) $_POST, true); // Todo: remove unsafe $_POST.
            }
        }

        foreach ($rowids as $rowindex => $rowid) {
            foreach ($colids as $colindex => $colid) {
                $value = $weights[$rowindex][$colindex];
                if ($value) {
                    $weight = (object) [
                        'rowid' => $rowid,
                        'colid' => $colid,
                        'weight' => 1
                    ];
                    $store->insert_matrix_weight($weight);
                }
            }
        }

        $transaction->allow_commit();
        return (object) [];
    }

    /**
     * Transform the weight from the edit-form's representation to a standard matrix
     * representation
     *
     * Input data is either
     *
     *      $question->{cell0_1] = 1
     *
     * or
     *
     *      $question->{cell0] = 3
     *
     * Output
     *
     *      [ 1 0 1 0 ]
     *      [ 0 0 0 1 ]
     *      [ 1 1 1 0 ]
     *      [ 0 1 0 1 ]
     *
     *
     * @param object  $data         Question's data, either from the question object or from the post
     * @param boolean $frommultiple Whether we extract from multiple representation or not
     * @result array                    The weights
     */
    public function to_weigth_matrix(object $data, bool $frommultiple): array {
        $result = [];
        $rowcount = 20;
        $colcount = 20;

        // Init.
        for ($row = 0; $row < $rowcount; $row++) {
            for ($col = 0; $col < $colcount; $col++) {
                $result[$row][$col] = 0;
            }
        }

        if ($frommultiple) {
            for ($row = 0; $row < $rowcount; $row++) {
                for ($col = 0; $col < $colcount; $col++) {
                    $key = qtype_matrix_grading::cell_name($row, $col, true);
                    $value = $data->{$key} ?? 0;
                    $result[$row][$col] = $value ? 1 : 0;
                }
            }
        } else {
            for ($row = 0; $row < $rowcount; $row++) {
                $key = qtype_matrix_grading::cell_name($row, 0, false);
                if (isset($data->{$key})) {
                    $col = $data->{$key};
                    $result[$row][$col] = 1;
                }
            }
        }
        return $result;
    }

    /**
     * True if the matrix is empty (contains only zeroes). False otherwise.
     *
     * @param array $matrix Array of arrays
     * @return boolean True if the matrix contains only zeros. False otherwise
     */
    public function is_matrix_empty(array $matrix): bool {
        foreach ($matrix as $row) {
            foreach ($row as $value) {
                if ($value && $value > 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * This method should be overriden if you want to include a special heading or some other
     * html on a question editing page besides the question editing form.
     *
     * @param question_edit_form $mform     a child of question_edit_form
     * @param object             $question
     * @param string             $wizardnow is '' for first page.
     */
    public function display_question_editing_page($mform, $question, $wizardnow): void {
        global $OUTPUT;
        $heading = $this->get_heading(empty($question->id));

        if (get_string_manager()->string_exists('pluginname_help', $this->plugin_name())) {
            echo $OUTPUT->heading_with_help($heading, 'pluginname', $this->plugin_name());
        } else {
            echo $OUTPUT->heading_with_help($heading, $this->name(), $this->plugin_name());
        }
        $mform->display();
    }

    public function name(): string {
        return 'matrix';
    }

    public function extra_question_fields(): array {
        return ['qtype_matrix', 'usedndui', 'grademethod', 'multiple'];
    }

    /**
     * Cant type this function to many  types returned!
     * import a matrix question from Moodle XML format
     *
     * @param             $data
     * @param             $question
     * @param qformat_xml $format
     * @param null        $extra
     * @return bool|object
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'matrix') {
            return false;
        }

        // Initial.
        $question = $format->import_headers($data);
        $question->qtype = 'matrix';
        $question->options = new stdClass();

        // Use_dnd_ui.
        $question->options->usedndui = $format->trans_single(
            $format->getpath($data, ['#', 'use_dnd_ui', 0, '#'], 0));
        // Todo: check if we translated this corrent!

        // Grademethod.
        $question->grademethod = $format->getpath(
            $data,
            ['#', 'grademethod', 0, '#'],
            self::defaut_grading()->get_name()
        );

        // Shuffleanswers.
        $question->options->shuffleanswers = $format->trans_single($format->getpath(
            $data,
            ['#', 'shuffleanswers', 0, '#'],
            1));

        // Multiple.
        $multiple = $format->trans_single($format->getpath(
            $data,
            ['#', 'multiple', 0, '#'],
            1));

        if ($multiple == 1) {
            $question->multiple = true;
        } else {
            $question->multiple = false;
        }

        // Renderer.
        $question->options->renderer = $format->getpath($data, ['#', 'renderer', 0, '#'], 'matrix');

        // Rows.
        $question->rows = [];
        $question->rows_shorttext = [];
        $question->rows_description = [];
        $question->rows_feedback = [];
        $question->rowid = [];
        $index = 0;
        $rowsxml = $data['#']['row'];

        foreach ($rowsxml as $rowxml) {
            $question->rows_shorttext[$index] = $format->getpath($rowxml, ['#', 'shorttext', 0, '#'], '');

            $question->rows_description[$index] = [
                'text' => $format->getpath($rowxml, ['#', 'description', 0, '#', 'text', 0, '#'], ''),
                'format' => $format->trans_format(
                    $format->getpath($rowxml, ['#', 'description', 0, '@', 'format'], 'html')
                )
            ];

            $question->rows_feedback[$index] = [
                'text' => $format->getpath($rowxml, ['#', 'feedback', 0, '#', 'text', 0, '#'], ''),
                'format' => $format->trans_format(
                    $format->getpath($rowxml, ['#', 'feedback', 0, '@', 'format'], 'html')
                )
            ];
            $question->rowid[$index] = false;
            $index++;
        }

        // Cols.
        $question->cols = [];
        $question->cols_shorttext = [];
        $question->cols_description = [];
        $question->colid = [];
        $index = 0;
        $colsxml = $data['#']['col'];

        foreach ($colsxml as $colxml) {
            $question->cols_shorttext[$index] = $format->getpath($colxml, ['#', 'shorttext', 0, '#'], '');
            $question->cols_description[$index] = [
                'text' => $format->getpath($colxml, ['#', 'description', 0, '#', 'text', 0, '#'], ''),
                'format' => $format->trans_format(
                    $format->getpath($colxml, ['#', 'description', 0, '@', 'format'], 'html')
                )
            ];
            $question->colid[$index] = false;
            $index++;
        }

        // Weights.
        $question->weights = [];
        $weightsofrowsxml = $data['#']['weights-of-row'];
        $rowindex = 0;

        if ($question->multiple) {
            foreach ($weightsofrowsxml as $weightsofrowxml) {
                $colindex = 0;
                foreach ($weightsofrowxml['#']['weight-of-col'] as $weightofcolxml) {
                    $key = qtype_matrix_grading::cell_name($rowindex, $colindex, $question->multiple);
                    $question->{$key} = floatval($weightofcolxml['#']);
                    $colindex++;
                }
                $rowindex++;
            }
        } else {
            foreach ($weightsofrowsxml as $weightsofrowxml) {
                $colindex = 0;
                foreach ($weightsofrowxml['#']['weight-of-col'] as $weightofcolxml) {
                    if (floatval($weightofcolxml['#']) != 0) {
                        $key = qtype_matrix_grading::cell_name($rowindex, $colindex, $question->multiple);
                        $question->{$key} = $colindex;
                    }
                    $colindex++;
                }
                $rowindex++;
            }
        }

        return $question;
    }

    /**
     * export a matrix question to Moodle XML format
     * 2020-06-05
     *
     * @param             $question
     * @param qformat_xml $format
     * @param null        $extra
     * @return string
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null): string {
        $output = '';

        // Use_dnd_ui.
        $output .= "    <use_dnd_ui>" . $question->options->usedndui . "</use_dnd_ui>\n";

        // Rows.
        foreach ($question->options->rows as $rowid => $row) {
            $output .= "<!--row: " . $rowid . "-->\n";
            $output .= "    <row>\n";
            $output .= "        <shorttext>" . $row->shorttext . "</shorttext>\n";
            $output .= "        <description {$format->format($row->description['format'])}>\n";
            $output .= $format->writetext($row->description['text'], 3);
            $output .= "        </description>\n";
            $output .= "        <feedback {$format->format($row->feedback['format'])}>\n";
            $output .= $format->writetext($row->feedback['text'], 3);
            $output .= "        </feedback>\n";
            $output .= "    </row>\n";
        }

        // Cols.
        foreach ($question->options->cols as $colid => $col) {
            $output .= "<!--col: " . $colid . "-->\n";
            $output .= "    <col>\n";
            $output .= "        <shorttext>" . $col->shorttext . "</shorttext>\n";
            $output .= "        <description {$format->format($col->description['format'])}>\n";
            $output .= $format->writetext($col->description['text'], 3);
            $output .= "        </description>\n";
            $output .= "    </col>\n";
        }

        // Weights.
        foreach ($question->options->weights as $rowid => $weightsofrow) {
            $output .= "<!--weights of row: " . $rowid . "-->\n";
            $output .= "    <weights-of-row>\n";
            foreach ($weightsofrow as $colid => $weightofcol) {
                $output .= "<!--weight of col: " . $colid . "-->\n";
                $output .= "    <weight-of-col>" . $weightofcol . "</weight-of-col>\n";
            }
            $output .= "    </weights-of-row>\n";
        }

        // Grademethod.
        $output .= '    <grademethod>' . $question->options->grademethod .
            "</grademethod>\n";

        // Shuffleanswers.
        $output .= '    <shuffleanswers>' . $question->options->shuffleanswers .
            "</shuffleanswers>\n";

        // Multiple.
        $multiple = 1;
        if (!$question->options->multiple) {
            $multiple = 0;
        }
        $output .= '    <multiple>' . $multiple . "</multiple>\n";
        // Renderer.
        $output .= '    <renderer>' . $question->options->renderer . "</renderer>\n";

        return $output;
    }

    /**
     * Initialise the common question_definition fields.
     *
     * @param question_definition $question     the question_definition we are creating.
     * @param object              $questiondata the question data loaded from the database.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata): void {
        parent::initialise_question_instance($question, $questiondata);
        $question->rows = $questiondata->options->rows;
        $question->cols = $questiondata->options->cols;
        $question->weights = $questiondata->options->weights;
        $question->grademethod = $questiondata->options->grademethod;
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->multiple = $questiondata->options->multiple;
    }

    /**
     * (non-PHPdoc).
     * @see question_type::get_possible_responses()
     * @param object $questiondata
     * @return array
     */
    public function get_possible_responses($questiondata) {
        $question = $this->make_question($questiondata);
        $weights = $question->weights;
        $parts = [];

        foreach ($question->rows as $rowid => $row) {

            $choices = [];
            foreach ($question->cols as $columnid => $column) {
                $correctreponse = '';
                $partialcredit = 0;
                /*
                 * Calculate the partial credit
                 * For Analysis of responses needs and due to non-linear math the fraction is set to 1
                 * for Kprime, Kprime1/0 and Difference scoring method. This way we are able to determine correct responses
                 */
                if ($weights[$rowid][$columnid] > 0) {
                    $partialcredit = $question->grademethod == 'all' ? (1 / count($question->rows)) : 1;
                    $correctreponse = ' (' . get_string('correctresponse', 'qtype_matrix') . ')';
                }

                $choices[$columnid] = new question_possible_response(
                    question_utils::to_plain_text($row->shorttext, $row->description['format']) .
                    ': ' . question_utils::to_plain_text($column->shorttext . $correctreponse, $column->description['format']), $partialcredit);
            }

            $choices[null] = question_possible_response::no_response();
            $parts[$rowid] = $choices;
        }

        return $parts;
    }
}
