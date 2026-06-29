<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\output\strings
 */
final class output_strings_test extends test\testcase {
    public function test_get(): void {
        self::assertEquals('{$a->index} of {$a->total}', output\strings::get('pagingsingle'));
        self::assertEquals('3 of 14', output\strings::get('pagingsingle', ['index' => '3', 'total' => '14']));
    }

    public function test_get_all(): void {
        self::assertEquals(self::load_strings(), output\strings::get_all());
    }

    public function test_get_ids(): void {
        $ids = array_keys(self::load_strings());
        self::assertEquals($ids, output\strings::get_ids());
    }

    public function test_get_many(): void {
        $strings = self::load_strings();
        $ids = self::random_items(array_keys($strings), 10);
        self::assertEquals(
            array_intersect_key($strings, array_combine($ids, $ids)),
            output\strings::get_many($ids)
        );
    }

    private static function load_strings(): array {
        global $CFG;

        $string = [];
        include("$CFG->dirroot/local/mail/lang/en/local_mail.php");

        return $string;
    }
}
