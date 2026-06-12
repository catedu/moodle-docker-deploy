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

namespace qtype_matrix\output;

use dml_exception;
use html_table;
use html_writer;
use qtype_matrix\local\setting;
use qtype_with_combined_feedback_renderer;
use question_attempt;
use question_display_options;
use question_state;

/**
 * Generates the output for matrix questions.
 */
class renderer extends qtype_with_combined_feedback_renderer {

    /**
     * Generate the display of the formulation part of the question. This is the
     * area that contains the question text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt         $qa      the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     * @throws dml_exception
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options): string {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();

        $table = new html_table();
        $table->attributes['class'] = 'matrix';

        if (setting::allow_dnd_ui() && $question->usedndui) {
            $table->attributes['class'] .= ' uses_dndui';
        }

        $table->head = [];
        $table->head[] = '';

        $order = $question->get_order($qa);

        foreach ($question->cols as $col) {
            $table->head[] = self::matrix_header($col);
        }

        if ($options->correctness) {
            $table->head[] = '';
        }

        if (count($question->rows) == 0) {
            // Todo: this is somehow possible since a preview is not a real attempt and therefore it can update the
            // Question and it will take away the rows and this will trigger an error her so we skip these.
            return "Expired question: This can happen in preview mode.";
        }

        foreach ($order as $rowid) {
            $row = $question->rows[$rowid];
            $rowdata = [];
            $rowdata[] = self::matrix_header($row);
            foreach ($question->cols as $col) {
                $key = $question->key($row, $col);
                $cellname = $qa->get_field_prefix() . $key;

                $isreadonly = $options->readonly;
                $ischecked = $question->response($response, $row, $col);

                if ($question->multiple) {
                    $cell = self::checkbox($cellname, $ischecked, $isreadonly);
                } else {
                    $cell = self::radio($cellname, $col->id, $ischecked, $isreadonly);
                }
                $weight = $question->weight($row, $col);
                if ($options->correctness && ($ischecked || question_state::graded_state_for_fraction($weight)->is_correct())) {
                    $cell .= $this->feedback_image($weight);
                }
                $rowdata[] = $cell;
            }

            if ($options->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $row, $response);
                $feedback = $row->feedback['text'];
                $feedback = strip_tags($feedback) ? format_text($feedback) : '';
                $rowdata[] = $this->feedback_image($rowgrade) . $feedback;
            }
            $table->data[] = $rowdata;
        }
        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, ['class' => 'question_text']);
        $result .= html_writer::table($table);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()),
                ['class' => 'validationerror']);
        }

        return $result;
    }

    public static function matrix_header(?object $header): string {
        if (empty($header)) {
            return "";
        }
        $text = $header->shorttext;

        $description = $header->description['text'];
        if (strip_tags($description)) {
            $description = preg_replace('-^<p>-', '', $description);
            $description = preg_replace('-</p>$-', '', $description);
            $description = '<span class="description" >' . format_text($description) . '</span>';
        } else {
            $description = '';
        }

        return '<span class="title">' . format_text($text) . '</span>' . $description;
    }

    protected static function checkbox(string $name, bool $checked, bool $readonly): string {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';
        return "<input type=\"checkbox\" name=\"$name\" $checked $readonly />";
    }

    protected static function radio(string $name, string $value, bool $checked, bool $readonly): string {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';
        return "<input type=\"radio\" name=\"$name\" value=\"$value\" $checked $readonly />";
    }

}
