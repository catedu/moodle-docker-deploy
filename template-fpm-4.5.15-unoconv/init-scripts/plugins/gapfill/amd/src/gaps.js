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
 * JavaScript code for parsing gapfill question text.
 *
 * @module qtype_gapfill/gaps
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Update JSON settings with new feedback data
 * @param {GapInfo} gapInfo - Object containing gapId and gapText
 * @param {string} correctFeedback - HTML content for correct feedback
 * @param {string} incorrectFeedback - HTML content for incorrect feedback
 * @returns {string} - Updated JSON string
 */
const updateJson = (gapInfo, correctFeedback, incorrectFeedback) => {
  // Get the current JSON data from the hidden field
  const settingsdata = document.querySelector("[name='itemsettings']");
  let parsedSettings = {};

  if (settingsdata && settingsdata.value) {
    try {
      const parsedData = JSON.parse(settingsdata.value);
      // Handle both array and object formats for backward compatibility
      if (Array.isArray(parsedData)) {
        // Convert array to object format for consistency
        parsedSettings = {};
        parsedData.forEach(item => {
          if (item.itemid) {
            parsedSettings[item.itemid] = item;
          }
        });
      } else {
        parsedSettings = parsedData;
      }
    } catch (e) {
      parsedSettings = {};
    }
  }

  let found = false;

  // Get delimiter characters
  const delimitcharsElement = document.getElementById('id_delimitchars');
  const delimitchars = delimitcharsElement ? delimitcharsElement.value : '';

  // Check if we already have settings for this gap ID
  for (const key in parsedSettings) {
    if (parsedSettings[key].itemid === gapInfo.gapId) {
      parsedSettings[key].correctfeedback = correctFeedback;
      parsedSettings[key].incorrectfeedback = incorrectFeedback;
      found = true;
      break;
    }
  }

  // If not found, add new settings
  if (!found) {
    const questionId = document.querySelector("input[name='id']");
    const itemsettings = {
      itemid: gapInfo.gapId,
      questionid: questionId ? questionId.value : "",
      correctfeedback: correctFeedback,
      incorrectfeedback: incorrectFeedback,
      gaptext: stripdelim(gapInfo.gapText, delimitchars)
    };

    // Add to parsedSettings object with a unique key
    // Use the itemid as the key to ensure uniqueness
    parsedSettings[gapInfo.gapId] = itemsettings;
  }

  // Convert to array format for consistency
  const settingsArray = Object.values(parsedSettings);
  return JSON.stringify(settingsArray);
};


/**
 * Strip delimiter characters from gap text
 * @param {string} gapText - The gap text with delimiters
 * @param {string} delimitchars - The delimiter characters (e.g., '[]')
 * @returns {string} - Gap text without delimiters
 */
const stripdelim = (gapText, delimitchars) => {
  if (!gapText || !delimitchars || delimitchars.length < 2) {
    return gapText;
  }

  const leftDelim = delimitchars.charAt(0);
  const rightDelim = delimitchars.charAt(1);
  let gaptextNodelim = gapText;

  // Remove left delimiter if present
  if (gapText.charAt(0) === leftDelim) {
    gaptextNodelim = gapText.substring(1);
  }

  // Remove right delimiter if present
  if (gaptextNodelim.charAt(gaptextNodelim.length - 1) === rightDelim) {
    gaptextNodelim = gaptextNodelim.substring(0, gaptextNodelim.length - 1);
  }

  return gaptextNodelim;
};

/**
 * Escape special regex characters in a string
 * @param {string} str - String to escape
 * @returns {string} - Escaped string
 */
const escapeRegex = (str) => {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
};

/**
 * Get the value from an input element by its ID
 * @param {string} id - Element ID
 * @returns {string|null} - Element value or null if not found
 */
const getElementValue = (id) => {
  const element = document.getElementById(id);
  return element ? element.value : null;
};

/**
 * Read existing itemsettings from the hidden field
 * @param {Object} gapInfo - Object containing gapId and gapText
 * @returns {{correctFeedback: string, incorrectFeedback: string}|null} Feedback object, or null on error/not found.
 */
const getItemSettings = (gapInfo) => {
    const itemSettingsField = document.querySelector("[name='itemsettings']");
    let existingSettings = {};
    if (itemSettingsField && itemSettingsField.value) {
        try {
            const parsedData = JSON.parse(itemSettingsField.value);
            // Handle both array and object formats for backward compatibility
            if (Array.isArray(parsedData)) {
                // Convert array to object format for consistency
                existingSettings = {};
                parsedData.forEach(item => {
                    if (item.itemid) {
                        existingSettings[item.itemid] = item;
                    }
                });
            } else {
                existingSettings = parsedData;
            }
        } catch (e) {
            // If parsing fails, start with empty object
            existingSettings = {};
        }
    }

    const searchId = gapInfo && gapInfo.gapId;
    if (!searchId) {
        return null;
    }

    const innerObjects = Object.values(existingSettings);

    const foundObject = innerObjects.find(
        item => item.itemid === searchId
    );

    if (foundObject) {
        return {
            correctFeedback: foundObject.correctfeedback,
            incorrectFeedback: foundObject.incorrectfeedback
        };
    } else {
        return null;
    }
};

/**
 * Parse question text and wrap gaps in spans with unique IDs
 *
 * The function generates IDs based on two factors:
 * 1. Position in text (first number): id1, id2, id3, etc.
 * 2. Occurrence count of each unique gap content (second number): _0, _1, _2, etc.
 *
 * Example:
 * Input:  "The big [cat] met the small [cat]"
 * Output: "The big <span id=\"id1_0\">[cat]</span> met the small <span id=\"id2_1\">[cat]</span>"
 *
 * Explanation:
 * - First [cat]: id1_0 (position 1, first occurrence of "cat")
 * - Second [cat]: id2_1 (position 2, second occurrence of "cat")
 *
 * @param {string} questionText - Raw question text containing gaps
 * @returns {string} - Processed HTML string with gaps wrapped in spans
 */
