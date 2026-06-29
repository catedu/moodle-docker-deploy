<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

class exception extends \moodle_exception {
    /**
     * Constructor.
     *
     * @param string $errorcode Language string name.
     * @param mixed $a Language string parameters.
     * @param ?string $debuginfo Optional debugging information
     */
    public function __construct(string $errorcode, $a = null, ?string $debuginfo = null) {
        parent::__construct($errorcode, 'local_mail', '', $a, $debuginfo);
    }
}
