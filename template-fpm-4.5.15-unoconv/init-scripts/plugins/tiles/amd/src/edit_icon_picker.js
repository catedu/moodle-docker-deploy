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
 * Javascript Module to handle the icon picker dialogue for format_tiles
 * which the editing user uses to select an icon for a tile or the default icon
 * for all tiles in the course
 *
 * @module      format_tiles/icon_picker
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since       Moodle 3.3
 */

define(["jquery", "core/templates", "core/ajax", "core/str", "core/notification", "core/config"],
    function ($, Templates, ajax, str, Notification, config) {
        "use strict";

        var modalStored;
        var stringStore = {pickAnIcon: ''};
        var iconSet = [];
        var recentPhotoSet = [];

        /**
         * Get the available icon set and photo set and store them for later use.
         * @param {number}courseId
         * @param {function|undefined} callback what to do after.
         */
        var getAndStoreIconSet = function(courseId, callback) {
            var photosPromises = ajax.call([{
                methodname: "format_tiles_get_icon_set",
                args: {courseid: courseId}
            }]);
            photosPromises[0].done(function (response) {
                if (response.photos) {
                    recentPhotoSet = JSON.parse(response.photos);
                }
                var icons = JSON.parse(response.icons);
                Object.keys(icons).forEach(function(icon) {
                    iconSet.push({filename: icon, displayname: icons[icon]});
                });
                if (iconSet.length <= 0) {
                    require(["core/log"], function(log) {
                        log.error("Error empty icon set");
                        log.debug(response);
                    });
                }
                if (typeof callback === "function") {
                    callback();
                }

                // Check if there are any photos in the library that need deleting.
                var photoNames = recentPhotoSet.map(function(photo) {
                    return photo.filename;
                });
                $("#iconpickerphotos").find(".photo").each(function (index, ph) {
                    ph = $(ph);
                    if (photoNames.indexOf(ph.data("filename")) === -1) {
                        ph.fadeOut(500);
                    }
                });
                if (response.status !== true) {
                    require(["core/log"], function(log) {
                        log.error("Non true status response when getting icon set");
                        log.debug(response);
                    });
                }
            });
            photosPromises[0].fail(function (response) {
                require(["core/log"], function(log) {
                    log.error("Fail when getting icon set");
                    log.debug(response);
                });
            });
        };

        /**
         * Get the URL for a link for a photo tile button (to access the edit form).
         * @param {number} courseId
         * @param {number} sectionId
         * @returns {string}
         */
        var getPhotoTileButtonUrl = function(courseId, sectionId) {
            return config.wwwroot
                + '/course/format/tiles/editor/editimage.php?sectionid=' + sectionId;
        };

        /**
         * Set the selected icon in the database via AJAX to the web service.
         * When successful, then change the icon being displayed to the current editing user.
         * If we are on an edit form, also select the selected icon in the hidden HTML selecftBox.
         * The select box
         * @param {number} sectionId
         * @param {number} sectionNum
         * @param {string} icon
         * @param {string} displayname
         * @param {string} pageType
         * @param {number} courseId
         * @param {string} imageType
         * @param {number|undefined} sourcecontextid
         * @param {number|undefined} sourceitemid
         */
        var setIcon = function (
            sectionId, sectionNum, icon, displayname, pageType, courseId, imageType, sourcecontextid, sourceitemid
        ) {
            var selectedIcon = $("#selectedicon");

            const onCoursePage = ['section-view-tiles', "course-view-tiles", 'course-view-section-tiles'].includes(pageType);
            var changeUiTilePhoto = function (jqueryObjToChange, imageUrl, imageType) {
                var templateToRender = '';
                var templateParams = {
                    tileicon: icon,
                    tileid: sectionNum,
                    secid: sectionId,
                    isediting: 1
                };
                switch (imageType) {
                    case 'tileicon':
                        if (icon.match(/^number_[\d]{1,2}$/)) {
                            templateToRender = 'tilenumber';
                            templateParams.tilenumber = icon.match(/\d+/)[0];
                        } else {
                            templateToRender = 'tileicon';
                        }
                        break;
                    case 'tilephoto':
                        if (!imageUrl) {
                            // Unsetting photo.
                            templateParams.tileicon = $('#format-tiles-js-config').data('defaultcourseicon');
                            templateToRender = 'tileicon';
                            break;
                        }
                        templateToRender = 'tilebarphoto';
                        templateParams.phototileurl = imageUrl;
                        templateParams.phototileediturl = getPhotoTileButtonUrl(courseId, sectionId);
                        templateParams.imagetype = imageType;
                        jqueryObjToChange.closest(".tileiconcontainer").addClass("hasphoto");
                        // Refresh the photos in library as may not are still be available.
                        setTimeout(function () {
                            getAndStoreIconSet(courseId);
                        }, 3000);
                        break;
                    case 'draftfile':
                        templateToRender = 'tilebarphoto';
                        templateParams.phototileurl = imageUrl;
                        templateParams.phototileediturl = getPhotoTileButtonUrl(courseId, sectionId);
                        templateParams.imagetype = imageType;
                        break;
                    default:
                        throw new Error("Invalid image type " + imageType);
                }
                var divToAnimate = onCoursePage ? jqueryObjToChange : selectedIcon;
                divToAnimate.animate({opacity: 0}, 500, function () {
                    Templates.render("format_tiles/" + templateToRender, templateParams)
                        .done(function (html) {
                            divToAnimate.html(html)
                                .animate({opacity: 1}, 500);
                        });
                });
            };
            var ajaxIconPickArgs = {
                image: icon,
                courseid: courseId,
                sectionid: Number(sectionId === undefined ? 0 : sectionId),
                imagetype: imageType,
                sourcecontextid: sourcecontextid === undefined ? 0 : sourcecontextid,
                sourceitemid: sourceitemid === undefined ? 0 : sourceitemid,
                // Sectionid will be zero if relates to whole course not just one sec.
            };
            var setIconDbPromises = ajax.call([{
                methodname: "format_tiles_set_image",
                args: ajaxIconPickArgs
            }]);

            setIconDbPromises[0].done(function (response) {
                if (response.status === true) {
                    if (onCoursePage) {
                        // We are changing an icon for a specific section from within the course.
                        // We are doing this by clicking an existing icon.
                        changeUiTilePhoto($("#tileicon_" + sectionNum), response.imageurl, imageType);
                        if (response.imageurl === '') {
                            // We are resetting tile.  Refresh photo library as image may be deleted now.
                            getAndStoreIconSet(courseId);
                        }
                    } else if (pageType === "course-edit") {
                        // We are changing the icon using a drop down menu not the icon picker modal.
                        // For the whole course.
                        // Select new icon in drop down.
                        var selectBox = $("#id_defaulttileicon"); // Valid if page type is course-edit.
                        selectBox.val(icon);
                        // Then change the image shown next to it.
                        if (imageType === "tileicon") {
                            Templates.renderPix("tileicon/" + icon, "format_tiles", displayname)
                                .done(function (newIcon) {
                                    selectedIcon.html(newIcon);
                                });
                        } else if (imageType === "tilephoto") {
                            // We are changing a tile photo.
                            changeUiTilePhoto($("#tileicon_" + sectionNum), response.imageurl, imageType);
                        }
                    }
                } else {
                    require(["core/log"], function(log) {
                        log.error("False response setting icon");
                        log.debug(response);
                    });
                }
            }).fail(function(response) {
                require(["core/log"], function(log) {
                    log.error("Fail setting icon");
                    log.debug(response);
                });
            });
        };

        /**
         * When user clicks to launch an icon picker modal, set which section it relates to
         * so that we know which section the icon clicked is for.  This is so that only one modal needs
         * to be rendered (with all the icons in it) - we can use it to assign icons to any section
         * @param {string} pageType
         * @param {number} courseId
         * @param {int} sectionId
         * @param {int} section
         * @param {int} allowPhotoTiles whether to render a button for the photo tile form - true or false).
         * @param {number} maxNumberIcons
         */
        var launchIconPicker = function (pageType, courseId, sectionId, section,
                                         allowPhotoTiles, maxNumberIcons) {
            // Launch icon picker can be a tile icon (if editing course) or a button (if on a form).
            var populatePhotoLibrary = function(photosHTML, modalRoot, modal) {
                var photoLibrary = $("#iconpickerphotos");
                photoLibrary.html(photosHTML);

                // Load the images that are not too large immediately.
                // (User draft files may be large so leave them to load last.
                var largeFileThreshold = 200000; // Bytes.
                var doLast = [];
                photoLibrary.find(".photo").each(function (index, image) {
                    image = $(image);
                    if (image.data("filesize") < largeFileThreshold && image.data("url")) {
                        setTimeout(function () {
                            image.attr("style", `background-image: url('${image.data("url")}');`);
                        }, index * 20);
                    } else {
                        doLast.push(image);
                    }

                    image.click(function (e) {
                        var clickedImage = $(e.currentTarget);
                        setIcon(
                            modalRoot.data("true-sectionid"),
                            modalRoot.data("section"),
                            clickedImage.data("filename"),
                            clickedImage.data("filename"),
                            pageType,
                            courseId,
                            clickedImage.data("imagetype"),
                            clickedImage.data("contextid"), // For existing photos - sourcecontextid.
                            clickedImage.data("itemid") // For existing photos - sourceitemid.
                        );
                        modal.hide();
                    });
                });
                setTimeout(function () {
                    doLast.forEach(function (image) {
                        image.attr("src", image.data("url"));
                    });
                }, 1000);
            };

            if (typeof modalStored !== "object") {
                // We only have one modal per page which we recycle.  We don't have it yet so create it.

                var renderModal = function() {
                    Templates.render("format_tiles/icon_picker_modal_body", {
                        /* eslint-disable-next-line camelcase */
                        icon_picker_icons: iconSet,
                        showphotos: allowPhotoTiles,
                        sectionnumber: section,
                        showicons: true, // Always include this, but we can hide it when using photos.
                        tilenumbers: Array.from({length: maxNumberIcons + 1}, (e, i)=> i).filter((e) => e > 0),
                        wwwroot: config.wwwroot
                    }).done(function (iconsHTML) {
                        require(["core/modal_factory"], function (modalFact) {
                            modalFact.create({
                                type: modalFact.types.DEFAULT,
                                title: stringStore.pickAnIcon,
                                body: iconsHTML
                            }).done(function (modal) {
                                modalStored = modal;
                                modal.setLarge();
                                modal.show();
                                var modalRoot = $(modal.root);
                                modalRoot.attr("id", "icon_picker_modal");
                                modalRoot.data("true-sectionid", sectionId);
                                modalRoot.data("section", section);
                                modalRoot.addClass("icon_picker_modal");
                                modalRoot.find(`#tile-number-container-${section}`).addClass('suggested');
                                modalRoot.on("click", ".pickericon", function (e) {
                                    var newIcon = $(e.currentTarget);
                                    setIcon(
                                        sectionId,
                                        section,
                                        newIcon.data("icon"),
                                        newIcon.attr("title"),
                                        pageType,
                                        courseId,
                                        'tileicon',
                                        newIcon.data("contextid"), // For existing photos - sourcecontextid.
                                        newIcon.data("itemid") // For existing photos - sourcetemid.
                                    );
                                    modal.hide();
                                });
                                // Icon search box handling.
                                modalRoot.on("input", "input.iconsearch", function (e) {
                                    var searchText = e.currentTarget.value.toLowerCase();
                                    modalRoot.find(".pickericon").show();
                                    if (searchText.length >= 3) {
                                        modalRoot.find(".pickericon").filter(function (index, icon) {
                                            // Show all icons then hide icons which do not match the search term.
                                            return $(icon).data('original-title').toLowerCase().indexOf(searchText) < 0;
                                        }).hide();
                                    }
                                });
                                if (allowPhotoTiles) {
                                    // Set the URL for the photo upload button if used (done dynamically as contains section id).
                                    var url = getPhotoTileButtonUrl(courseId, sectionId);
                                    modalRoot.find('#phototilebtn')
                                        .attr('href', url);
                                    // Now that we have modal, if photo library tab is clicked we need to lazy load the photos.
                                    $("#launch-photo-library").click(function () {
                                        if (recentPhotoSet.length !== 0) {
                                            Templates.render("format_tiles/icon_picker_photos", {
                                                /* eslint-disable-next-line camelcase */
                                                icon_picker_photos: recentPhotoSet,
                                                wwwroot: config.wwwroot
                                            }).done(function (photosHTML) {
                                                populatePhotoLibrary(photosHTML, modalRoot, modal);
                                            });
                                        }
                                    });
                                }
                                $(document).trigger('format-tiles-icon-picker-modal-created');
                            });
                        });
                    });
                };
                if (iconSet.length <= 0) {
                    getAndStoreIconSet(courseId, renderModal);
                } else {
                    renderModal();
                }
            } else {
                // We already have the modal so recycle it instead of re-rendering.
                modalStored.root.data("true-sectionid", sectionId);
                modalStored.root.data("section", section);
                modalStored.root.off("click");
                modalStored.root.find(`.tile-number-container`).removeClass('suggested');
                modalStored.root.find(`#tile-number-container-${section}`).addClass('suggested');
                modalStored.root.on("click", ".pickericon", function (e) {
                    var newIcon = $(e.currentTarget);
                    setIcon(
                        sectionId,
                        section,
                        newIcon.data("icon"),
                        newIcon.attr("title"),
                        pageType,
                        courseId,
                        newIcon.data("imagetype"),
                        newIcon.data("contextid"), // For existing photos - sourcecontextid.
                        newIcon.data("itemid") // For existing photos - sourcetemid.
                    );
                    modalStored.hide();
                });
                if (allowPhotoTiles) {
                    // Set the URL for the photo upload button if used (done dynamically as contains section id).
                    var url = getPhotoTileButtonUrl(courseId, sectionId);
                    modalStored.root.find('#phototilebtn')
                        .attr('href', url);
                }
                modalStored.show();
            }
        };

        return {
            init: function (courseId, pageType, allowPhotoTiles, maxNumberIcons) {
                $(document).ready(function () {
                    var stringKey = allowPhotoTiles ? "picknewiconphoto" : "picknewicon";
                    str.get_string(stringKey, "format_tiles").done(function (pickAnIcon) {
                        stringStore.pickAnIcon = pickAnIcon;
                    });
                    // Get the core icon set now so that we don't have to wait later.
                    getAndStoreIconSet(courseId);

                    var pageContent = $("#page-content");
                    if (pageContent.length === 0) {
                        // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                        pageContent = $("#region-main");
                    }
                    pageContent.on("click", '[data-tiles-action="launch-tiles-icon-picker"]', function (e) {
                        e.preventDefault();
                        var clickedIcon = $(e.currentTarget);
                        launchIconPicker(
                            pageType,
                            courseId,
                            clickedIcon.data('true-sectionid'),
                            clickedIcon.data('section'),
                            allowPhotoTiles,
                            maxNumberIcons
                        );
                    });
                });
            }
        };
    }
);