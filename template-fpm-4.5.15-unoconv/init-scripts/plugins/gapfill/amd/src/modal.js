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
 * JavaScript code for handling the gap settings modal.
 *
 * @module qtype_gapfill/modal
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Log from 'core/log';
import Templates from 'core/templates';

// Import functions from gaps.js that are needed
import {
    updateJson,
    getItemSettings,
    stripdelim,
    updateGapClasses,
    updateGapClassesFromSettings
} from 'qtype_gapfill/gaps';

/**
 * Show gap settings modal for a specific gap
 * @param {Object} gapInfo - Object containing gap information
 * @param {string} gapInfo.gapId - Unique identifier for the gap
 * @param {string} gapInfo.gapText - The text content of the gap
 */
const showGapSettingsModal = async(gapInfo) => {
    // Get language strings
    const correctLabel = M.util.get_string('correct', 'qtype_gapfill');
    const incorrectLabel = M.util.get_string('incorrect', 'qtype_gapfill');

    // Get delimiter characters and strip them from gap text for cleaner display
    const delimitcharsElement = document.getElementById('id_delimitchars');
    const delimitchars = delimitcharsElement ? delimitcharsElement.value : '[]';
    const cleanGapText = stripdelim(gapInfo.gapText, delimitchars);
    const titleString = M.util.get_string('additemsettings', 'qtype_gapfill') + ': ' + cleanGapText;

    // Render the Mustache template with language strings
    const templateContext = {
        correct: correctLabel,
        incorrect: incorrectLabel
    };
    const bodyContent = await Templates.render('qtype_gapfill/gapfeedback_modal', templateContext);

    // Create modal using ModalFactory
    const modal = await ModalSaveCancel.create({
        title: titleString,
        body: bodyContent,
        large: true,
    });

    let correctEditorInstance = null;
    let incorrectEditorInstance = null;

    // Show the modal
    modal.show();

    // Update gap classes based on existing feedback when modal loads
    updateGapClassesFromSettings(gapInfo);

    // After modal is shown, initialize TinyMCE editors for the feedback fields
    modal.getRoot().on(ModalEvents.shown, async() => {
        // Wait a moment for DOM to be ready
        await new Promise(resolve => setTimeout(resolve, 300));

        // Get the TinyMCE instance from the global scope
        /* global tinyMCE */
        // Clean up any existing TinyMCE instances for these elements
        const correctEditor = tinyMCE.get('gapfill-feedback-correct');
        if (correctEditor) {
            correctEditor.remove();
        }
        const incorrectEditor = tinyMCE.get('gapfill-feedback-incorrect');
        if (incorrectEditor) {
            incorrectEditor.remove();
        }

        const feedback = getItemSettings(gapInfo);

        // Initialize TinyMCE for both feedback fields with a single init call
        try {
            await tinyMCE.init({
                selector: '#gapfill-feedback-correct, #gapfill-feedback-incorrect',
                menubar: false,
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link unlink',
                plugins: 'lists link',
                height: 150, // Set height to approximately 4 rows
                setup: (ed) => {
                    ed.on('init', () => {
                        const editorId = ed.id;
                        const content = editorId === 'gapfill-feedback-correct'
                            ? (feedback && feedback.correctFeedback) || ''
                            : (feedback && feedback.incorrectFeedback) || '';
                        if (editorId === 'gapfill-feedback-correct') {
                            correctEditorInstance = ed;
                        } else {
                            incorrectEditorInstance = ed;
                        }
                        ed.setContent(content);
                    });
                }
            });
        } catch (error) {
            Log.debug('Failed to initialize TinyMCE:', error);
        }

        // Set up save event handler after TinyMCE is ready
        modal.getRoot().on(ModalEvents.save, (e) => {
            e.preventDefault();

            const correctFeedback = correctEditorInstance ? correctEditorInstance.getContent() : '';
            const incorrectFeedback = incorrectEditorInstance ? incorrectEditorInstance.getContent() : '';

            // Update the JSON data
            const JSONstr = updateJson(gapInfo, correctFeedback, incorrectFeedback);

            // Save to the hidden field
            const itemSettingsField = document.querySelector("[name='itemsettings']");
            if (itemSettingsField) {
                itemSettingsField.value = JSONstr;
            }

            // Update gap classes based on the new feedback
            updateGapClasses(gapInfo, correctFeedback, incorrectFeedback);

            // Close the modal
            modal.hide();
        });

    });

    // Clean up TinyMCE instances when modal is hidden
    modal.getRoot().on(ModalEvents.hidden, () => {
        if (tinyMCE) {
            const correctEditor = tinyMCE.get('gapfill-feedback-correct');
            if (correctEditor) {
                correctEditor.remove();
            }

            const incorrectEditor = tinyMCE.get('gapfill-feedback-incorrect');
            if (incorrectEditor) {
                incorrectEditor.remove();
            }
        }
    });
};

export {
    showGapSettingsModal
};