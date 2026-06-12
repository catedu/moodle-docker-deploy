<?php
/*
 * SPDX-FileCopyrightText: 2017-2024 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'local_mail' => [
        'handlers' => [
            'view' => [
                'init' => 'init',
                'method' => 'view',
            ],
        ],
        'lang' => [
            ['pluginname', 'local_mail'],
        ],
    ],
];
