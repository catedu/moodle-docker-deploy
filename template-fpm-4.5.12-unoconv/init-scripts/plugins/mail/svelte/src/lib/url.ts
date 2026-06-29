/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

import { type Dialog, type SearchParams, type Tray, type ViewParams } from './state';

function baseUrl() {
    return window.M.cfg.wwwroot + '/local/mail/';
}

export function createUrl(courseid?: number, recipients: number[] = [], role?: string): string {
    const url = new URL(baseUrl() + 'create.php');

    if (courseid) {
        url.searchParams.set('course', String(courseid));
    }
    if (recipients.length) {
        url.searchParams.set('recipients', recipients.join(','));
    }
    if (role) {
        url.searchParams.set('role', role);
    }
    url.searchParams.set('sesskey', window.M.cfg.sesskey);

    return url.toString();
}

export function downloadAllUrl(messageid: number): string {
    const url = new URL(baseUrl() + 'download.php');

    url.searchParams.set('m', String(messageid));

    return url.toString();
}

export function getViewParamsFromUrl(): ViewParams {
    const url = new URL(window.location.href);
    const params: ViewParams = {
        tray: (url.searchParams.get('t') as Tray) || undefined,
        courseid: parseInt(url.searchParams.get('c') || '') || undefined,
        labelid: parseInt(url.searchParams.get('l') || '') || undefined,
        messageid: parseInt(url.searchParams.get('m') || '') || undefined,
        offset: parseInt(url.searchParams.get('o') || '') || undefined,
        dialog: (url.searchParams.get('d') as Dialog) || undefined,
    };
    const search: SearchParams = {
        content: url.searchParams.get('s') || undefined,
        sendername: url.searchParams.get('sf') || undefined,
        recipientname: url.searchParams.get('st') || undefined,
        unread: url.searchParams.get('su') == '1' || undefined,
        withfilesonly: url.searchParams.get('sa') == '1' || undefined,
        maxtime: parseInt(url.searchParams.get('sd') || '') || undefined,
        startid: parseInt(url.searchParams.get('ss') || '') || undefined,
        reverse: url.searchParams.get('sr') == '1' || undefined,
    };
    return Object.values(search).some((v) => v != null) ? { ...params, search } : params;
}

export function setUrlFromViewParams(params: ViewParams, replace: boolean) {
    const url = new URL(viewUrl(params));
    if (url.search != window.location.search) {
        if (replace) {
            window.history.replaceState(undefined, '', url.toString());
        } else {
            window.history.pushState(undefined, '', url.toString());
        }
    }
}

export function viewUrl(params: ViewParams): string {
    const url = new URL(baseUrl() + 'view.php');

    if (params.tray) {
        url.searchParams.set('t', params.tray);
    }
    if (params.courseid) {
        url.searchParams.set('c', String(params.courseid));
    }
    if (params.tray == 'label' && params.labelid) {
        url.searchParams.set('l', String(params.labelid));
    }
    if (params.messageid) {
        url.searchParams.set('m', String(params.messageid));
    }
    if (params.offset) {
        url.searchParams.set('o', String(params.offset));
    }
    if (params.search?.content) {
        url.searchParams.set('s', params.search.content);
    }
    if (params.search?.sendername) {
        url.searchParams.set('sf', params.search.sendername);
    }
    if (params.search?.recipientname) {
        url.searchParams.set('st', params.search.recipientname);
    }
    if (params.search?.unread) {
        url.searchParams.set('su', '1');
    }
    if (params.search?.withfilesonly) {
        url.searchParams.set('sa', '1');
    }
    if (params.search?.maxtime) {
        url.searchParams.set('sd', String(params.search.maxtime));
    }
    if (params.search?.startid) {
        url.searchParams.set('ss', String(params.search.startid));
    }
    if (params.search?.reverse) {
        url.searchParams.set('sr', '1');
    }
    if (params.dialog) {
        url.searchParams.set('d', params.dialog);
    }

    return url.toString();
}
