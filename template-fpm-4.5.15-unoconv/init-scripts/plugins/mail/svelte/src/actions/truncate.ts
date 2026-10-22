/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

export function truncate(node: HTMLElement, tooltip: string) {
    function handleMouseEnter() {
        if (node.offsetWidth < node.scrollWidth) {
            node.setAttribute('title', tooltip);
        } else {
            node.removeAttribute('title');
        }
    }

    node.style.overflow = 'hidden';
    node.style.textOverflow = 'ellipsis';
    node.style.whiteSpace = 'nowrap';
    node.addEventListener('mouseenter', handleMouseEnter);

    return {
        update(newTooltip: string) {
            tooltip = newTooltip;
        },
        destroy() {
            node.removeEventListener('mouseenter', handleMouseEnter, true);
        },
    };
}
