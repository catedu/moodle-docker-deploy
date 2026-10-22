/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

const that = this;

class AddonLocalMailLinkHandler extends that.CoreContentLinksHandlerBase {
    name = 'AddonLocalMailLinkHandler';
    pattern = new RegExp('/local/mail/view.php');

    getActions(siteIds, url, params) {
        const action = {
            action(siteId) {
                const page = `siteplugins/content/local_mail/view/0`;
                const pageParams = {
                    title: 'plugin.local_mail.pluginname',
                    args: params,
                };
                that.CoreNavigatorService.navigateToSitePath(page, { params: pageParams, siteId });
            },
        };

        return [action];
    }
}

class AddonLocalMaiMainMenuHandler {
    name = 'AddonLocalMailMainMenuHandler';

    async isEnabled() {
        return true;
    }

    getDisplayData() {
        return {
            title: 'plugin.local_mail.pluginname',
            icon: 'far-envelope',
            page: 'siteplugins/content/local_mail/view/0',
            get pageParams() {
                const zoomLevel = document.documentElement.style.getPropertyValue('--zoom-level');
                return {
                    title: 'plugin.local_mail.pluginname',
                    args: {
                        appzoom: parseInt(zoomLevel) / 100,
                    },
                };
            },
        };
    }
}

that.CoreMainMenuDelegate.registerHandler(new AddonLocalMaiMainMenuHandler());
that.CoreContentLinksDelegate.registerHandler(new AddonLocalMailLinkHandler());
