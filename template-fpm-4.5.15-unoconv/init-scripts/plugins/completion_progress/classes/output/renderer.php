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
 * Completion Progress block renderer.
 *
 * @package    block_completion_progress
 * @copyright  2016 Michael de Raadt
 * @copyright  2020 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_completion_progress\output;

use block_completion_progress\completion_progress;
use block_completion_progress\defaults;
use block_completion_progress\table\overview;
use plugin_renderer_base;
use html_writer;

/**
 * Completion Progress block renderer.
 *
 * @package    block_completion_progress
 * @copyright  2020 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Render a progress bar.
     * @param completion_progress $progress
     * @return string HTML
     */
    public function render_completion_progress(completion_progress $progress) {
        return $this->render_from_template(
            'block_completion_progress/completion_progress',
            $progress->export_for_template($this)
        );
    }

    /**
     * Render the overview table.
     * @param block_completion_progress\table\overview $table
     * @return string
     */
    public function render_overview(overview $table): string {
        ob_start();
        $table->out($table->get_default_per_page(), true);
        return ob_get_clean();
    }
}
