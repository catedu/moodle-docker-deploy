<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'courses' => [
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
    ],
    'labels' => [
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
    ],
];
