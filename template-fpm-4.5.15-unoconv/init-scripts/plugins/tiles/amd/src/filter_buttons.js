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

/* eslint space-before-function-paren: 0 */

/**
 * Javascript Module to handle filter buttons which appear above the tiles if used for format_tiles
 *
 * @module      format_tiles/filter_buttons
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 3.3
 */

define(["jquery"], function ($) {
    "use strict";

    var Selector = {
        FILTER_BUTTON: ".filterbutton",
        TILE: ".tile",
        COLLAPSING: ".tile-collapsing",
        COLLAPSED: ".tile-collapsed",
        SPACER: ".spacer",
        PAGE_CONTENT: "#page-content",
        REGION_MAIN: "#region-main"
    };

    var ClassNames = {
        COLLAPSING: "tile-collapsing",
        COLLAPSED: "tile-collapsed",
        SELECTED: "selected"
    };

    var Module = {
        /**
         * Which filter button does the user already have pressed
         * @param {number} courseId the id of the course
         * @param {boolean} storageEnabled whether the user's device supports local/session storage
         * @returns {string|null} the filter button id or null if none stored
         */
        getPressedFilterButton: function (courseId, storageEnabled) {
            return storageEnabled
                ? localStorage.getItem("mdl-course-" + courseId + "-filter-btn")
                : null;
        },

        setPressedFilterButton: function (courseId, buttonId, storageEnabled) {
            if (storageEnabled) {
                if (buttonId === 0) {
                    localStorage.removeItem("mdl-course-" + courseId + "-filter-btn");
                } else {
                    localStorage.setItem("mdl-course-" + courseId + "-filter-btn", buttonId);
                }
            }
        },

        /**
         * The filter buttons at the top of the course can be used to collapse or expand tiles (animated via CSS)
         * This function and other others below handle that when filter buttons are pressed
         * Tried to use jquery .slideUp() / .slideDown() for this but animation was not smooth, so used this instead
         * @param {Array|number} tileIds the tile IDs to change
         */
        unCollapseTiles: function (tileIds) {
            tileIds.forEach(function(index, tileId) {
                $("#tile-" + tileIds[tileId]).addClass(ClassNames.COLLAPSING).removeClass(ClassNames.COLLAPSED);
            });
            setTimeout(function () {
                $(Selector.COLLAPSING).removeClass(ClassNames.COLLAPSING);
            }, 300);
        },

        /**
         * Collapse all tiles in the course
         */
        collapseAllTiles: function () {
            $(Selector.TILE).not($(Selector.COLLAPSED)).not($(Selector.SPACER)).addClass(ClassNames.COLLAPSING);
            setTimeout(function () {
                $(Selector.COLLAPSING).addClass(ClassNames.COLLAPSED).removeClass(ClassNames.COLLAPSING);
            }, 250);
        },

        /**
         * Un-collapse all tiles in the present course (animated via CSS) on filter button press
         */
        unCollapseAllTiles: function () {
            setTimeout(function () {
                $(Selector.FILTER_BUTTON).removeClass(ClassNames.SELECTED);
                $(Selector.COLLAPSED).addClass(ClassNames.COLLAPSING).removeClass(ClassNames.COLLAPSED);
                setTimeout(function () {
                    $(Selector.COLLAPSING).removeClass(ClassNames.COLLAPSING);
                }, 250);
                $("#filterbutton-all").addClass(ClassNames.SELECTED);
            }, 250);
        }
    };
    return {
        init: function (courseId, storageEnabledLocal) {
            $(document).ready(function () {
                const getSectionsForButtons = function (buttons) {
                    let sections = [];
                    buttons.forEach(button => {
                        button = $(button);
                        if (button.attr('data-sections')) {
                            const dataSections = JSON.parse(button.attr('data-sections'));
                            dataSections.forEach(s => {
                                if (!sections.includes(s)) {
                                    sections.push(s);
                                }
                            });
                        } else {
                            // We don't have a data sections attribute so find out which tiles have filter-{x} classes instead.
                            const newSections = $("li.tile.filter-" + button.attr('data-buttonid')).map(
                                (index, tile) => {
                                    return $(tile).attr('data-section');
                                }
                            ).toArray();
                            newSections.forEach(s => {
                                if (!sections.includes(s)) {
                                    sections.push(s);
                                }
                            });
                        }
                    });
                    return sections;
                };

                // On page load, if a filter button is already pressed according to user's local storage, press it now.
                var buttonAlreadyPressed = Module.getPressedFilterButton(courseId, storageEnabledLocal);
                if (buttonAlreadyPressed) {
                    var pressedButton = $("#filterbutton" + buttonAlreadyPressed);
                    if (!pressedButton) {
                        Module.setPressedFilterButton(courseId, 0);
                    }
                    $(Selector.FILTER_BUTTON).removeClass(ClassNames.SELECTED);
                    const sections = getSectionsForButtons([pressedButton]);
                    if (sections.length) {
                    pressedButton.addClass(ClassNames.SELECTED);
                    Module.collapseAllTiles();
                    setTimeout(function () {
                            Module.unCollapseTiles(sections);
                    }, 250);
                    Module.setPressedFilterButton(courseId, buttonAlreadyPressed, storageEnabledLocal);
                    } else {
                        Module.unCollapseAllTiles();
                    }
                } else {
                    $('#filterbutton-all').addClass(ClassNames.SELECTED);
                }
                var pageContent = $(Selector.PAGE_CONTENT);
                if (pageContent.length === 0) {
                    // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                    pageContent = $(Selector.REGION_MAIN);
                }
                // When a filter button is pressed, mark it as selected and hide/unhide the related tiles.
                // See @param buttonId {integer|string} the ID of the button pressed.
                pageContent.on("click", Selector.FILTER_BUTTON, function (e) {
                    var button = $(e.target);
                    var buttonId = button.attr("data-buttonid");
                    if (buttonId === "all") {
                        // If "All" button is pressed, un-collapse all tiles.
                        Module.collapseAllTiles();
                        setTimeout(function () {
                            Module.unCollapseAllTiles();
                        }, 500);
                        $(Selector.FILTER_BUTTON).removeClass(ClassNames.SELECTED);
                        Module.setPressedFilterButton(courseId, 0, storageEnabledLocal);
                        $("#filterbutton-all").addClass(ClassNames.SELECTED);
                    } else {
                        // A button except 'All' has been pressed, so collapse all tiles then just reveal the ones we want.
                        const pressedButtons =
                            $(Selector.FILTER_BUTTON
                                + '.' + ClassNames.SELECTED + ':not("#filterbutton-all"):not("#filterbutton' + buttonId + '")');
                        const pressedButtonsArr = pressedButtons.toArray();
                        const areUnPressing = button.hasClass(ClassNames.SELECTED);
                        if (!areUnPressing) {
                            // We are not 'un-pressing' an already pressed button, so add it in.
                            pressedButtonsArr.push(button);
                        }
                        $(Selector.FILTER_BUTTON).removeClass(ClassNames.SELECTED);
                        if (!areUnPressing) {
                            button.addClass(ClassNames.SELECTED);
                        } else {
                            button.removeClass(ClassNames.SELECTED);
                        }
                        const sections = getSectionsForButtons(pressedButtonsArr);
                        if (sections.length) {
                            pressedButtons.addClass(ClassNames.SELECTED);
                        Module.collapseAllTiles();
                        setTimeout(function () {
                                Module.unCollapseTiles(sections);
                        }, 250);
                        Module.setPressedFilterButton(courseId, buttonId, storageEnabledLocal);
                        } else {
                            Module.unCollapseAllTiles();
                        }
                    }
                });
            });
        }
    };
});