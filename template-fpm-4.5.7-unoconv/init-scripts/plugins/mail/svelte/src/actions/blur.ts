/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

export function blur(node: HTMLElement, handler: () => void) {
    function handleFocusOut(event: FocusEvent) {
        if (event.relatedTarget instanceof Node && !node.contains(event.relatedTarget)) {
            handler();
        }
    }

    function handleMouseDown(event: Event) {
        if (event.target instanceof Node && !node.contains(event.target)) {
            handler();
        }
    }

    document.addEventListener('mousedown', handleMouseDown, { capture: true, passive: true });
    node.addEventListener('focusout', handleFocusOut);

    return {
        update(newHandler: () => void) {
            handler = newHandler;
        },
        destroy() {
            document.removeEventListener('mousedown', handleMouseDown, { capture: true });
            node.removeEventListener('focusout', handleFocusOut);
        },
    };
}
