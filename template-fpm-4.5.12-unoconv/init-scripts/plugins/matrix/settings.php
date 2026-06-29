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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('qtype_matrix/show_non_kprime_gui',
        new lang_string('show_non_kprime_gui', 'qtype_matrix'),
        new lang_string('show_non_kprime_gui', 'qtype_matrix'), '0',
        '1', '0'));

    $settings->add(new admin_setting_configcheckbox('qtype_matrix/allow_dnd_ui',
        new lang_string('allow_dnd_ui', 'qtype_matrix'),
        new lang_string('allow_dnd_ui_descr', 'qtype_matrix'), '0',
        '1', '0'));

}
