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
 * Completion Progress block.
 *
 * @package    block_completion_progress
 * @copyright  2020 Michael Hawkins <michaelh@moodle.com>
 * @copyright  2026 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_completion_progress\output;

use core\context;
use renderer_base;
use stdClass;

/**
 * Overview table data filter.
 */
class overview_filter extends \core\output\datafilter {
    /** @var stdClass $initialfilters */
    private $initialfilters;

    /**
     * Filter constructor.
     *
     * @param context $context The context where the filters are being rendered
     * @param stdClass $initialfilters The initial filter configuration to pass down into JavaScript.
     * @param string|null $tableregionid Container of the table which will be updated by this filter
     */
    public function __construct(context $context, stdClass $initialfilters, ?string $tableregionid = null) {
        parent::__construct($context, $tableregionid);
        $this->initialfilters = $initialfilters;
    }

    /**
     * Get all valid filter types.
     * @return array
     */
    protected function get_filtertypes(): array {
        $filtertypes = [];

        if ($filtertype = $this->get_roles_filter()) {
            $filtertypes[] = $filtertype;
        }
        if ($filtertype = $this->get_groups_filter()) {
            $filtertypes[] = $filtertype;
        }
        if ($filtertype = $this->get_groupings_filter()) {
            $filtertypes[] = $filtertype;
        }

        return $filtertypes;
    }

    /**
     * Get data for the roles filter.
     * @return stdClass|null
     */
    protected function get_roles_filter(): ?stdClass {
        $roles = get_viewable_roles($this->context, null, ROLENAME_BOTH);
        $usedroles = get_roles_used_in_context($this->context, false);
        $options = array_intersect_key($roles, $usedroles);

        return $this->get_filter_object(
            'roles',
            get_string('roles', 'core_role'),
            false,
            true,
            null,
            array_map(
                fn($id, $title) => (object) [
                    'value' => $id,
                    'title' => $title,
                ],
                array_keys($options),
                array_values($options)
            ),
            joinlist: [self::JOINTYPE_NONE, self::JOINTYPE_ANY]
        );
    }

    /**
     * Get data for the groups filter.
     * @return stdClass|null
     */
    protected function get_groups_filter(): ?stdClass {
        global $USER;

        $options = [];
        $allgroups = groups_get_all_groups(
            $this->course->id,
            has_capability('moodle/site:accessallgroups', $this->context) ? 0 : $USER->id
        );
        foreach ($allgroups as $rec) {
            $options[] = (object) [
                'value' => $rec->id,
                'title' => format_string($rec->name, true, ['context' => $this->context]),
            ];
        }
        if (empty($options)) {
            return null;
        }

        return $this->get_filter_object(
            'groups',
            get_string('groups', 'core_group'),
            false,
            true,
            null,
            $options
        );
    }

    /**
     * Get data for the groupings filter.
     * @return stdClass|null
     */
    protected function get_groupings_filter(): ?stdClass {
        if (!has_capability('moodle/site:accessallgroups', $this->context)) {
            return null;
        }

        $options = [];
        $allgroupings = groups_get_all_groupings($this->course->id);
        foreach ($allgroupings as $rec) {
            $options[] = (object) [
                'value' => $rec->id,
                'title' => format_string($rec->name, true, ['context' => $this->context]),
            ];
        }
        if (empty($options)) {
            return null;
        }

        return $this->get_filter_object(
            'groupings',
            get_string('groupings', 'core_group'),
            false,
            true,
            null,
            $options,
            joinlist: [self::JOINTYPE_ANY]
        );
    }

    /**
     * Export template data.
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output): stdClass {
        return (object) [
            'tableregionid' => $this->tableregionid,
            'courseid' => $this->context->instanceid,
            'initialfilters' => json_encode($this->initialfilters),
            'filtertypes' => $this->get_filtertypes(),
            'rownumber' => 1,
        ];
    }
}
