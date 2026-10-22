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

use availability_xp\condition;

/**
 * Data generator.
 *
 * @package    availability_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_xp_generator extends component_generator_base {
    /**
     * Create a restriction.
     *
     * This is a hack to speed up behat testing by generating the restrictions
     * without needing to run steps with JavaScript enabled.
     *
     * @param object|array $data The data.
     */
    public function create_restriction($data = null) {
        global $DB;
        $data = (object) ($data ?: []);

        $modesmap = [
            'gte' => condition::OPERATOR_GTE,
            'eq' => condition::OPERATOR_EQ,
        ];

        if (!isset($data->cmid)) {
            throw new \coding_exception('Missing cmid');
        } else if (!isset($data->mode)) {
            throw new \coding_exception('Missing mode');
        } else if (!isset($modesmap[$data->mode])) {
            throw new \coding_exception('Invalid mode, use one of ' . implode(', ', array_keys($modesmap)));
        } else if (!isset($data->level)) {
            throw new \coding_exception('Missing level');
        }

        $availability = json_encode([
            'op' => '&',
            'showc' => [false],
            'c' => [
                [
                    'type' => 'xp',
                    'requiredlvl' => (int) $data->level,
                    'operator' => $modesmap[$data->mode],
                ],
            ],
        ]);
        $DB->set_field('course_modules', 'availability', $availability, ['id' => $data->cmid]);
    }
}
