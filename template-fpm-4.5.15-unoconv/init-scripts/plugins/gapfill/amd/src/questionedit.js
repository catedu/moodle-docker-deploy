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
 * JavaScript code for the gapfill question type.
 *
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Module for setting up the quesiton editing page
 *
 * @module     qtype_gapfill/questionedit
 */

/**
 *  Initialize the question edit functionality.
 *  @method init
 *  @returns void
 */
export const init = () => {
  document.getElementById('id_answerdisplay').addEventListener('change', function() {
    // Use const instead of var.
    const selected = this.value;

    if (selected == 'gapfill') {
      document.getElementById('id_fixedgapsize').disabled = false;
      document.getElementById("id_optionsaftertext").disabled = true;
      document.getElementById("id_optionsaftertext").checked = false;
      document.getElementById('id_singleuse').disabled = true;
      document.getElementById('id_singleuse').checked = false;
      document.getElementById('id_disableregex').disabled = false;

    }
    if (selected == 'dragdrop') {
      document.getElementById('id_optionsaftertext').disabled = false;
      document.getElementById('id_singleuse').disabled = false;
      document.getElementById('id_fixedgapsize').disabled = false;
      document.getElementById('id_disableregex').disabled = false;
    }
    if (selected == 'dropdown') {
      document.getElementById('id_fixedgapsize').disabled = true;
      document.getElementById('id_fixedgapsize').checked = false;
      document.getElementById('id_optionsaftertext').disabled = true;
      document.getElementById('id_optionsaftertext').checked = false;
      document.getElementById('id_singleuse').disabled = true;
      document.getElementById('id_singleuse').checked = false;
      document.getElementById('id_disableregex').disabled = true;
      document.getElementById('id_disableregex').checked = false;
    }

  });
};