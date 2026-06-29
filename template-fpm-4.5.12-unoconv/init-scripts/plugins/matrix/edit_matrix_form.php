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

use qtype_matrix\local\grading\difference;
use qtype_matrix\local\lang;
use qtype_matrix\local\matrix_form_builder;
use qtype_matrix\local\qtype_matrix_grading;
use qtype_matrix\local\setting;

defined('MOODLE_INTERNAL') || die;

/**
 * The question type class for the matrix question type.
 *
 */
require_once($CFG->dirroot . '/question/type/edit_question_form.php');

/**
 * matrix editing form definition. For information about the Moodle forms library,
 * which is based on the HTML Quickform PEAR library
 *
 * @see http://docs.moodle.org/en/Development:lib/formslib.php
 */
class qtype_matrix_edit_form extends question_edit_form {

    const PARAM_COLS = 'cols_shorttext';
    const DEFAULT_COLS = 2;
    const PARAM_ADD_COLUMNS = 'add_cols';
    const PARAM_ROWS = 'rows_shorttext';
    const DEFAULT_ROWS = 4;
    const PARAM_ADD_ROWS = 'add_rows';
    const PARAM_GRADE_METHOD = 'grademethod';
    const PARAM_MULTIPLE = 'multiple';
    const DEFAULT_MULTIPLE = false;
    const PARAM_USE_DND_UI = 'usedndui';
    const DEFAULT_USE_DND_UI = false;
    const PARAM_SHUFFLE_ANSERS = 'shuffleanswers';
    const DEFAULT_SHUFFLE_ANSWERS = true;

    /**
     *
     * @var matrix_form_builder
     */
    private $builder = null;

    public function qtype(): string {
        return 'matrix';
    }