const parseQuestionText = (questionText) => {
  const delimitchars = getElementValue('id_delimitchars');

  if (!delimitchars || delimitchars.length !== 2) {
    return questionText;
  }

  const leftDelim = delimitchars.charAt(0);
  const rightDelim = delimitchars.charAt(1);
  let processedText = questionText;
  let gapCounter = 0;
  const gapContentCounts = new Map();

  // Find all occurrences of text within delimiters
  const regex = new RegExp(
    escapeRegex(leftDelim) + '(.*?)' + escapeRegex(rightDelim),
    'g'
  );

  processedText = processedText.replace(regex, (match, gapContent) => {
    gapCounter++;

    // Track occurrence count for this specific gap content
    const currentCount = gapContentCounts.get(gapContent) || 0;
    gapContentCounts.set(gapContent, currentCount + 1);

    // Create ID: position_counter (e.g., id1_0, id2_1)
      const spanId = 'id' + gapCounter + '_' + currentCount;
      const settings = getItemSettings({gapId: spanId}) || {};
      const classes = [];
      if (settings.correctFeedback?.trim()) {
        classes.push('hascorrect');
      }
      if (settings.incorrectFeedback?.trim()) {
        classes.push('hasnocorrect');
      }
      const classAttr = classes.length ? ' class="' + classes.join(' ') + ' item"' : ' class="item"';
      return '<span id="' + spanId + '"' + classAttr + '>' + match + '</span>';
  });

  return processedText;
};

/**
 * Determines if click was within a gap span and extracts gap information
 *
 * @param {Event} clickEvent - The click event to analyze
 * @returns {Object|null} - Object with gapId, gapText, hascorrect, and hasincorrect, or null if not a gap click
 */
const getGap = (clickEvent) => {
  // Get the target element from the click event
  let target = clickEvent.target;

  // Check if the target or any of its parents is a gap span
  let gapSpan = target;
  while (gapSpan && gapSpan.tagName !== 'SPAN') {
    gapSpan = gapSpan.parentNode;
  }

  // If we found a span, check if it has an id attribute starting with 'id'
  if (gapSpan && gapSpan.id && gapSpan.id.startsWith('id')) {
    return {
      gapId: gapSpan.id,
      gapText: gapSpan.textContent || gapSpan.innerText,
    };
  }

  // Not a gap click
  return null;
};

/**
 * Update gap classes based on feedback content
 * @param {Object} gapInfo - Object containing gapId
 * @param {string} correctFeedback - HTML content for correct feedback
 * @param {string} incorrectFeedback - HTML content for incorrect feedback
 */
const updateGapClasses = (gapInfo, correctFeedback, incorrectFeedback) => {
  const gapSpan = document.getElementById(gapInfo.gapId);
  if (!gapSpan) {
    return;
  }

  // Remove existing classes
  gapSpan.classList.remove('hascorrect', 'hasnocorrect');

  // Add classes based on feedback content
  if (correctFeedback && correctFeedback.trim() !== '') {
    gapSpan.classList.add('hascorrect');
  }
  if (incorrectFeedback && incorrectFeedback.trim() !== '') {
    gapSpan.classList.add('hasnocorrect');
  }
};

/**
 * Update gap classes from existing settings
 * @param {Object} gapInfo - Object containing gapId and gapText
 */
const updateGapClassesFromSettings = (gapInfo) => {
  const settings = getItemSettings(gapInfo);
  if (settings) {
    updateGapClasses(gapInfo, settings.correctFeedback, settings.incorrectFeedback);
  }
};

/**
 * Initialize gap classes for all gaps in the document
 */
const initializeAllGapClasses = () => {
  // Get all gap spans in the document
  const gapSpans = document.querySelectorAll('span[id^="id"]');

  if (gapSpans.length === 0) {
    return;
  }

  // Get the current itemsettings
  const itemSettingsField = document.querySelector("[name='itemsettings']");
  if (!itemSettingsField || !itemSettingsField.value) {
    return;
  }

  try {
    const parsedData = JSON.parse(itemSettingsField.value);
    let existingSettings = {};

    // Handle both array and object formats
    if (Array.isArray(parsedData)) {
      parsedData.forEach(item => {
        if (item.itemid) {
          existingSettings[item.itemid] = item;
        }
      });
    } else {
      existingSettings = parsedData;
    }

    // Apply classes to each gap
    gapSpans.forEach(gapSpan => {
      const gapId = gapSpan.id;
      const settings = existingSettings[gapId];

      if (settings) {
        // Remove existing classes
        gapSpan.classList.remove('hascorrect', 'hasincorrect');

        // Add classes based on feedback content
        if (settings.correctfeedback && settings.correctfeedback.trim() !== '') {
          gapSpan.classList.add('hascorrect');
        }
        if (settings.incorrectfeedback && settings.incorrectfeedback.trim() !== '') {
          gapSpan.classList.add('hasincorrect');
        }
      }
    });
  } catch (e) {
    // Debug logging for initialization failures
    // console.debug('Failed to initialize gap classes:', e);
  }
};

export {
  parseQuestionText,
  getGap,
  updateJson,
  getItemSettings,
  stripdelim,
  updateGapClasses,
  updateGapClassesFromSettings,
  initializeAllGapClasses
};
