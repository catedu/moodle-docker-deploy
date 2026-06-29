/**
 * TinyMCE gap feedback functionality for gapfill question type.
 *
 * @module qtype_gapfill/tiny_gapfeedback
 */

// Import functions from gaps.js using ES6 import
import {parseQuestionText, getGap, initializeAllGapClasses} from 'qtype_gapfill/gaps';
import {showGapSettingsModal} from 'qtype_gapfill/modal';


/**
 * Handle click events on the select area
 * @param {Event} event - The click event
 */
const handleSelectAreaClick = (event) => {
    // Prevent default behavior to avoid focusing the contenteditable div
    event.preventDefault();
    event.stopPropagation();

    // Use get_gap to check if the click was within a gap
    const gapInfo = getGap(event);

    if (gapInfo) {
        showGapSettingsModal(gapInfo);
    }
};


/**
 * Creates an editable div called tiny_select_area and replaces the TinyMCE instance
 */
const createGapSelectArea = () => {
    /* global tinyMCE */
    const editor = tinyMCE.get('id_questiontext');
    const container = editor.getContainer();
    const tox = container.querySelector('.tox-edit-area');
    // Check if tiny_select_area already exists (toggle back to TinyMCE)
    const existingSelectArea = document.getElementById('tiny_select_area');
    if (existingSelectArea) {
        // Toggle back: remove tiny_select_area and show TinyMCE
        existingSelectArea.remove();
        container.style.display = 'block';
        return;
    }
    // Get the actual editor dimensions from the edit area
    const editorElement = tox.querySelector('.tox-edit-area');
    const editorWidth = editorElement ? editorElement.offsetWidth : container.offsetWidth;
    const editorHeight = editorElement ? editorElement.offsetHeight : container.offsetHeight;
    // Get the text content from TinyMCE
    const questionText = editor.getContent();
    // Process the text with parseQuestionText to wrap gaps in spans
    const processedText = parseQuestionText(questionText);
    // Create the tiny_select_area div
    const selectArea = document.createElement('div');
    selectArea.id = 'tiny_select_area';
    selectArea.className = 'tiny_select_area';
    selectArea.contentEditable = 'false';
    selectArea.innerHTML = processedText;
    // Apply dimensions
    selectArea.style.width = editorWidth + 'px';
    selectArea.style.height = editorHeight + 'px';
    // Insert the tiny_select_area where the TinyMCE instance was
    container.parentNode.insertBefore(selectArea, container.nextSibling);
    container.style.display = 'none';

    // Attach event handler to the select area
    // Use capture phase to ensure we get the event before contenteditable tries to focus
    selectArea.addEventListener('click', (event) => handleSelectAreaClick(event), true);

    // Initialize gap classes for all gaps in the select area
    // We need a small delay to ensure the DOM is fully updated
    setTimeout(() => {
        initializeAllGapClasses();
    }, 100);
};


/**
 * Initialize the gap feedback functionality
 */
export const init = async() => {
    // Wait for the button element to be ready in the DOM
    const button = document.getElementById('id_itemsettings_button');
    if (button) {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            createGapSelectArea();
        });
    }
};
