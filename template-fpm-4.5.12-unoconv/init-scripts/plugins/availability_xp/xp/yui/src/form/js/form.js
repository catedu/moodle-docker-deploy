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
 * @package    availability_xp
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var TEMPLATE = '<div class="availability_xp_frontend"><div>' +
    '<span>{{get_string "levelis" "availability_xp"}}</span>' +
    '<label><span class="accesshide sr-only visually-hidden">{{ get_string "levelconditionoperator" "availability_xp" }}</span>' +
    '<select name="level" class="level-operator custom-select form-select">' +
    '<option value="{{ OPERATOR_GTE }}">{{get_string "opgreaterorequalto" "availability_xp"}}</option>' +
    '<option value="{{ OPERATOR_EQ }}">{{get_string "opequalto" "availability_xp"}}</option>' +
    '</select>' +
    '</label>' +
    ' ' +
    '<label><span class="accesshide sr-only visually-hidden">{{ get_string "levelnumber" "availability_xp" }}</span>' +
    '<select name="level" class="level-number custom-select form-select">' +
    '{{#each levels}}<option value="{{this}}">{{this}}</option>{{/each}}' +
    '</select>' +
    '</label>' +
    '</div></div>';

var OPERATOR_GTE = 0;
var OPERATOR_EQ = 1;

M.availability_xp = M.availability_xp || {}; // eslint-disable-line

M.availability_xp.form = Y.merge(M.core_availability.plugin, {

    levels: null,
    _node: null,

    initInner: function(params) {
        this.levels = params.levels;
    },

    getNode: function(json) {
        var template,
            levelObj = [],
            node,
            operator,
            i;

        if (!this._node) {

            for (i = 1; i <= this.levels; i++) {
                levelObj.push(i);
            }
            template = Y.Handlebars.compile(TEMPLATE);
            this._node = Y.Node.create(template({
                OPERATOR_GTE: OPERATOR_GTE,
                OPERATOR_EQ: OPERATOR_EQ,
                levels: levelObj
            }));

            // When any select chances.
            Y.one('#fitem_id_availabilityconditionsjson, .availability-field').delegate('change', function() {
                M.core_availability.form.update();
            }, '.availability_xp select');
        }

        node = this._node.cloneNode(true);
        if (typeof json.requiredlvl !== 'undefined') {
            // Set the level in the select box, it will not be set if invalid which is what we want.
            node.one('.level-number').set('value', json.requiredlvl);
        }

        // Select relevant operator, it defaults to OPERATOR_GTE.
        operator = typeof json.operator === 'undefined' ? OPERATOR_GTE : json.operator;
        node.one('.level-operator').set('value', operator);

        return node;
    },

    fillValue: function(value, node) {
        var numberselect = node.one('.level-number'),
            operatorselect = node.one('.level-operator'),
            level = numberselect.get('value'),
            operator = operatorselect.get('value');
        value.requiredlvl = level;
        value.operator = operator;
    },

    fillErrors: function(errors, node) {
        var select = node.one('.level-number'),
            level = parseInt(select.get('value'), 10);

        if (isNaN(level) || level < 1 || level > this.levels) {
            errors.push('availability_xp:invalidlevel');
        }
    }
});