    /**
     * @param $mform MoodleQuickForm should be MoodleQuickForm but cant type it due the parent function not implementing it.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition_inner($mform): void {
        $this->builder = new matrix_form_builder($mform);
        $builder = $this->builder;

        $this->question->options = $this->question->options ?? (object) [];

        $this->add_multiple();
        $this->add_grading();

        if (setting::allow_dnd_ui()) {
            $builder->add_selectyesno(self::PARAM_USE_DND_UI, lang::use_dnd_ui());
            $builder->set_default(self::PARAM_USE_DND_UI, self::DEFAULT_USE_DND_UI);
        }

        $mform->addElement('advcheckbox', self::PARAM_SHUFFLE_ANSERS, lang::shuffle_answers(), null, null, [0,
            1]);
        $builder->add_help_button(self::PARAM_SHUFFLE_ANSERS);
        $builder->set_default(self::PARAM_SHUFFLE_ANSERS, self::DEFAULT_SHUFFLE_ANSWERS);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function add_multiple(): void {
        // Multiple allowed.
        $builder = $this->builder;

        if (setting::show_kprime_gui()) {
            $builder->add_selectyesno(self::PARAM_MULTIPLE, lang::multiple_allowed());
            $builder->set_default(self::PARAM_MULTIPLE, self::DEFAULT_MULTIPLE);
            $builder->register_hook_multiple();
        } else {
            $this->_form->addElement('hidden', self::PARAM_MULTIPLE, self::DEFAULT_MULTIPLE);
            $this->_form->setType(self::PARAM_MULTIPLE, PARAM_RAW);
        }
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function add_grading(): void {
        $builder = $this->builder;

        // Grading method.
        $defaultgrading = qtype_matrix::defaut_grading();
        $defaultgradingname = $defaultgrading->get_name();
        $gradings = qtype_matrix::gradings();

        $radioarray = [];

        foreach ($gradings as $grading) {
            $radioarray[] = &$this->_form->createElement('radio',
                self::PARAM_GRADE_METHOD,
                '',
                $grading->get_title(),
                $grading->get_name(),
                '');
        }

        $this->_form->addGroup($radioarray, self::PARAM_GRADE_METHOD, lang::grade_method(), [
            '<br>'], false);
        $this->_form->setDefault(self::PARAM_GRADE_METHOD, $defaultgradingname);
        $builder->add_help_button(self::PARAM_GRADE_METHOD);
    }

    /**
     * Override if you need to setup the form depending on current values.
     * This method is called after definition(), data submission and set_data().
     * All form setup that is dependent on form values should go in here.
     *
     * @return void
     * @throws coding_exception
     */
    public function definition_after_data(): void {
        $builder = $this->builder;

        $this->add_matrix();
        $builder->add_javascript($this->get_javascript());
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function add_matrix(): void {
        $mform = $this->_form;
        $builder = $this->builder;

        $colscount = $this->param_cols();
        $rowscount = $this->param_rows();

        $grademethod = $this->param_grade_method();
        $grading = qtype_matrix::grading($grademethod);
        $multiple = $this->param_multiple();

        $matrix = [];
        $html = '<table class="quedit matrix"><thead><tr>';
        $html .= '<th></th>';
        $matrix[] = $builder->create_static($html);
        for ($col = 0; $col < $colscount; $col++) {
            $matrix[] = $builder->create_static('<th>');
            $matrix[] = $builder->create_static('<div class="input-group">');
            $matrix[] = $builder->create_text("cols_shorttext[$col]", false);

            $popup = $builder->create_htmlpopup("cols_description[$col]", lang::col_description());
            $matrix = array_merge($matrix, $popup);

            $matrix[] = $builder->create_hidden("colid[$col]");
            $matrix[] = $builder->create_static('</div>');
            $matrix[] = $builder->create_static('</th>');
        }

        $matrix[] = $builder->create_static('<th>');
        $matrix[] = $builder->create_static(lang::row_feedback());
        $matrix[] = $builder->create_static('</th>');

        $matrix[] = $builder->create_static('<th>');
        if (setting::show_kprime_gui()) {
            $matrix[] = $builder->create_submit(self::PARAM_ADD_COLUMNS, '+', [
                'class' => 'button-add']);
            $builder->register_no_submit_button(self::PARAM_ADD_COLUMNS);
        }
        $matrix[] = $builder->create_static('</th>');

        $matrix[] = $builder->create_static('</tr></thead><tbody>');

        for ($row = 0; $row < $rowscount; $row++) {
            $matrix[] = $builder->create_static('<tr>');
            $matrix[] = $builder->create_static('<td>');

            $matrix[] = $builder->create_static('<div class="input-group">');

            $matrix[] = $builder->create_text("rows_shorttext[$row]", false);
            $questionpopup = $builder->create_htmlpopup("rows_description[$row]", lang::row_long());
            $matrix = array_merge($matrix, $questionpopup);
            $matrix[] = $builder->create_hidden("rowid[$row]");

            $matrix[] = $builder->create_static('</div>');
            $matrix[] = $builder->create_static('</td>');

            for ($col = 0; $col < $colscount; $col++) {
                $matrix[] = $builder->create_static('<td>');
                $cellcontent = $grading->create_cell_element($mform, $row, $col, $multiple);
                $cellcontent = $cellcontent ? : $builder->create_static('');
                $matrix[] = $cellcontent;
                $matrix[] = $builder->create_static('</td>');
            }

            $matrix[] = $builder->create_static('<td class="feedback">');

            $feedbackpopup = $builder->create_htmlpopup("rows_feedback[$row]", lang::row_feedback());
            $matrix = array_merge($matrix, $feedbackpopup);

            $matrix[] = $builder->create_static('</td>');

            $matrix[] = $builder->create_static('<td></td>');

            $matrix[] = $builder->create_static('</tr>');
        }

        $matrix[] = $builder->create_static('<tr>');
        $matrix[] = $builder->create_static('<td>');
        if (setting::show_kprime_gui()) {
            $matrix[] = $builder->create_submit('add_rows', '+', ['class' => 'button-add']);
            $builder->register_no_submit_button('add_rows');
        }
        $matrix[] = $builder->create_static('</td>');
        for ($col = 0; $col < $colscount; $col++) {
            $matrix[] = $builder->create_static('<td>');
            $matrix[] = $builder->create_static('</td>');
        }
        $matrix[] = $builder->create_static('</tr>');
        $matrix[] = $builder->create_static('</tbody></table>');

        $matrixheader = $builder->create_header('matrixheader');
        $matrixgroup = $builder->create_group('matrix', null, $matrix, '', false);

        if (isset($this->_form->_elementIndex['tagsheader'])) {
            $builder->insert_element_before($matrixheader, 'tagsheader');
            $refreshbutton = $builder->create_submit('refresh_matrix');
            $builder->register_no_submit_button('refresh_matrix');
            $builder->disabled_if('refresh_matrix', self::PARAM_GRADE_METHOD, 'eq', 'none');
            $builder->disabled_if('defaultgrade', self::PARAM_GRADE_METHOD, 'eq', 'none');
            $builder->insert_element_before($refreshbutton, 'tagsheader');
            $builder->insert_element_before($matrixgroup, 'tagsheader');
        } else {
            $this->_form->addElement($matrixheader);
            $refreshbutton = $builder->create_submit('refresh_matrix');
            $builder->register_no_submit_button('refresh_matrix');
            $builder->disabled_if('refresh_matrix', self::PARAM_GRADE_METHOD, 'eq', 'none');
            $builder->disabled_if('defaultgrade', self::PARAM_GRADE_METHOD, 'eq', 'none');
            $this->_form->addElement($refreshbutton);
            $this->_form->addElement($matrixgroup);
        }

        if ($colscount > 1 && (empty($this->question->id) || empty($this->question->options->rows))) {
            $builder->set_default('cols_shorttext[0]', lang::true_());
            $builder->set_default('cols_shorttext[1]', lang::false_());
        }
        $this->_form->setExpanded('matrixheader');
    }

    /**
     * Returns the current number of columns
     *
     * @return int The number of columns
     * @throws coding_exception
     */
    protected function param_cols(): int {
        $result = self::DEFAULT_COLS;
        // Todo: fix direct access to POST! Insecure, no filters in place.
        if (isset($_POST[self::PARAM_COLS])) {
            $result = count($_POST[self::PARAM_COLS]);
        } else if (isset($this->question->options->cols) && count($this->question->options->cols) > 0) {
            $result = count($this->question->options->cols);
        }

        $addcols = $this->param_add_columns();
        if ($addcols) {
            $result++;
        }

        return $result;
    }

    // Elements.

    /**
     * Returns column which is sent by the user, can be used to check if a response is made.
     * True if data exists (!=''). False if not (=='').
     *
     * @return string columns to add
     * @throws coding_exception
     */
    protected function param_add_columns(): string {
        return optional_param(self::PARAM_ADD_COLUMNS, '', PARAM_TEXT);
    }

    /**
     * @return int
     * @throws coding_exception
     */
    protected function param_rows(): int {
        $result = self::DEFAULT_ROWS;
        // Todo: fix direct access to POST! Insecure, no filters in place.
        if (isset($_POST[self::PARAM_ROWS])) {
            $result = count($_POST[self::PARAM_ROWS]);
        } else if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $result = count($this->question->options->rows);
        }

        $addrows = $this->param_add_rows();
        if ($addrows) {
            $result++;
        }
        return $result;
    }

