<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail\output;

class strings {
    /**
     * Returns a language string with parameters replaced.
     *
     * @param string $id The string identifier.
     * @param string|object|array $param A string, a number or an object to replace parameters with.
     * @return string The localized string.
     */
    public static function get(string $id, $param = null): string {
        return get_string($id, 'local_mail', $param);
    }

    /**
     * Returns all strings.
     *
     * @return string[] All localized strings.
     */
    public static function get_all(): array {
        return get_string_manager()->load_component_strings('local_mail', current_language());
    }

    /**
     * Returns the identifiers of all strings.
     *
     * @return string[]
     */
    public static function get_ids(): array {
        return array_keys(get_string_manager()->load_component_strings('local_mail', 'en'));
    }

    /**
     * Returns multiple strings.
     *
     * @param string[] $ids Identifiers.
     * @return string[] Localized strings indexed by identifier.
     */
    public static function get_many(array $ids): array {
        return (array) get_strings($ids, 'local_mail');
    }
}
