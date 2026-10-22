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
global $CFG;

$string['pluginname'] = 'Matrix/Kprime';
if (!property_exists($CFG, 'qtype_matrix_show_non_kprime_gui') || $CFG->qtype_matrix_show_non_kprime_gui !== '0') {
    $string['pluginname'] = 'Matrix/Kprime';
    $string['pluginnamesummary'] = 'In matrix questions various statements regarding a common subject have to be rated correctly. In Kprime questions exactly four such statements have to be correctly rated as “true” or “false”.';
    $string['pluginnameadding'] = 'Adding a Matrix/Kprime question';
    $string['pluginnameediting'] = 'Editing a Matrix/Kprime question';
    $string['pluginname_help'] = '<p>Matrix questions consist of an item stem such as a question or incomplete statement, and multiple answer statements, such as corresponding answers or completions. Students rate these statements as “true” or “false”. Alternatively, custom ratings for the answer statements may be defined.
Kprime questions consist of an item stem and four corresponding answer statements. For each answer statement students have to decide whether it is right or wrong.</p>';
    $string['grademethod_help'] = '<ul>
<li><b>Kprime</b>: The student receives one point, if all responses are correct, half a point if one response is wrong and the rest of responses are correct, and zero points otherwise.</li>
<li><b>Kprime1/0</b>: The student receives one point, if all responses are correct, and zero points otherwise. The scoring methods Kprime and Kprime1/0 should only be used for questions with exactly four answer statements.</li>
<li><b>Subpoints</b>: The student is awarded subpoints for each correct response.</li>
<li><b>Difference</b>: The student receives a point depending on the deviation of their selected answer from a pre-specified value (correct answer). The formula for the deviation scores is: maximum attainable difference value – (student’s answer – correct answer)^2. The deviation score then is transformed to a partial credit score ranging between 0 and 1 where 1 stands for a correct answer.</li>
</ul>';
} else {
    $string['pluginname'] = 'Kprime';
    $string['pluginnamesummary'] = 'In Kprime questions exactly four such statements have to be correctly rated as “true” or “false”.';
    $string['pluginnameadding'] = 'Adding a Kprime question';
    $string['pluginnameediting'] = 'Editing a Kprime question';
    $string['pluginname_help'] = '<p>Kprime questions consist of an item stem and four corresponding answer statements. For each answer statement students have to decide whether it is right or wrong.</p>';
    $string['grademethod_help'] = '<ul>
<li><b>Kprime</b>: The student receives one point, if all responses are correct, half a point if one response is wrong and the rest of responses are correct, and zero points otherwise.</li>
<li><b>Kprime1/0</b>: The student receives one point, if all responses are correct, and zero points otherwise.</li>
<li><b>Subpoints</b>: The student is awarded subpoints for each correct response.</li>
<li><b>Difference</b>: The student receives a point depending on the deviation of their selected answer from a pre-specified value (correct answer). The formula for the deviation scores is: maximum attainable difference value – (student’s answer – correct answer)^2. The deviation score then is transformed to a partial credit score ranging between 0 and 1 where 1 stands for a correct answer.</li>
</ul>';
}


$string['pluginname_link'] = 'question/type/matrix';

// Gradings.
$string['all'] = 'Subpoints';
$string['kany'] = 'Kprime (at least one correct, no wrong answer)  ';
$string['kprime'] = 'Kprime1/0';
$string['difference'] = 'Difference';

// Strings.
$string['true'] = 'True';
$string['false'] = 'False';

// Form.
$string['multipleallowed'] = 'Allow multiple responses per answer statement?';

$string['grademethod'] = 'Scoring method';

$string['rowsheader'] = 'Matrix rows';
$string['rowsheader_desc'] = '<p>Shorttext will be used when it\'s present, with the longer text as a tooltip.<br />Be mindful of how this will be displayed</p>
<p>Students can select multiple or single columns per row, depending on how the question has been configured, and each row receives a grade, defined by one of the grading methods.</p>
<p>The final grade for the question is an average of their grades for each of the rows with the exeption of the Kprime type where all answers have to be correct.</p>';

$string['rows_shorttext'] = 'Answer statement';
$string['rows_description'] = 'Description';
$string['rows_feedback'] = 'Feedback';

$string['colsheader'] = 'Matrix columns';
$string['colsheader_desc'] = '<p>Shorttext will be used when it\'s present, with the longer text as a tooltip.<br />Be mindful of how this will be displayed.</p>
<p>Students can select multiple or single columns per row, depending on how the question has been configured, and each row receives a grade, defined by one of the grading methods.</p>
<p>The final grade for the question is an average of their grades for each of the rows with the exeption of the Kprime type where all answers have to be correct.</p>';

$string['cols_shorttext'] = 'Response';
$string['cols_description'] = 'Description';

$string['refresh_matrix'] = 'Refresh response matrix';

$string['matrixheader'] = 'Response matrix';

$string['mustdefine1by1'] = 'You must define at least one answer statement and two responses';
$string['mustaddupto100'] = 'The sum of all non negative weights in each row must be 100%';
$string['weightednomultiple'] = 'It doesn\'t make sense to choose weighted grading with multiple answers not allowed';
$string['oneanswerperrow'] = 'You must provide an answer for each row';

$string['shuffleanswers'] = 'Shuffle answer statements?';
$string['shuffleanswers_help'] = 'If enabled, the order of the answer statements is randomly shuffled for each attempt, provided that “Shuffle within questions” in the activity settings is also enabled.';
$string['show_non_kprime_gui'] = 'Show graphical user interface for options which are not strictly kprime matrix options (more than four rows, more than two columsn, multiple options).';

$string['allow_dnd_ui'] = 'Allow usage of Drag&Drop UI';
$string['allow_dnd_ui_descr'] = 'If allowed, the teachers will have the possibility to enable the Drag&Drop feature to any Matrix questions';
$string['use_dnd_ui'] = 'Use drag &amp; drop ?';
$string['privacy:metadata'] = 'The Kprime/Matrix Question Type plugin does not store any personal data.';
$string['correctresponse'] = 'Correct Response';

