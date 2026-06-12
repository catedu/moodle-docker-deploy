/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/// <reference types="svelte" />
/// <reference types="vite/client" />

declare interface Window {
    M: {
        cfg: {
            wwwroot: string;
            sesskey: string;
        };
        str: {
            langconfig: {
                localecldr: string;
            };
        };
    };
    require: (deps: string[], callback: (...modules: unknown[]) => void) => void;
    local_mail_navbar_data: Record<string, unknown> | undefined;
    local_mail_view_data: Record<string, unknown> | undefined;
}
