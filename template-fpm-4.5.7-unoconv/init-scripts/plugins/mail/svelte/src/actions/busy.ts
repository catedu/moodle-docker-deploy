/*
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

export function busy(node: HTMLElement, busy: boolean) {
    const listener = (event: Event) => {
        event.stopPropagation();
        event.preventDefault();
    };

    const update = (busy: boolean) => {
        if (busy) {
            node.classList.add('local-mail-busy');
            node.addEventListener('keydown', listener, { capture: true });
        } else {
            node.classList.remove('local-mail-busy');
            node.removeEventListener('keydown', listener, { capture: true });
        }
    };

    update(busy);

    return {
        update,
        destroy() {
            update(false);
        },
    };
}