    /**
     * True if the user asked to add a row. False otherwise.
     *
     * @return bool rows to add
     * @throws coding_exception
     */
    protected function param_add_rows(): bool {
        return !empty(optional_param(self::PARAM_ADD_ROWS, '', PARAM_TEXT));
    }

    /**
     * Cant type this function -> to many types used. why is the name returned here?
     *
     * @return array|string|string[] The grade method parameter
     */
    protected function param_grade_method() {
        $data = $this->_form->exportValues();
        return $data[self::PARAM_GRADE_METHOD] ?? qtype_matrix::defaut_grading()->get_name();
    }

    /**
     * Cant type this function -> to many types used.
     *
     * @return mixed Whether the question allows multiple answers
     */
    protected function param_multiple() {
        $data = $this->_form->exportValues();
        if ($this->param_grade_method() == difference::get_name()) {
            $data[self::PARAM_MULTIPLE] = false;
        }
        return $data[self::PARAM_MULTIPLE] ?? self::DEFAULT_MULTIPLE;
    }

    public function get_javascript(): string {
        return "var YY = null;
        window.mtrx_current = false;
        function mtrx_popup(id) {
            var current_id = window.mtrx_current;
            var new_id = '#' + id;
            if(current_id == false) {
                console.log(current_id);
                node = YY.one(new_id);
                node.setStyle('display', 'block');
                window.mtrx_current = new_id;
            } else if(current_id == new_id) {
                console.log(current_id);
                node = YY.one(window.mtrx_current);
                node.setStyle('display', 'none');
                window.mtrx_current = false;
            } else {
                console.log(current_id);
                node = YY.one(current_id);
                node.setStyle('display', 'none');
                node = YY.one(new_id);
                node.setStyle('display', 'block');
                window.mtrx_current = new_id;
            }
        }
        YUI(M.yui.loader).use('node', function(Y) {
            YY = Y;
        });";
    }

