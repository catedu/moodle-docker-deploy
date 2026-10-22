/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

const that = this;

const getPluginElement = () => {
    return that.componentContainer.closest('page-core-site-plugins-plugin');
};

const messageHandler = async (event) => {
    if (event.data.addon != 'local_mail') {
        return;
    }
    if (event.data.setLoading != null) {
        const toolbar = getPluginElement()?.querySelector('ion-toolbar');
        if (toolbar) {
            let spinner = toolbar.querySelector('ion-spinner');
            if (!spinner) {
                spinner = document.createElement('ion-spinner');
                spinner.setAttribute('slot', 'end');
                spinner.setAttribute('color', 'primary');
                spinner.setAttribute('paused', 'true');
                spinner.style.marginRight = '15px';
                spinner.style.opacity = 0;
                spinner.style.transitionDuration = '400ms';
                spinner.style.transitionProperty = 'opacity';
                spinner.addEventListener("transitionend", () => {
                    if (spinner.style.opacity == 0) {
                        spinner.setAttribute('paused', 'true');
                    }
                });
                toolbar.append(spinner);
            }
            if (event.data.setLoading) {
                spinner.setAttribute('paused', 'false');
                spinner.style.opacity = 1;
            } else {
                spinner.style.opacity = 0;
            }
        }
    }
    if (event.data.setTitle != null) {
        const element = getPluginElement()?.querySelector('h1');
        if (element) {
            element.textContent = event.data.setTitle;
        }
    }
    if (event.data.captureBack != null) {
        const element = getPluginElement()?.querySelector('ion-back-button');
        if (element) {
            if (event.data.captureBack) {
                element?.addEventListener('click', backButtonHandler, { capture: true });
            } else {
                element?.removeEventListener('click', backButtonHandler, { capture: true });
            }
        }
    }
    if (event.data.openUrl) {
        const action = await that.CoreContentLinksHelperProvider.getFirstValidActionFor(
            event.data.openUrl,
        );
        if (action) {
            action.action(that.CoreSitesProvider.getCurrentSiteId());
        } else {
            const site = that.CoreSitesProvider.currentSite;
            site.openInAppWithAutoLogin(event.data.openUrl);
        }
    }
};

const backButtonHandler = (event) => {
    const iframe = getPluginElement()?.querySelector('iframe');
    if (iframe) {
        iframe.contentWindow.postMessage({ addon: 'local_mail', backClicked: true }, '*');
        event.stopPropagation();
    }
};

that.ionViewWillEnter = () => {
    window.addEventListener('message', messageHandler);
};

that.ionViewWillLeave = () => {
    window.removeEventListener('message', messageHandler);
};
