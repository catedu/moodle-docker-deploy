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
 * Main Javascript module for format_tiles for when user is *NOT* editing.
 * See course_edit for if they are editing.
 * Handles the UI changes when tiles are selected and anything else not
 * covered by the specific modules
 *
 * @module      format_tiles/course
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/templates", "core/ajax", "format_tiles/browser_storage",
        "core/notification", "core/str", "format_tiles/tile_fitter", 'core/fragment'],
    function ($, Templates, ajax, browserStorage, Notification, str, tileFitter, Fragment) {
        "use strict";

        var isMobile;
        var loadingIconHtml;
        var stringStore = [];
        var reopenLastVisitedSection = false;
        var courseId;
        var courseContextId;
        let resizeTimeout;
        var enableCompletion;
        var reorgSectionsDisabledUntil = 0;
        /**
         * If the user has previously expanded a sub-section, its ID will be in expandedSubSectionIds.
         * @type {{}}
         */
        var expandedSubSectionIds = {};

         // Keep a record of which tile is currently open.
        var openTile = 0;

        var Selector = {
            BODY: "body",
            PAGE: "#page",
            TILE: ".tile",
            TILEID: "#tile-",
            MOVEABLE_SECTION: ".moveablesection",
            FILTER_BUTTON: ".filterbutton",
            TILE_LOADING_ICON: ".tile-loading-icon",
            TILE_LOADING_ICON_ID: "#loading-icon-",
            TILE_COLLAPSED: ".tile-collapsed",
            TILE_CLICKABLE: ".tile-clickable",
            TILES: "ul.tiles",
            ACTIVITY: ".activity",
            ACTIVITY_NAME: ".activityname",
            ABOVE_TILES: "#abovetiles",
            FOCUSABLE_ELEMS: 'button, a, input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])',
            INSTANCE_NAME: ".instancename",
            SPACER: ".spacer",
            SECTION_ID: "#section-",
            SECTION_TITLE: ".sectiontitle",
            SECTION_MAIN: ".section.main",
            SECTION_BUTTONS: ".sectionbuttons",
            CLOSE_SEC_BTN: ".closesectionbtn",
            HIDE_SEC0_BTN: ".buttonhidesec0",
            SECTION_ZERO: "#section-0",
            MOODLE_VIDEO: ".mediaplugin.mediaplugin_videojs",
            MOODLE_DIALOGUE: ".moodle-dialogue-confirm", // E.g. glossary entry.
            MANUAL_COMPLETION: '[data-action="toggle-manual-completion"]',
            TOOLTIP: "[data-toggle=tooltip]",
            MATHJAX_EQUATION: ".filter_mathjaxloader_equation"
        };
        var ClassNames = {
            SELECTED: "selected",
            OPEN: "open",
            CLOSED: "closed",
            STATE_VISIBLE: 'state-visible', // This is a Snap theme class. Was added to make this format cooperate better with it.
            HAS_OPEN_TILE: 'format-tiles-tile-open',
            ON_TILE_CONTROL: 'on-tile-control' // Tiles may have controls on them which do not open the section when clicked.
        };

        var Event = {
            CLICK: "click",
            KEYDOWN: "keydown",
        };

        var CSS = {
            DISPLAY: "display",
            Z_INDEX: "z-index",
            HEIGHT: "height",
            BG_COLOUR: "background-color"
        };
        var Keyboard = {
            TAB: 9,
            ENTER: 13
        };

        const OVERLAY_ID = 'format_tiles_overlay';

        /**
         * If we have embedded video in section, stop it.
         * Runs when section is closed.
         * @param {number} section sec number where the video is.
         * @param {number} sectionId sec ID where the video is.
         */
        var stopVideoPlaying = function(section, sectionId) {
            var contentSection = $(Selector.SECTION_ID + section);

            // First iframes (e.g. embedded YouTube).
            contentSection.find("iframe").each(function (index, iframe) {
                iframe = $(iframe);
                // Remove the src from the iframe but keep it in case the section is re-opened.
                if (iframe.attr('src')) {
                    iframe.data('src', iframe.attr("src"));
                    iframe.attr('src', "");
                }
            });

            // Then Moodle media player.
            var mediaPlayers = contentSection.find(Selector.MOODLE_VIDEO);
            if (mediaPlayers.length > 0) {
                contentSection.html("");
                getSectionContentFromServer(courseContextId, sectionId).done(function (html, js) {
                    setCourseContentHTML(contentSection, html, js);
                });
            }
        };

        /**
         * When JS navigation is being used, when a user un-selects a tile, we have to un-fade other tiles
         * @param {number} sectionToFocus if we want to focus a tile after closing, which one
         */
        var cancelTileSelections = function (sectionToFocus) {
            $(Selector.MOVEABLE_SECTION).each(function (index, sec) {
                sec = $(sec);
                if (sec.is(":visible")) {
                    stopVideoPlaying(sec.data("section"), sec.data("sectionid"));
                    sec.slideUp().removeClass(ClassNames.STATE_VISIBLE); // Excludes section 0.
                }
            });
            $(Selector.TILE).removeClass(ClassNames.SELECTED).css(CSS.Z_INDEX, "").css(CSS.BG_COLOUR, "");
            $(".section " + ClassNames.SELECTED).removeClass(ClassNames.SELECTED).css(CSS.Z_INDEX, "");

            if (sectionToFocus !== undefined && sectionToFocus !== 0) {
                $(Selector.TILEID + sectionToFocus).focus();
            }
            $(Selector.TILE_LOADING_ICON).fadeOut(300, function () {
                $(Selector.TILE_LOADING_ICON).html("");
            });
            openTile = 0;
            $(Selector.BODY).removeClass(ClassNames.HAS_OPEN_TILE);
            overlay.fadeOut(300);
            overlay.css('min-height', 'inherit');

            // If any moodle dialogues are open, close them (e.g. glossary auto links).
            $(Selector.MOODLE_DIALOGUE).remove();
        };

        const overlay = $('#' + OVERLAY_ID);
        overlay.attr('aria-hidden', true);

        /**
         * Used where the user clicks the window overlay but we want the active click to be behind the
         * overlay e.g. the tile or custom menu item behind it.  So we get the co-ordinates of the click
         * on the overlay and then repeat the click at that spot ignoring the overlay
         * @param {object} e the click event object
         */
        var clickItemBehind = function (e) {
            var clickedItem = $(e.currentTarget);
            if (clickedItem.attr("id") === OVERLAY_ID) {
                // We need to know what is behind the modal, so hide it for an instant to find out.
                clickedItem.hide();
                var BottomElement = $(document.elementFromPoint(e.clientX, e.clientY));
                clickedItem.show();
                if (BottomElement.hasClass("filterbutton") || BottomElement.hasClass("list-group-item")) {
                    // Must ba a filter button clicked or a nav drawer item.
                    BottomElement.click();
                } else {
                    // Must be a tile clicked.
                    var clickedTile = BottomElement.closest(Selector.TILE);
                    if (clickedTile) {
                        clickedTile.click();
                    }
                }
            }
        };

        /**
         * Set the HTML for a course section to the correct div in the page
         * @param {Object} contentArea the jquery object for the content area
         * @param {String} html the HTML
         * @param {String} js Any additional JS for the new HTML.
         */
        var setCourseContentHTML = function (contentArea, html, js) {
            if (html) {
                // If section content is reloaded following a completion change, server does not know if sub-sections were expanded.
                // We keep a local record of sub-sections which were expanded.
                // When we get HTML from server, we adjust it to re-expand any subsections which were expanded before displaying.
                const newHtml = $(html);
                const subSections = newHtml.find('li.modtype_subsection');
                subSections.each((i) => {
                    const subSection = $(subSections[i]);
                    if (subSection.find('.course-content-item-content.collapse').length) {
                        const subSectionId = subSection.find('a[data-toggle="collapse"]').data('subSectionId');
                        // If the user has previously expanded the section, its ID will be in expandedSubSectionIds.
                        const shouldBeExpanded = expandedSubSectionIds[subSectionId] !== undefined;
                        if (shouldBeExpanded) {
                            // We are manipulating the new HTML before it's added to the DOM so cannot use .collapse('show').
                            subSection.find('a[data-toggle="collapse"]')
                                .removeClass('collapsed').attr('aria-expanded', true);
                            subSection.find('.course-content-item-content')
                                .addClass('show').addClass('collapse').removeClass('collapsing');
                        }
                    }
                });

                contentArea.html(newHtml.html());

                // In the new content area, check for any expand or collapse of sub-sections.
                // Keep a local record of which are expanded.
                contentArea.find('li.modtype_subsection a[data-toggle="collapse"]').on(Event.CLICK, (e) => {
                    const clickedButton = $(e.currentTarget);
                    const subSectionId = clickedButton.data('subSectionId');
                    const subSectionContent = $('#coursecontentcollapse' + subSectionId);
                    const isCollapsed = subSectionContent && subSectionContent.length && !subSectionContent.hasClass('show');
                    if (isCollapsed && expandedSubSectionIds[subSectionId] === undefined) {
                        // Sub-section is being expanded - record that fact locally.
                        expandedSubSectionIds[subSectionId] = true;
                    } else if (!isCollapsed && expandedSubSectionIds[subSectionId] !== undefined) {
                        delete expandedSubSectionIds[subSectionId];
                    }
                });

                $(Selector.TILE_LOADING_ICON).fadeOut(300, function () {
                    $(Selector.TILE_LOADING_ICON).html("");
                });

                if (contentArea.attr("id") !== Selector.SECTION_ZERO) {
                    // Trap the tab key navigation in the content bearing section.
                    // Until the user clicks the close button.
                    // When user reaches last item, send them back to first.
                    // And vice versa if going backwards.

                    const activityLinks = contentArea.find(Selector.ACTIVITY).not(Selector.SPACER).find('a');
                    activityLinks.on(Event.KEYDOWN, function (e) {
                        if (e.keyCode === Keyboard.ENTER) {
                            var toClick = $(e.currentTarget).find("a");
                            window.location.href = toClick.attr("href");
                        }
                    });

                    if (!isMobile) {
                        const focusableElements = contentArea.find(Selector.FOCUSABLE_ELEMS);
                        const firstFocusableElement = focusableElements.eq(0);
                        const lastFocusableElement = focusableElements.eq(focusableElements.length - 1);
                        lastFocusableElement.on(Event.KEYDOWN, function (e) {
                            if (e.keyCode === Keyboard.TAB && !e.shiftKey
                                && $(e.relatedTarget).closest(Selector.SECTION_MAIN).attr("id") !== contentArea.attr("id")) {
                                // RelatedTarget is the item we tabbed to.
                                // If we reached here, the item we are on is not a member of the section we were in.
                                // (I.e. we are trying to tab out of bottom of section) so move tab to first item instead.
                                setTimeout(function () {
                                    // Allow very short delay so we don't skip forward on the basis of our last key press.
                                    firstFocusableElement.focus();
                                    contentArea.find(Selector.SECTION_BUTTONS).css("top", "");
                                }, 100);
                            }
                        });
                        firstFocusableElement.on(Event.KEYDOWN, function (e) {
                            if (e.keyCode === Keyboard.TAB && e.shiftKey
                                && $(e.relatedTarget).closest(Selector.SECTION_MAIN).attr("id") !== contentArea.attr("id")) {
                                // See explanation previous block.
                                // Here we are trying to tab backwards out of the top of our section.
                                // So take us to last item instead.
                                setTimeout(function () {
                                    lastFocusableElement.focus();
                                }, 100);
                            }
                        });
                    }
                }

                if (!isMobile) {
                    // Activate tooltips for completion toggle and any "restricted" items in this content.
                    setTimeout(function () {
                        // Manual forms, auto icons and "Restricted until ..." etc.
                        try {
                            const tooltipItems = contentArea.find(".badge-info");
                            if (tooltipItems.length > 0 && typeof tooltipItems.tooltip == 'function') {
                                tooltipItems.tooltip();
                            }
                        } catch (err) {
                            require(["core/log"], function(log) {
                                log.debug(err);
                            });
                        }
                    }, 500);
                }

                setTimeout(() => {
                    // If subtile title is long, it overlaps background image.
                    // Check heights to see if any subtile backgrounds need dimming.
                    // Allow short delay for content to be added first.
                    const MAX_HEIGHT = 110;
                    contentArea.find(
                        Selector.ACTIVITY_NAME).each((i, el) => {
                        el = $(el);
                        if (el.height() > MAX_HEIGHT) {
                            el.closest(Selector.INSTANCE_NAME).addClass('opaque-bg');
                        }
                    });
                }, 100);
            }
            setTimeout(() => {
                if (js) {
                    // User may be opening same section multiple times so avoid adding same script again.
                    const head = $('head');
                    const existingScripts = head.find('script').filter(
                        (index, script) => {
                            return $(script).html() === js;
                        }
                    );
                    if (existingScripts.length === 0) {
                        Templates.runTemplateJS(js);
                    }
                }

                applyMathJax(contentArea);

                const moodleVideos = contentArea.find(Selector.MOODLE_VIDEO);
                if (moodleVideos.length > 0) {
                    // This already happens once on page load, but we repeat since reloaded HTML containing lazy load videos.
                    require(["media_videojs/loader"], function (videoJS) {
                        videoJS.setUp();
                    });

                    // Issue 87 - If video fullscreen button is pressed, temporarily disable tile re-orgs on screen resize.
                    const fsEvents = ['fullscreenchange', 'webkitfullscreenchang', 'mozfullscreenchange', 'msfullscreenchange'];
                    fsEvents.forEach(function (ev) {
                        document.addEventListener(ev, function () {
                            const disableDurationMilliSeconds = 1000;
                            reorgSectionsDisabledUntil = Date.now() + disableDurationMilliSeconds;
                        });
                    });
                }

                // Issue 123 workaround.
                // If (as yet) hidden modals are contained in added markup, move to body (parent li.section has low z-index).
                contentArea.find('.modal.fade').appendTo('body');
            }, 1000);

            $(document).trigger('format-tiles-section-content-changed', {
                courseId: parseInt(courseId),
                section: contentArea.data('section'),
                sectionid: contentArea.data('sectionid')
            });
        };

        /**
         * Find Mathjax equations in a content area and queue them for processing.
         * @param {Object} contentArea the jquery object for the content area
         */
        const applyMathJax = function(contentArea) {
            if (typeof window.MathJax !== "undefined") {
                try {
                    const mathJaxElems = contentArea.find(Selector.MATHJAX_EQUATION);
                    if (mathJaxElems.length) {
                        mathJaxElems.each((i, node) => {
                            window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, node]);
                        });
                    }
                } catch (err) {
                    require(["core/log"], function (log) {
                        log.debug(err);
                    });
                }
            }
        };

        /**
         * Expand a content containing section (e.g. on tile click)
         * @param {object} contentArea
         * @param {number} sectionNumber to expand
         */
        var expandSection = function (contentArea, sectionNumber) {
            const tile = document.getElementById('tile-' + sectionNumber);

            /**
             * Need to adjust the height of the overlay to ensure it covers full height of expanded section.
             * The element height we need to match varies depending on Moodle version.
             * For Moodle 4.3+ we can use #page.  For Moodle 4.0/4.2 use #topofscroll.
             * Not all themes may have #topofscroll (e.g. Adaptable) so also use fallback.
             * Quickly show content area and close all others to grab full height before reverting that for animation.
             */
            const setOverlayHeight = () => {
                const footerHeight = $('#page-footer').outerHeight() ?? 0;
                $('li.section.state-visible').hide();
                contentArea.show();
                const heights = [
                    $('#page').outerHeight() ?? 0,
                    ($('#topofscroll.main-inner').outerHeight() ?? 0) + ($('#page-header').outerHeight() ?? 0),
                    $('#page-content').outerHeight() ?? 0
                ];
                contentArea.hide();
                overlay.css('min-height', `${Math.ceil(Math.max(...heights)) + footerHeight + 20}px`);
            };

            var expandAndScroll = function () {
                // Scroll to the top of content bearing section
                // We have to wait until possible reOrg and slide down totally before calling this, else co-ords are wrong.
                tile.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // For users with screen readers, move focus to the first item within the tile.
                // Short timeout for this to allow for animation to finish.
                setTimeout(() => {
                    contentArea.find(Selector.FOCUSABLE_ELEMS).eq(0).focus();
                }, 300);

                openTile = sectionNumber;

                // If we have any iframes in the section which were previous emptied out, re-populate.
                // This will happen if we have previously closed a section with videos in, and they were muted.
                const iframes = contentArea.find("iframe");
                if (iframes.length > 0) {
                    iframes.each(function (index, iframe) {
                        iframe = $(iframe);
                        // If iframe has no src, add it from data-src.
                        if (iframe.attr('src') === '' && iframe.data('src') !== undefined) {
                            iframe.attr('src', iframe.data("src"));
                        }
                    });

                    if (enableCompletion) {
                        // Some iframes may load content set to mark as complete on view.
                        // So maybe need to update tile completion info. E.g. applies with H5P filter.
                        setTimeout(() => {
                            $(document).trigger('format-tiles-completion-changed', {
                                courseid: courseId,
                                section: sectionNumber
                            });
                        }, 1000);
                    }
                }
            };

            contentArea.addClass(ClassNames.STATE_VISIBLE);
            setOverlayHeight();
            overlay.fadeIn(300);
            tile.classList.add(ClassNames.SELECTED);
            $(Selector.BODY).addClass(ClassNames.HAS_OPEN_TILE);
            contentArea.slideDown(350, function () {
                // Wait until we have finished sliding down before we work out where the top is for scroll.
                expandAndScroll();
            });
            openTile = sectionNumber;
        };

        /**
         * We find out what section is open, collapse them all, then run the re-org.
         * Finally we re-open the section.
         * This is to ensure that the content bearing section is on the row under the tile clicked.
         * It is run at page load and again if window is re-sized etc.
         * @param {boolean} fitTilesToScreenWidth whether we need to resize the tiles window while tiles are closed.
         * @returns {Promise}
         */
        var reOrgSections = function (fitTilesToScreenWidth) {
            var dfd = new $.Deferred();
            if (reorgSectionsDisabledUntil > Date.now()) {
                dfd.resolve();
            }
            const disableDurationMilliSeconds = 1000;
            reorgSectionsDisabledUntil = Date.now() + disableDurationMilliSeconds;

            var reOrgFunc = function() {
                tileFitter.runReOrg()
                    .done(function(result) {
                        dfd.resolve(result);
                    })
                    .fail(function(result) {
                        dfd.reject(result);
                    });
            };

            if (fitTilesToScreenWidth) {
                tileFitter.resizeTilesDivWidth(courseId).done(() => {
                    // Wait until resize is done to start re-org to allow tiles to settle.
                    reOrgFunc();
                });
            } else {
                reOrgFunc();
            }
            return dfd.promise();
        };

        var failedLoadSectionNotify = function(sectionNum, failResult, contentArea) {
            if (failResult) {
                if (failResult.errorcode === 'servicerequireslogin') {
                    // Moodle may refresh the page here anyway but we do it if not.
                    // Session may have expired and refresh will force new login.
                    window.location.reload();
                } else {
                    // We did get a "failResult" from server.
                    // So it looks like we do have a connection and can notify user this way.
                    Notification.confirm(
                        stringStore.sectionerrortitle,
                        failResult.message,
                        stringStore.continue
                    );
                    require(["core/log"], function(log) {
                        log.debug(failResult);
                    });
                }

                contentArea.html(""); // Clear loading icon.
            } else {
                // It looks like we may not have a connection so we can't launch notifications.
                // We can warn the user like this instead.
                setCourseContentHTML(contentArea, "<p>" + stringStore.noconnectionerror + "</p>", '');
                setTimeout(function () {
                    expandSection(contentArea, sectionNum);
                }, 500);
            }
        };

        /**
         * For a given section, get the content from the server, add it to the store and maybe UI and maybe show it
         * @param {number} courseContextId the id for the affected course context
         * @param {number} sectionId the section ID we are wanting to populate
         * @return {Promise} promise to resolve when the ajax call returns.
         */
        var getSectionContentFromServer = function (courseContextId, sectionId) {
            if (!courseContextId || !sectionId) {
                require(["core/log"], function(log) {
                    log.debug(`No course context ID '${courseContextId.toString()}' or section ID '${sectionId.toString()}'`);
                });
            }
            // This gets the fragment from format_tiles_output_fragment_get_cm_list().
            return Fragment.loadFragment(
                'format_tiles', 'get_cm_list', courseContextId, {sectionid: sectionId}
            );
        };

        /**
         * If the user had section zero collapsed in this course previously, collapse it now
         */
        var setSectionZeroFromUserPref = function () {
            var buttonHideSecZero = $(Selector.HIDE_SEC0_BTN);
            var sectionZero = $(Selector.SECTION_ZERO);
            if (browserStorage.storageEnabledLocal()) {
                // Collapse section zero if user had it collapsed before - relies on local storage so only if enabled.
                if (browserStorage.getSecZeroCollapseStatus() === true) {
                    sectionZero.slideUp(0);
                    buttonHideSecZero.addClass(ClassNames.CLOSED).removeClass(ClassNames.OPEN); // Button image.
                    $(Selector.ABOVE_TILES).addClass('sec-zero-closed');
                } else {
                    sectionZero.slideDown(300);
                    buttonHideSecZero.addClass(ClassNames.OPEN).removeClass(ClassNames.CLOSED); // Button image.
                    $(Selector.ABOVE_TILES).removeClass('sec-zero-closed');
                }
            } else {
                // Storage not available, so we don't know if sec zero was previously collapsed - expand it.
                buttonHideSecZero.addClass(ClassNames.OPEN).removeClass(ClassNames.CLOSED);
                sectionZero.slideDown(300);
                $(Selector.ABOVE_TILES).removeClass('sec-zero-closed');
            }
        };

        /**
         * To be called when a tile is clicked. Get content from server or storage and display or store it.
         * @param {number} courseContextId course context id of this course.
         * @param {number} sectionId section Id for the clicked section.
         * @param {number} sectionNumber the section number of the tile.
         */
        var populateAndExpandSection = function(courseContextId, sectionId, sectionNumber) {
            $(Selector.TILE).removeClass(ClassNames.SELECTED);
            openTile = sectionNumber;
            // Then close all open secs.
            // Timed to finish in 200 so that it completes well before the opening next.
            $(Selector.MOVEABLE_SECTION).each(function (index, sec) {
                sec = $(sec);
                if (sec.is(":visible")) {
                    stopVideoPlaying(sec.data("section"), sec.data("sectionid"));
                    sec.slideUp(200).removeClass(ClassNames.STATE_VISIBLE); // Excludes section 0.
                }
            });
            // Log the fact we viewed the section.
            ajax.call([{
                methodname: "format_tiles_log_tile_click", args: {
                    coursecontextid: courseContextId,
                    sectionnumber: sectionNumber,
                    sectionid: sectionId
                }
            }])[0].fail(Notification.exception);
            // Get the content - use locally stored content first if available.
            var relatedContentArea = $(Selector.SECTION_ID + sectionNumber);
            if (relatedContentArea.find(Selector.ACTIVITY).length > 0) {
                // There is already some content on the screen so display immediately.
                expandSection(relatedContentArea, sectionNumber);

                // Still contact the server in case content has changed (e.g. restrictions now satisfied).
                getSectionContentFromServer(courseContextId, sectionId).done(function (html, js) {
                    setCourseContentHTML(relatedContentArea, html, js);
                }).fail(function (failResult) {
                    failedLoadSectionNotify(sectionNumber, failResult, relatedContentArea);
                    cancelTileSelections(sectionNumber);
                });
            } else {
                relatedContentArea.html(loadingIconHtml);
                // Get from server.
                getSectionContentFromServer(courseContextId, sectionId).done(function (html, js) {
                    setCourseContentHTML(relatedContentArea, html, js);
                    expandSection(relatedContentArea, sectionNumber);
                }).fail(function (failResult) {
                    failedLoadSectionNotify(sectionNumber, failResult, relatedContentArea);
                    cancelTileSelections(sectionNumber);
                });
            }
            browserStorage.setLastVisitedSection(sectionNumber);

            // If any moodle dialogues are open, close them (e.g. glossary auto links).
            $(Selector.MOODLE_DIALOGUE).remove();
        };

        const removeUrlParam = function (pattern) {
            if ((window.location.href).match(pattern)) {
                history.pushState(null, null, (window.location.href).replace(pattern, ''));
            }
        };


        /**
         * Watch the course index and, if a section link is clicked, treat it as a tile click.
         */
        const initCourseIndexWatcher = () => {
            // We have to watch the parent element as the children are not populated on page load.
            const courseIndex = $('nav#courseindex');
            if (courseIndex.length > 0) {
                courseIndex.on('click', (e) => {
                    const target = $(e.target);
                    if (target.hasClass('courseindex-link') && target.data('action') === 'togglecourseindexsection') {
                        const sec = target.closest('.courseindex-section');
                        const sectionNumber = sec.data('number');
                        if (sec && sectionNumber !== undefined) {
                            e.preventDefault();
                            if (sectionNumber === 0) {
                                cancelTileSelections(0);
                            } else {
                                const subSectionParent = sec.parent().closest('.courseindex-section');
                                if (subSectionParent && subSectionParent.length) {
                                    // A subsection has been clicked - need to expand parent, then the subsection.
                                    populateAndExpandSection(
                                        courseContextId, subSectionParent.data('id'), subSectionParent.data('number')
                                    );
                                    // Need to wait for parent section content before we can expand subsection.
                                    // Not ideal to use a timeout - temporary approach pending wider refactor.
                                    setTimeout(() => {
                                        const subSectionContent = $('#coursecontentcollapse' + sec.data('id'));
                                        const needsExpanding = subSectionContent && subSectionContent.length
                                            && !subSectionContent.hasClass('show');
                                        if (needsExpanding) {
                                            subSectionContent.collapse('show');
                                            expandedSubSectionIds[sec.data('id')] = true;
                                        }
                                    }, 1000);
                                } else {
                                    populateAndExpandSection(courseContextId, sec.data('id'), sectionNumber);
                                }
                            }
                        }
                    }
                });
            }
        };

        return {
            init: function (
                courseIdInit,
                useJavascriptNav, // Set by site admin see settings.php.
                isMobileInit,
                sectionNum,
                useFilterButtons, // If > 0 then are using filters (value depends on filter type).
                assumeDataStoreConsent, // Set by site admin see settings.php.
                reopenLastSectionInit, // Set by site admin see settings.php.
                userId,
                fitTilesToWidth,
                enableCompletionInit,
                useSubTiles,
                courseContextIdInit
            ) {
                courseId = parseInt(courseIdInit);
                courseContextId = courseContextIdInit;
                isMobile = isMobileInit;
                // Some args are strings or ints but we prefer bool.  Change to bool now as they are passed on elsewhere.
                reopenLastVisitedSection = reopenLastSectionInit === "1";
                assumeDataStoreConsent = assumeDataStoreConsent === "1";
                enableCompletion = enableCompletionInit === "1";
                 // We want to initialise the browser storage JS module for storing user settings.
                browserStorage.init(
                    courseId,
                    false,
                    sectionNum,
                    assumeDataStoreConsent,
                    userId
                );
                $(document).ready(function () {
                    const isMultiSectionPage = $(Selector.TILES).length === 1;

                    if (useSubTiles) {
                        // We need to be able to style tooltips outside of ul.tiles element.
                        $(Selector.BODY).addClass('format-tiles-subtiles');
                    }
                    var pageContent = $("#page-content");
                    if (pageContent.length === 0) {
                        // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                        pageContent = $("#region-main");
                    }

                    // If we are being told to launch a section number from the URL, use that.
                    if (sectionNum !== 0) {
                        openTile = sectionNum;
                    } else {
                        // Don't use the URL param - check local storage instead.
                        if (reopenLastVisitedSection && browserStorage.storageEnabledLocal) {
                            openTile = browserStorage.getLastVisitedSection();
                            // If user is not on mobile, retrieve last visited section id from browser storage (if present).
                            // And click it.
                        }
                    }

                    // If there is an anchor e.g. #module-123 then open relevant section.
                    const anchorMatches = (window.location.href).match(/#module-(\d+)$/gi);
                    if (anchorMatches && anchorMatches.length) {
                        const anchorCmId = parseInt(anchorMatches[0].split('-')[1]);
                        if (anchorCmId) {
                            // Set openTile to null for now so that null is passed to tile fitter before AJAX below returns.
                            openTile = null;
                            ajax.call([{
                                methodname: "format_tiles_get_course_mod_info", args: {cmid: anchorCmId}
                            }])[0].done(function (data) {
                                if (data && data.courseid === courseId) {
                                    openTile = data.sectionnumber;
                                    if (useJavascriptNav) {
                                        populateAndExpandSection(
                                            data.coursecontextid, data.sectionid, data.sectionnumber
                                        );
                                    }
                                }
                            });
                        }
                    }

                    if (openTile !== 0) {
                        tileFitter.init(courseId, openTile, fitTilesToWidth, false);
                    } else {
                        tileFitter.init(courseId, null, fitTilesToWidth, false);
                    }

                    // We are going to watch for changes to size of main tiles window.
                    // This allows us to call the tile fitter to re-org tiles if needed.
                    const pageContentElem = $('#page-content');
                    // In case some themes don't have a page content div, use window as alternative.
                    const widthObservedElement = pageContentElem.length ? pageContentElem : $(window);
                    var observedElementWidth = widthObservedElement.outerWidth();
                    const page = $('#page');

                    if (useJavascriptNav) {
                        // User is not editing but is usingJS nav to view.

                         // On a tile click, decide what to do an do it.
                         // (Collapse if already expanded, or expand it and fill with content).
                        pageContent.on(Event.CLICK, Selector.TILE_CLICKABLE, function (e) {
                            // Prevent the link being followed to reload the PHP page as we are using JS instead.
                            if (!useJavascriptNav) {
                                return;
                            }
                            const target = $(e.target);
                            if (target.hasClass(ClassNames.ON_TILE_CONTROL)) {
                                // The user has clicked a control on the tile so we don't expand it.
                                return;
                            }
                            e.preventDefault();
                            // If other tiles have loading icons, fade them out (on the tile not the content sec).
                            $(Selector.TILE_LOADING_ICON).fadeOut(300, function () {
                                $(Selector.TILE_LOADING_ICON).html();
                            });
                            var thisTile = $(e.currentTarget).closest(Selector.TILE);
                            var dataSection = parseInt(thisTile.data("section"));
                            if (thisTile.hasClass(ClassNames.SELECTED)) {
                                // This tile is already expanded so collapse it.
                                cancelTileSelections(dataSection);
                                browserStorage.setLastVisitedSection(0);
                                overlay.fadeOut(300);
                                overlay.css('min-height', 'inherit');
                            } else {
                                populateAndExpandSection(courseContextId, thisTile.data('true-sectionid'), dataSection);
                            }
                        });

                        overlay.on(Event.CLICK, function(e) {
                            cancelTileSelections(0);
                            browserStorage.setLastVisitedSection(0);
                            clickItemBehind(e);
                        });

                        // When window is re-sized, content sections under the tiles may be in wrong place.
                        // So remove them and re-initialise them.
                        // Collapse the selected section before doing this.
                        // Otherwise the re-organisation won't work as the tiles' flow will be out when they are analysed.
                        // We use the multi_section_tiles element to capture left and right drawer opening/closing.
                        const resizeObserver = new ResizeObserver(() => {
                            // On iOS resize events are triggered often on scroll because the address bar hides itself.
                            // Avoid this using observedElementWidth here.
                            if (observedElementWidth === widthObservedElement.outerWidth()) {
                                return;
                            }

                            // We wait for a short time before doing anything, as user may still be dragging window size change.
                            // We don't want to react to say 20 resize events happening over a single drag.
                            clearTimeout(resizeTimeout);
                            resizeTimeout = setTimeout(function() {

                                if (reorgSectionsDisabledUntil > Date.now()) {
                                    // We wait until inside our timeout for this as we may be responding to a fullscreen event.
                                    return;
                                }

                                // First assume that we are going to resize, but we have checks to make below.
                                var resizeRequired = true;

                                // If we have an iframe in the section in fullscreen, ignore this resize event.
                                // It was probably caused when user pressed the full screen button.
                                // This could be a Moodle media player div, or a YouTube embed or other.
                                var openContentSection = $(".moveablesection:visible");
                                if (openContentSection.length !== 0) {
                                    var iframes = openContentSection.find("iframe");
                                    if (iframes.length !== 0) {
                                        iframes.each(function (index, player) {
                                            player = $(player);
                                            if (player.outerWidth() > openContentSection.outerWidth()) {
                                                // Video is present and playing full screen so don't react to resize event.
                                                resizeRequired = false;
                                            }
                                        });
                                    }
                                }
                                if (resizeRequired) {
                                    // Set global for comparison next time.
                                    observedElementWidth = widthObservedElement.outerWidth();
                                    reOrgSections(fitTilesToWidth);
                                }
                            }, 100);
                        });

                        resizeObserver.observe(document.getElementById('page-content'));

                        // When user clicks to close a section using cross at top right in section.
                        pageContent.on(Event.CLICK, Selector.CLOSE_SEC_BTN, function (e) {
                            const currentSectionNumber = $(e.currentTarget).data("section");
                            cancelTileSelections(currentSectionNumber);
                            // For screen readers, move focus back to tile just closed so they can advance from there.
                            $('#sectionlink-' + currentSectionNumber).focus();
                        });

                        // Most filter button related JS is in filter_buttons.js module which is required below.

                        if (isMultiSectionPage) {
                            // Remove section and cmid URL params if present as we are using JS nav and showing all tiles.
                            removeUrlParam(/(&|\\?)cmid=\d+/gi);
                            removeUrlParam(/(&|\\?)section=\d+/gi);
                        }

                        // Move overlay into #page element.
                        // Enables left and right drawers plus scroll bar remain on top when tile is open.
                        if (page.length) {
                            $(`#${OVERLAY_ID}`).appendTo(page).css('position', 'absolute');
                        }

                        initCourseIndexWatcher();

                    } else if (fitTilesToWidth) {
                        tileFitter.resizeTilesDivWidth(courseId);
                    }

                    // If this event is triggered, user has updated a completion check box.
                    // We need to retrieve section content from server in case availability of items has changed.
                    // Will also be triggered on focus change e.g. user has returned to this tab from a new window.
                    $(document).on('format-tiles-completion-changed', function(e, data) {
                        if (data.courseid && parseInt(courseId) !== parseInt(data.courseid)) {
                            return;
                        }
                        const allSectionNums = $(Selector.TILE).not(Selector.SPACER).map((i, tile) => {
                            return parseInt($(tile).data('section'));
                        }).toArray();
                        // Need to include sec zero as may have completion tracked items.
                        allSectionNums.push(0);

                        // Get the section ID from section number.
                        const contentArea = $(Selector.SECTION_ID + data.section);
                        const sectionId = contentArea.data('sectionid')
                            ?? contentArea.data('section-id');
                        // This gets the fragment from format_tiles_output_fragment_get_cm_list().
                        Fragment.loadFragment(
                            'format_tiles', 'get_cm_list', courseContextId, {sectionid: sectionId}
                        )
                        .done((html, js) => {
                            setCourseContentHTML(contentArea, html, js);
                        })
                        .catch(err => {
                            require(["core/log"], function(log) {
                                log.debug(err);
                            });
                        });

                        ajax.call([
                            {
                                methodname: "format_tiles_get_section_information",
                                args: {
                                    courseid: courseId,
                                    sectionnums: allSectionNums
                                }
                            }
                        ])[0]
                        .done((response) => {
                            require(["format_tiles/completion"], function (completion) {
                                completion.updateSectionsInfo(
                                    response.sections, response.overall.complete, response.overall.outof
                                );
                            });

                        })
                        .catch(err => {
                            require(["core/log"], function(log) {
                                log.debug(err);
                            });
                        });
                    });

                    if (enableCompletion) {
                        // We use pageContent for listener here, as completion button is replaced by core JS when it's clicked.
                        // This is for non-subtiles only.
                        // We wait half a second to enable the completion change to be registered first.
                        pageContent.on(Event.CLICK, Selector.MANUAL_COMPLETION, function(e) {
                            const currentTarget = $(e.currentTarget);
                            const sectionNum = currentTarget.closest(Selector.SECTION_MAIN).data("section");
                            const cmid = currentTarget.data("cmid");
                            require(["format_tiles/completion"], function (completion) {
                                setTimeout(() => {
                                    completion.triggerCompletionChangedEvent(
                                        sectionNum ? parseInt(sectionNum) : 0,
                                        cmid ? parseInt(cmid) : 0
                                    );
                                }, 500);
                            });
                        });
                    }

                    const sectionZero = $(Selector.SECTION_ZERO);

                    // When the user presses the button to collapse or expand Section zero (section at the top of the course).
                    // Button will absent if site admin has disabled sec zero collapse, in which case nothing to do here.
                    const buttonHideSecZero = $(Selector.HIDE_SEC0_BTN);
                    if (buttonHideSecZero.length) {
                        setSectionZeroFromUserPref();
                        $('#page').on(Event.CLICK, Selector.HIDE_SEC0_BTN, function (e) {
                            if (sectionZero.css(CSS.DISPLAY) === "none") {
                                // Sec zero is collapsed so expand it on user click.
                                sectionZero.slideDown(250);
                                $(Selector.ABOVE_TILES).removeClass('sec-zero-closed');
                                $(e.currentTarget).addClass(ClassNames.OPEN).removeClass(ClassNames.CLOSED);
                                browserStorage.setSecZeroCollapseStatus("collapsed");
                            } else {
                                // Sec zero is expanded so collapse it on user click.
                                sectionZero.slideUp(250);
                                $(Selector.ABOVE_TILES).addClass('sec-zero-closed');
                                $(e.currentTarget).addClass(ClassNames.CLOSED).removeClass(ClassNames.OPEN);
                                browserStorage.setSecZeroCollapseStatus("expanded");
                            }
                        });
                    }

                    if (useFilterButtons) {
                        require(["format_tiles/filter_buttons"], function (filterButtons) {
                            filterButtons.init(courseId, browserStorage.storageEnabledLocal);
                        });
                        if (useJavascriptNav) {
                            pageContent.on(Event.CLICK, Selector.FILTER_BUTTON, function () {
                                cancelTileSelections(0);
                                reOrgSections(false);
                            });
                        }

                    }
                    // If theme is displaying the .tiles_coursenav class items, show items with this class.
                    // They will be hidden otherwise.
                    // They are hidden when initially rendered from PHP as we only want them shown if browser supports JS.
                    // See lib.php extend_course_navigation.
                    $(".tiles_coursenav").removeClass("hidden");

                    // Render the loading icon and store its HTML globally so that we can use it where needed later.
                    Templates.render("format_tiles/loading", {}).done(function (html) {
                        loadingIconHtml = html;
                    });

                     // Get these strings now, in case we need them.
                    // E.g. after we lose connection and cannot display content on a user tile click.
                    var stringKeys = [
                        {key: "sectionerrortitle", component: "format_tiles"},
                        {key: "refresh"},
                        {key: "cancel", component: "moodle"},
                        {key: "noconnectionerror", component: "format_tiles"},
                        {key: "show"},
                        {key: "hide"},
                        {key: "other", component: "format_tiles"},
                        {key: "continue"}
                    ];
                    str.get_strings(stringKeys).done(function (s) {
                        s.forEach(function(str, index) {
                            if (str) {
                                stringStore[stringKeys[index].key] = str;
                            } else {
                                stringStore[stringKeys[index].key] = 'Error.';
                                require(["core/log"], function(log) {
                                    log.debug(`Format tiles get_strings error ${index}`);
                                    log.debug(s);
                                });
                            }
                        });
                    })
                    .fail(function(err) {
                        require(["core/log"], function(log) {
                            log.debug(err);
                        });
                    });

                    // When a section is open, fix close/edit buttons to top of screen (else hidden on scroll).
                    let fixButtonsDisabled = false;
                    $(window).scroll(function() {
                        if (!fixButtonsDisabled) {
                            try {
                                if ($(window).scrollTop() >= 300) {
                                    $('.moveablesection.state-visible').each((i, s) => {
                                        s = $(s);
                                        const section = document.getElementById('section-' + s.data('section'));
                                        const sectionRect = section.getBoundingClientRect();
                                        const right = document.body.clientWidth - sectionRect.right + 30;
                                        const sectionButtons = s.find('.sectionbuttons');
                                        const topMargin = page.offset().top;
                                        if (sectionRect.top + topMargin < 0 && sectionRect.bottom - topMargin > 0) {
                                            sectionButtons.addClass('position-fixed');
                                            sectionButtons.css({'top': topMargin + 10, 'right': right});
                                        } else {
                                            sectionButtons.removeClass('position-fixed');
                                            sectionButtons.css({'top': 'unset', 'right': 'unset'});
                                        }
                                    });
                                }
                            } catch (err) {
                                require(["core/log"], function(log) {
                                    log.debug(err);
                                });
                                fixButtonsDisabled = true;
                            }
                        }
                    });

                    // The URL may include a section ID in the form "#sectionid-xx-title" where xx is section ID.
                    // This would be from a section "permalink".
                    // We cannot get that value in PHP so try redirect from here instead.
                    // This is not needed from Moodle 4.4+ as then the section.php URL is used for permalinks.
                    const urlPattern = /.*\/course\/view\.php\?id=([\d]+)#sectionid-([\d+]+)-title/;
                    const urlMatches = window.location.href.match(urlPattern);
                    if (urlMatches && urlMatches.length === 3) {
                        const sectionId = urlMatches[2];
                        const redirectUrl = urlMatches[0].replace(
                            `#sectionid-${sectionId}-title`, `&sectionid=${sectionId}`
                        );
                        window.location.replace(redirectUrl);
                    }
                });
            },
            populateAndExpandSection(courseContextId, sectionId, sectionNumber) {
                populateAndExpandSection(courseContextId, sectionId, sectionNumber);
            }
        };
    }
);