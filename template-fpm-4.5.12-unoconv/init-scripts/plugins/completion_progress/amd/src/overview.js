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
 * Completion Progress overview page behaviour.
 *
 * @module     block_completion_progress/overview
 * @copyright  2020 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import Pending from 'core/pending';
import * as PubSub from 'core/pubsub';
import {showAddNote, showSendMessage} from 'core_user/local/participants/bulkactions';

const tablesel = '.table-dynamic[data-table-component="block_completion_progress"]' +
        '[data-table-handler="overview"]';

export const init = (courseid, noteStateNames) => {
    const page = document.getElementById('page');

    // Listen for submissions on the download form and copy the filters in.
    page.addEventListener('submit', function(e) {
        if (!e.target.matches('form.dataformatselector')) {
            return;
        }
        var filtersetel = e.target.elements.filterset || (function() {
            var el = document.createElement('INPUT');
            el.type = 'hidden';
            el.name = 'filterset';
            e.target.appendChild(el);
            return el;
        })();
        filtersetel.value = document.querySelector(tablesel).dataset.tableFilters;
    });

    // Listen for togglegroup checkboxes changing state and enable/disable bulk action buttons.
    PubSub.subscribe('core/checkbox-toggleall:checkboxToggled', function(e) {
        var buttons = document.querySelectorAll('[data-table-component="block_completion_progress"] .bulkactions button');
        buttons.forEach(n => void (n.disabled = !e.anyChecked));
    });

    /**
     * Operate the bulk messaging/note functions.
     * @copyright 2017 Damyon Wiese
     */
    page.addEventListener('click', function(e) {
        let bulkAction;
        const checkboxes = document.querySelectorAll("input[data-togglegroup='overview-table'][data-toggle='slave']:checked," +
            "input[data-togglegroup='overview-table'][data-toggle='target']:checked");
        const noteHelpIcon = document.querySelector('[data-region="state-help-icon"]');
        const ids = [];
        checkboxes.forEach(checkbox => {
            ids.push(checkbox.getAttribute('name').replace('user', ''));
        });

        if (e.target.id === 'sendmessage') {
            bulkAction = showSendMessage(ids);
        } else if (e.target.id === 'addnote') {
            bulkAction = showAddNote(courseid, ids, noteStateNames, noteHelpIcon);
        }
        if (bulkAction) {
            const pendingBulkAction = new Pending('core_user/participants:bulkActionSelected');
            bulkAction.then(modal => {
                pendingBulkAction.resolve();
                return modal;
            }).catch(Notification.exception);
        }
    });
};
