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
 * Hook callbacks for format tiles.
 *
 * @package   format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_standard_head_html_generation::class,
        'callback' => 'format_tiles\hooks\output\standard_head_html_prepend::callback',
        'priority' => 0,
    ],
    [
        'hook' => \core\hook\output\before_footer_html_generation::class,
        'callback' => 'format_tiles\hooks\output\before_footer_html_generation::callback',
        'priority' => 0,
    ],
];
