<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

defined('MOODLE_INTERNAL') || die();

class local_mail_coverage extends phpunit_coverage_info {
    protected $includelistfolders = [
        'backup',
    ];

    protected $includelistfiles = [
        'db/upgrade.php',
    ];

    protected $excludelistfolders = [
        'classes/test',
    ];
}

return new local_mail_coverage();