    /**
     *
     * @param $question object
     * @return void
     */
    public function set_data($question): void {
        $isnew = empty($question->id);
        if (!$isnew) {
            $options = $question->options;
            $question->multiple = $options->multiple ? '1' : '0';
            $question->grademethod = $options->grademethod;
            $question->shuffleanswers = $options->shuffleanswers ? '1' : '0';
            $question->usedndui = $options->usedndui ? '1' : '0';
            $question->rows_shorttext = [];
            $question->rows_description = [];
            $question->rows_feedback = [];
            $question->rowid = [];
            foreach ($options->rows as $row) {
                $question->rows_shorttext[] = $row->shorttext;
                $question->rows_description[] = $row->description;
                $question->rows_feedback[] = $row->feedback;
                $question->rowid[] = $row->id;
            }

            $question->cols_shorttext = [];
            $question->cols_description = [];
            $question->colid = [];
            foreach ($options->cols as $col) {
                $question->cols_shorttext[] = $col->shorttext;
                $question->cols_description[] = $col->description;
                $question->colid[] = $col->id;
            }

            $rowindex = 0;
            foreach ($options->rows as $row) {
                $colindex = 0;
                foreach ($options->cols as $col) {
                    $cellnamemultipleanswers = qtype_matrix_grading::cell_name($rowindex, $colindex, true);
                    $cellnamesingleanswer = qtype_matrix_grading::cell_name($rowindex, $colindex, false);

                    $weight = $options->weights[$row->id][$col->id];
                    // Todo: check security impact we access and set direct on an object, could be bad.
                    $question->{$cellnamemultipleanswers} = ($weight > 0) ? 'on' : '';
                    $question->{$cellnamesingleanswer} = $colindex;
                    if (!$options->multiple && $weight > 0) {
                        break;
                    }
                    $colindex++;
                }
                $rowindex++;
            }
        }
        /* set data should be called on new questions to set up course id, etc
         * after setting up values for question
         */
        parent::set_data($question);
    }

    /**
     *
     * @param $fromform
     * @param $files
     * @return mixed
     * @throws coding_exception
     */
    public function validation($fromform, $files): array {
        $errors = parent::validation($fromform, $files);
        if (setting::show_kprime_gui()) {
            if ($this->col_count($fromform) < 2) {
                $errors['refresh_matrix'] = lang::must_define_1_by_1();
            }
            if ($this->row_count($fromform) == 0) {
                $errors['refresh_matrix'] = lang::must_define_1_by_1();
            }
        } else {
            if ($this->col_count($fromform) != 2) {
                $errors['refresh_matrix'] = lang::must_define_1_by_1();
            }
            if ($this->row_count($fromform) != 4) {
                $errors['refresh_matrix'] = lang::must_define_1_by_1();
            }
        }
        $grading = qtype_matrix::grading($fromform[self::PARAM_GRADE_METHOD]);
        $gradingerrors = $grading->validation($fromform);
        return array_merge($errors, $gradingerrors);
    }

    protected function col_count(array $data): int {
        return count(array_filter($data['cols_shorttext']));
    }

    protected function row_count(array $data): int {
        return count(array_filter($data['rows_shorttext']));
    }
}
