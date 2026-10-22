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
 * Completion Progress block overview table.
 *
 * @package    block_completion_progress
 * @copyright  2016 Michael de Raadt
 * @copyright  2026 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_completion_progress\table;

use core_table\local\filter\filterset;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

/**
 * Overview table filter set.
 */
class overview_filterset extends filterset {
    /**
     * Return the required filters.
     * @return array
     */
    public function get_required_filters(): array {
        return [
            'courseid' => integer_filter::class,
            'blockinstanceid' => integer_filter::class,
        ];
    }

    /**
     * Return the optional filters.
     * @return array
     */
    public function get_optional_filters(): array {
        return [
            'groups' => integer_filter::class,
            'groupings' => integer_filter::class,
            'roles' => integer_filter::class,
        ];
    }
}
