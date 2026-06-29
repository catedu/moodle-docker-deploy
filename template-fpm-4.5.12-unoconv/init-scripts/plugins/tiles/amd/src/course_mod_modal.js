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
 * Javascript Module to handle rendering of course modules (e.g. resource/PDF, resource/html, page) in modal windows
 *
 * When the user clicks a PDF course module subtile or old style resource
 * if we are using modals for it (e.g. PDF) , create, populate, launch and size the modal
 *
 * @module      format_tiles/course_mod_modal
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since       Moodle 3.3
 */

define(["jquery", "core/modal_factory", "core/config", "core/templates", "core/notification", "core/ajax",
        'core/fragment', "core/modal_events"],
    function ($, modalFactory, config, Templates, Notification, ajax, Fragment, ModalEvents) {
        "use strict";

        var loadingIconHtml;
        const win = $(window);
        var courseId;
        var tilesConfig;
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

        const Selector = {
            modal: ".modal",
            modalDialog: ".modal-dialog",
            modalBody: ".modal-body",
            sectionMain: ".section.main",
            pageContent: "#page",
            regionMain: "#region-main",
            completionState: "#completion-check-",
            cmModal: ".embed_cm_modal",
            moodleMediaPlayer: ".mediaplugin_videojs",
            closeBtn: "button.btn-close",
            ACTIVITY: "li.activity",
            URLACTIVITYPOPUPLINK: ".activity.modtype_url.urlpopup a",
            modalHeader: ".modal-header",
            embedModuleButtons: ".embed-module-buttons",
            iframe: "iframe"
        };

        const CLASS = {
            COMPLETION_ENABLED: "completion-enabled",
            COMPLETION_MANUAL: "completion-manual",
            COMPLETION_AUTO: "completion-auto", // E.g. grade based.
            COMPLETION_VIEW: "completion-view",
            COMPLETION_CHECK_BOX: "completioncheckbox",
            COMPLETION_DROPDOWN: "completion-dropdown"
        };

        const modalMinWidth = function () {
            return Math.min(win.width(), 1100);
        };

        /**
         * Some modals contain videos in iframes or objects, which need to stop playing when dismissed.
         * @param {object} modal the modal which contains the video.
         */
        const stopAllVideosOnDismiss = function(modal) {
            modal.on(ModalEvents.hidden, function() {
                const iframes = modal.find(Selector.iframe);
                const objects = modal.find("object");
                const moodleMediaPlayer = modal.find(Selector.moodleMediaPlayer);

                if (iframes.length || objects.length || moodleMediaPlayer.length) {
                    modal.remove();
                }
            });
        };
        /**
         *
         * @param {number} cmId
         * @param {number} moduleContextId
         * @param {number} sectionNum
         * @param {string} title
         * @param {string} objectType
         * @param {boolean} completionEnabled
         * @param {number} existingCompletionState
         * @param {boolean} isManualCompletion
         * @param {string} descriptionHTML
         * @returns {boolean}
         */
        const launchCmModal = function (
                cmId, moduleContextId, sectionNum, title, objectType,
                completionEnabled, existingCompletionState, isManualCompletion, descriptionHTML
            ) {
            modalFactory.create({
                type: modalFactory.types.DEFAULT,
                title: title,
                body: loadingIconHtml
            }).done(function (modal) {
                modal.setLarge();
                modal.show();
                modal.setScrollable(true);
                const modalRoot = $(modal.root);
                const modalId = "embed_mod_modal_" + cmId;
                // If modal exists from previous launch, remove.
                $(`#${modalId}`).remove();
                modalRoot.attr("id", modalId);
                modalRoot.data("cmid", cmId);
                modalRoot.data("section", sectionNum);
                modalRoot.addClass("embed_cm_modal");

                // If it's a page activity, we simply add the page HTML as the modal body.
                // Otherwise, we set the body by rendering from a template.
                if (objectType === 'page') {
                    modalRoot.addClass('mod_' + objectType);
                    stopAllVideosOnDismiss(modalRoot);
                    Fragment.loadFragment(
                        'format_tiles', `get_cm_content`, moduleContextId, {contextid: moduleContextId}
                    )
                       .done(function(html, js) {
                            modal.setBody(html);
                            Templates.runTemplateJS(js);
                        });
                } else {
                    // Render the modal body and set it to the page.
                    // First a blank template data object.
                    var templateData = {
                        id: cmId,
                        objectType: null,
                        width: "100%",
                        height: Math.round(win.height() - 60), // Embedded object height in modal - make as high as poss.
                        cmid: cmId,
                        tileid: sectionNum,
                        isediting: 0,
                        sesskey: config.sesskey,
                        activityname: title,
                        config: {wwwroot: config.wwwroot},
                        completionstring: '',
                        description: descriptionHTML
                    };

                    var template = null;
                    if (objectType === "html") {
                        templateData.objectType = "text/html";
                        template = 'format_tiles/embed_file_modal_body';
                    } else if (objectType === "pdf") {
                        templateData.objectType = 'application/pdf';
                        // Issue #222 - Safari second page load of PDF.
                        // Safari fails to re-load cached PDF, so for now bust cache by adding ?t={time}.
                        // (Reason seems to be related to 'Accept-Ranges: bytes' in response from readfile_accel()).
                        templateData.cachebustparam = isSafari ? Date.now() : null;
                        template = 'format_tiles/embed_file_modal_body';
                    } else if (objectType === "url") {
                        templateData.objectType = 'url';
                        template = 'format_tiles/embed_url_modal_body';
                    }

                    const redirectModule =
                        ['pdf', 'html'].includes(objectType) ? 'resource' : objectType;
                    if (template !== null) {
                        Templates.render(template, templateData).done(function (html) {
                            modal.setBody(html);
                            modalRoot.find(Selector.modalBody).animate({"min-height": Math.round(win.height() + 20)}, "fast");

                            if (objectType === "html" || objectType === 'url') {
                                // HTML files only - set widths to 100% since they may contain embedded videos etc.
                                modalRoot.find(Selector.modal).animate({"max-width": "100%"}, "fast");
                                modalRoot.find(Selector.modalDialog).animate({"max-width": "100%"}, "fast");
                                modalRoot.find(Selector.modalBody).animate({"max-width": "100%"}, "fast");
                                stopAllVideosOnDismiss(modalRoot);
                                if (objectType === 'url') {
                                    modalRoot.find(Selector.modalBody).addClass("text-center");
                                }
                            } else if (objectType === "pdf") {
                                // Otherwise (e.g. for PDF) we don't need 100% width.
                                modalRoot.find(Selector.modal).animate({"max-width": modalMinWidth()}, "fast");
                                // We do modal-dialog too since Moove theme uses it.
                                modalRoot.find(Selector.modalDialog).animate({"max-width": modalMinWidth()}, "fast");
                            }

                        }).fail(() => {
                            window.location.href = `${config.wwwroot}/mod/${redirectModule}/view.php?id=${cmId}`;
                        });
                    } else {
                        window.location.href = `${config.wwwroot}/mod/${redirectModule}/view.php?id=${cmId}`;
                    }
                }

                // Render the modal header / title and set it to the page.
                var headerTemplateData = {
                    cmid: cmId,
                    activityname: title,
                    tileid: sectionNum,
                    showDownload: objectType === "pdf" ? 1 : 0,
                    showNewWindow: ["pdf", 'url'].includes(objectType) ? 1 : 0,
                    forModal: true,
                    config: {wwwroot: config.wwwroot}
                };
                if (completionEnabled) {
                    headerTemplateData.istrackeduser = 1;
                    headerTemplateData.hascompletion = 1;
                    const oldState = existingCompletionState === 1;

                    // Core completion button template has 'overallcomplete' arg relating to this cm.
                    // See course/templates/completion_manual.mustache.
                    headerTemplateData.overallcomplete = oldState ? 1 : 0;
                    headerTemplateData.overallincomplete = oldState ? 0 : 1;
                    headerTemplateData.completionIsManual = isManualCompletion;
                    if (!headerTemplateData.completionIsManual) {
                        // Auto completion has different vars for core template core_course/completion_automatic.
                        headerTemplateData.statuscomplete = headerTemplateData.overallcomplete;
                        headerTemplateData.statusincomplete = headerTemplateData.overallincomplete;
                    }
                    // Trigger event to check if other items in course have updated availability.
                    if (oldState !== headerTemplateData.completionstate) {
                        require(["format_tiles/completion"], function (completion) {
                            completion.triggerCompletionChangedEvent(parseInt(sectionNum), parseInt(cmId));
                        });
                    }
                }

                Templates.render("format_tiles/embed_module_modal_header_btns", headerTemplateData).done(function (html) {
                    modalRoot.find(Selector.embedModuleButtons).remove();
                    modalRoot.find($('button.close')).remove();
                    modalRoot.find($('button.btn-close')).remove(); // Moodle 4.5+.
                    modalRoot.find(Selector.modalHeader).append(html);
                    modalRoot.find(Selector.closeBtn).detach().appendTo(modalRoot.find(Selector.embedModuleButtons));
                    const toggleCompletionSelector = '[data-action="toggle-manual-completion"]';
                    modalRoot.find(toggleCompletionSelector).on('click', () => {
                        require(["format_tiles/completion"], function (completion) {
                            // In this case, core will handle the request to set the new completion value in the DB.
                            // We wait a moment to allow that to get a head start.
                            // Then we trigger an event which course.js will see and update section content to show new statuses.
                            // Use will not notice this as they are looking at the modal, but it's ready when they dismiss modal.
                            setTimeout(() => {
                                completion.triggerCompletionChangedEvent(
                                    parseInt(modalRoot.data('section')), parseInt(modalRoot.data("cmid"))
                                );
                            }, 300);
                        });
                    });
                }).fail(Notification.exception);

                // Allow a short delay before we resize the modal, and check a few times, as content may be loading.
                setTimeout(() => {
                    modalHeightChangeWatcher(modalRoot, 3, 1000);
                }, 500);

                return true;
            });
            return false;
        };

        /**
         * Resize the modal to account for its content.
         * @param {object} modalRoot
         */
        var resizeModal = function(modalRoot) {
            modalRoot.find(Selector.modal).animate({"max-width": modalMinWidth()}, "fast");

            var MODAL_MARGIN = 70;

            // If the modal contains a Moodle mediaplayer div, remove the max width css rule which Moodle applies.
            // Otherwise video will be 400px max wide.
            var mediaPlayer = $(Selector.moodleMediaPlayer);
            mediaPlayer.find("div").each(function(index, child) {
                $(child).css("max-width", "");
            });
            if (mediaPlayer.length > 0) {
                stopAllVideosOnDismiss(modalRoot);
            }

            // If the activity contains an iframe (e.g. is a page with a YouTube video in it, or H5P), ensure modal is big enough.
            // Do this for every iframe in the course module.
            modalRoot.find(Selector.iframe).each(function (index, iframe) {

                const iframeSelector = $(iframe);

                // Get the modal.
                var modal;
                // Boost calls the modal "modal dialog" so try this first.
                modal = modalRoot.find(Selector.modalDialog);

                // If no luck, try what Clean and Adaptable do instead.
                if (modal.length === 0) {
                    modal = modalRoot.find(Selector.modal);
                }

                // Now check and adjust the width of the modal.
                var iframeWidth = Math.min(iframeSelector.width(), win.width());
                if (iframeWidth > modal.width() - MODAL_MARGIN) {
                    modal.animate(
                        {"max-width": Math.max(iframeWidth + MODAL_MARGIN, modalMinWidth())},
                        "fast"
                    );
                    modalRoot.find(Selector.modal).animate(
                        {"max-width": Math.max(iframeWidth + MODAL_MARGIN, modalMinWidth())},
                        "fast"
                    );
                }

                // Then the height of the modal body.
                var modalBody = modalRoot.find(Selector.modalBody);
                if (iframeSelector.height() > modalBody.height() - MODAL_MARGIN) {
                    iframeSelector.attr('height', modalBody.height() - MODAL_MARGIN);
                }
                stopAllVideosOnDismiss(modalRoot);
            });
        };

        /**
         * Check the modal height to see if the iframe in it is bigger.  If it is, adjust modal height up.
         * Do this a few times so that, if iframe content is loading, we can check after it's loaded.
         * @param {object} modalRoot
         * @param {number} howManyChecks
         * @param {number}duration
         * @param {number} oldHeight
         */
        const modalHeightChangeWatcher = function (modalRoot, howManyChecks, duration, oldHeight = 0) {
            const iframe = modalRoot.find(Selector.modalBody);
            if (iframe) {
                const newHeight = Math.round(iframe.height());
                if (newHeight && newHeight > oldHeight + 10) {
                    resizeModal(modalRoot);
                }
                if (howManyChecks > 0) {
                    setTimeout(() => {
                        modalHeightChangeWatcher(modalRoot, howManyChecks - 1, duration, newHeight);
                    }, duration);
                }
            }
        };

        /**
         * Do we need a modal for this cm?
         * @param {number} cmId course module ID
         * @param {string} url
         * @return boolean
         */
        const modalRequired = function(cmId, url) {
            if (tilesConfig.modalallowedmodnames === undefined) {
                return false;
            }
            if (tilesConfig.modalallowedcmids === undefined) {
                return false;
            }
            if (!(tilesConfig.modalallowedcmids).includes(cmId)) {
                return false;
            }

            return ((tilesConfig.modalallowedmodnames).includes('page') && url.startsWith(`${config.wwwroot}/mod/page/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('url') && url.startsWith(`${config.wwwroot}/mod/url/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('pdf') && url.startsWith(`${config.wwwroot}/mod/resource/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('html')
                    && url.startsWith(`${config.wwwroot}/mod/resource/view.php`));
        };

        return {
            init: function (courseIdInit, isEditing, pageType, launchModalCmid, usingJsNav) {
                courseId = parseInt(courseIdInit);
                $(document).ready(function () {
                    tilesConfig = $('#format-tiles-js-config').data();
                    const courseIndex = $('nav#courseindex');

                    if (['course-view-tiles', 'section-view-tiles', 'course-view-section-tiles'].includes(pageType)) {
                        // We are on a main tiles page, /course/view.php or /course/section.php in Moodle 4.4+.
                        // If any link in the course index on the left is clicked, check if it needs a modal.
                        // If it does, launch the modal instead of following the link.
                        // This isn't ideal but saves plugin re-implementing / maintaining large volume of course index code.
                        // TODO use reactive UI - courseformat/activity:openAnchor in course/format/amd/src/local/courseindex.
                        if (!isEditing && courseIndex.length > 0) {
                            courseIndex.on('click', function(e) {
                                const target = $(e.target);
                                const link = target.hasClass('courseindex-link') ? target : target.find('a.courseindex-link');
                                if (link && link.data('for') === 'cm_name') {
                                    e.preventDefault();
                                    const linkUrl = link.attr('href');
                                    if (linkUrl) {
                                        const cmId = link.closest('li.courseindex-item').data('id');
                                        ajax.call([{
                                            methodname: "format_tiles_get_course_mod_info", args: {cmid: cmId}
                                        }])[0].done(function (data) {
                                            const expandedSection = $(`li#section-${data.sectionnumber}.state-visible`);
                                            if (modalRequired(cmId, linkUrl)) {
                                                if (!data || !data.modalallowed) {
                                                    window.location.href = linkUrl;
                                                    return;
                                                }
                                                if (usingJsNav) {
                                                    if (expandedSection.length === 0) {
                                                        require(["format_tiles/course"], function (course) {
                                                            course.populateAndExpandSection(
                                                                data.coursecontextid, data.sectionid, data.sectionnumber
                                                            );
                                                        });
                                                    }
                                                    launchCmModal(
                                                        cmId,
                                                        data.modulecontextid,
                                                        data.sectionnumber,
                                                        data.name,
                                                        data.modaltype,
                                                        data.completionenabled ? 1 : 0,
                                                        data.iscomplete ? 1 : 0,
                                                        data.ismanualcompletion,
                                                        data.description,
                                                    );
                                                } else {
                                                    const newUrl = config.wwwroot
                                                        + `/course/section.php?id=${data.sectionid}&cmid=${cmId}`;
                                                    const isDifferentSection =
                                                        !window.location.href.endsWith(`id=${data.sectionid}`)
                                                        && !window.location.href.includes(`id=${data.sectionid}&cmid=`);
                                                    if (isDifferentSection) {
                                                        window.location.href = newUrl;
                                                    } else {
                                                        // We are in same section so just launch modal.
                                                        launchCmModal(
                                                            cmId,
                                                            data.modulecontextid,
                                                            data.sectionnumber,
                                                            data.name,
                                                            data.modaltype,
                                                            data.completionenabled ? 1 : 0,
                                                            data.iscomplete ? 1 : 0,
                                                            data.ismanualcompletion,
                                                            data.description,
                                                        );
                                                    }
                                                }
                                            } else {
                                                // Link URL may be anchor e.g. #module-138 if the item is a label.
                                                const isAnchorLink = link.data('anchor') || linkUrl.startsWith('#');
                                                if (!isAnchorLink) {
                                                    window.location.href = linkUrl;
                                                } else {
                                                    if (usingJsNav) {
                                                        if (expandedSection.length === 0) {
                                                            require(["format_tiles/course"], function (course) {
                                                                course.populateAndExpandSection(
                                                                    data.coursecontextid, data.sectionid, data.sectionnumber
                                                                );
                                                            });
                                                        }
                                                    } else {
                                                        window.location.href = config.wwwroot
                                                            + `/course/section.php?id=${data.sectionid}`;
                                                    }
                                                }
                                            }
                                        })
                                        .fail(function() {
                                            window.location.href = linkUrl;
                                        });
                                    }
                                }
                            });
                        }

                        // If we are passing ?cmid=xxx in the URL this suggests we are trying to launch course mod modal.
                        // This would be from clicking a course index link while in another activity.
                        // E.g. from /mod/xxx/view.php for another course module.
                        // This isn't ideal but saves this plugin re-implementing / maintaining large volume of course index code.
                        if (launchModalCmid) {
                            ajax.call([{
                                methodname: "format_tiles_get_course_mod_info", args: {cmid: launchModalCmid}
                            }])[0].done(function (data) {
                                if (data && data.modalallowed && data.courseid === courseId) {
                                    const expandedSection = $(`li#section-${data.sectionnumber}.state-visible`);
                                    if (expandedSection.length === 0) {
                                        if (usingJsNav) {
                                            require(["format_tiles/course"], function (course) {
                                                course.populateAndExpandSection(
                                                    data.coursecontextid, data.sectionid, data.sectionnumber
                                                );
                                            });
                                        }
                                    }

                                    launchCmModal(
                                        launchModalCmid,
                                        data.modulecontextid,
                                        data.sectionnumber,
                                        data.name,
                                        data.modaltype,
                                        data.completionenabled ? 1 : 0,
                                        data.iscomplete ? 1 : 0,
                                        data.ismanualcompletion,
                                        data.description,
                                    );
                                }
                            });
                        }

                        var pageContent = $(Selector.pageContent);
                        if (pageContent.length === 0) {
                            // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                            pageContent = $(Selector.regionMain);
                        }

                        pageContent.on("keydown", `[data-action="launch-tiles-cm-modal"]`, function (e) {
                            const ENTER_KEY = 13;
                            if (e.keyCode === ENTER_KEY) {
                                // User has tabbed to a modal capable activity and pressed enter.
                                // To improve accessibility, do not launch a modal but show them standard activity screen.
                                e.preventDefault();
                                const url = $(e.target).attr('href');
                                if (url) {
                                    window.location.href = url;
                                }
                            }
                        });

                        pageContent.on("click", `[data-action="launch-tiles-cm-modal"]`, function (e) {
                            // If click is on a completion checkbox within activity, ignore here as handled elsewhere.
                            const tgt = $(e.target);
                            const isExcludedControl = tgt.hasClass(CLASS.COMPLETION_CHECK_BOX)
                                || tgt.parent().hasClass(CLASS.COMPLETION_CHECK_BOX)
                                || tgt.hasClass(CLASS.COMPLETION_DROPDOWN)
                                || tgt.parent().hasClass(CLASS.COMPLETION_DROPDOWN)
                                || tgt.is(":button")
                                || tgt.hasClass('expanded-content') // "Show less" link on restrictions.
                                || tgt.hasClass('collapsed-content'); // "Show more" link on restrictions
                            if (isExcludedControl) {
                                return;
                            }
                            e.preventDefault();
                            const currTgt = $(e.currentTarget);
                            var clickedCmObject = currTgt.closest("li.activity");
                            const cmId = clickedCmObject.data('cmid');
                            const moduleContextId = clickedCmObject.data('contextid');
                            const sectionNum = clickedCmObject.closest(Selector.sectionMain).data('section');

                            launchCmModal(
                                cmId,
                                moduleContextId,
                                sectionNum,
                                clickedCmObject.data('title'),
                                clickedCmObject.data('modal'),
                                clickedCmObject.hasClass(CLASS.COMPLETION_ENABLED),
                                clickedCmObject.data('completion-state')
                                    ? parseInt(clickedCmObject.data('completion-state')) : null,
                                clickedCmObject.hasClass(CLASS.COMPLETION_MANUAL),
                                clickedCmObject.find('.modal-description').html(),
                            );
                        });

                        // Render the loading icon and append it to body so that we can use it later.
                        Templates.render("format_tiles/loading", {})
                            .catch(Notification.exception)
                            .done(function (html) {
                                loadingIconHtml = html; // TODO get this from elsewhere.
                            }).fail(Notification.exception);

                    } else if (pageType.match('^mod-[a-z]+-view$')) {
                        courseIndex.on('click', function (e) {
                            const target = $(e.target);
                            const link = target.hasClass('courseindex-link') ? target : target.find('a.courseindex-link');
                            if (link && link.data('for') === 'cm_name') {
                                e.preventDefault();
                                const linkUrl = link.attr('href');
                                if (linkUrl) {
                                    const link = $(e.target);
                                    const cmId = link.closest('li.courseindex-item').data('id');
                                    if (modalRequired(cmId, linkUrl)) {
                                        if (usingJsNav) {
                                            window.location.href = `${config.wwwroot}/course/view.php?id=${courseId}&cmid=${cmId}`;
                                        } else {
                                            const sectionElement = link.closest('.courseindex-section');
                                            const sectionId = sectionElement ? sectionElement.data('id') : 0;
                                            window.location.href =
                                                `${config.wwwroot}/course/section.php?id=${sectionId}&cmid=${cmId}`;
                                        }
                                    } else {
                                        window.location.href = linkUrl;
                                    }
                                }
                            }
                        });
                    }
                });
            }
        };
    }
);
