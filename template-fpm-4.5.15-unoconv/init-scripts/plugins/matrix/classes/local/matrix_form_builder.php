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

namespace qtype_matrix\local;

use coding_exception;
use HTML_QuickForm_element;
use MoodleQuickForm;
use qtype_matrix\local\grading\difference;

/**
 * Helper class to build the form.
 */
class matrix_form_builder {

    /**
     * @var MoodleQuickForm
     */
    private $_form;

    public function __construct(MoodleQuickForm $form) {
        $this->_form = $form;
    }

    /**
     * @param string $name
     * @param string $label
     * @return object
     * @throws coding_exception
     */
    public function create_text(string $name, string $label = ''): object {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->createElement('text', $name, $label);
    }

    /**
     * @param string $name
     * @param string $label
     * @return array
     * @throws coding_exception
     */
    public function create_htmlpopup(string $name, string $label = ''): array {
        static $popcount = 0;
        $popcount++;
        $id = "htmlpopup$popcount";
        $result = [];
        $result[] = $this->create_static(
            '<a class="pbutton input-group-addon" href="#" onclick="mtrx_popup(\'' . $id . '\');return false;" >...</a>'
        );
        $result[] = $this->create_static('<div id="' . $id . '" class="popup">');
        $result[] = $this->create_static('<div>');
        $result[] = $this->create_static(
            '<a class="pbutton close" href="#" onclick="mtrx_popup(\'' . $id . '\');return false;" >&nbsp;&nbsp;&nbsp;</a>'
        );
        $result[] = $this->create_static('<span class="title">');
        $result[] = $this->create_static($label);
        $result[] = $this->create_static('</span>');
        $result[] = $this->create_htmleditor($name);
        $result[] = $this->create_static('</div>');
        $result[] = $this->create_static('</div>');
        return $result;
    }

    public function create_static(string $html): object {
        $name = $this->create_name();
        return $this->_form->createElement('static', $name, null, $html);
    }

    public function create_name(): string {
        static $count = 0;
        return '__j' . $count++;
    }

    /**
     * @param string $name
     * @param string $label
     * @return object
     * @throws coding_exception
     */
    public function create_htmleditor(string $name, string $label = ''): object {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->createElement('editor', $name, $label);
    }

    public function create_hidden(string $name, $value = null): object {
        return $this->_form->createElement('hidden', $name, $value);
    }

    /**
     * @param string|null $name
     * @param string|null $label
     * @param array       $elements
     * @param string      $separator
     * @param bool        $appendname
     * @return object
     * @throws coding_exception
     */
    public function create_group(?string $name = null,
        ?string $label = null,
        array $elements = [],
        string $separator = '',
        bool $appendname = true): object {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->createElement('group', $name, $label, $elements, $separator, $appendname);
    }

    /**
     * @param string $name
     * @param string $label
     * @return object
     * @throws coding_exception
     */
    public function create_header(string $name, string $label = ''): object {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->createElement('header', $name, $label);
    }

    /**
     * @param string $name
     * @param string $label
     * @param array  $attributes
     * @return object
     * @throws coding_exception
     */
    public function create_submit(string $name, string $label = '', array $attributes = []): object {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->createElement('submit', $name, $label, $attributes);
    }

    public function add_javascript(string $js): object {
        $element = $this->create_javascript($js);
        $this->_form->addElement($element);
        return $element;
    }

    public function create_javascript(string $js): object {
        $html = '<script type="text/javascript">';
        $html .= $js;
        $html .= '</script>';
        $name = $this->create_name();
        return $this->_form->createElement('static', $name, null, $html);
    }

    /**
     * @param string $name
     * @param string $label
     * @return HTML_QuickForm_element
     * @throws coding_exception
     */
    public function add_selectyesno(string $name, string $label = ''): HTML_QuickForm_element {
        if ($label === '') {
            $shortname = explode('[', $name);
            $shortname = reset($shortname);
            $label = lang::get($shortname);
        }
        return $this->_form->addElement('advcheckbox', $name, $label);
    }

    public function add_help_button(string $elementname,
        ?string $identifier = null,
        string $component = 'qtype_matrix',
        string $linktext = '',
        bool $suppresscheck = false): void {
        if (is_null($identifier)) {
            $identifier = $elementname;
        }
        $this->_form->addHelpButton($elementname, $identifier, $component, $linktext, $suppresscheck);
    }

    public function set_default(string $name, $value): void {
        $this->_form->setDefault($name, $value);
    }

    public function insert_element_before($element, $beforename): object {
        return $this->_form->insertElementBefore($element, $beforename);
    }

    public function disabled_if($elementname, $dependenton, string $condition = 'notchecked', $value = '1'): void {
        $this->_form->disabledIf($elementname, $dependenton, $condition, $value);
    }

    public function register_no_submit_button(string $name): void {
        $this->_form->registerNoSubmitButton($name);
    }

    public function register_hook_multiple() {
        $this->_form->hideIf('multiple', 'grademethod', 'eq', difference::get_name());
    }
}
