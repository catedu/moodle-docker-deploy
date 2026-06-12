<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\exception
 */
final class exception_test extends \basic_testcase {
    public function test_construct(): void {
        $exception = new exception('errortoomanyrecipients', 123, 'debug info');

        self::assertEquals('errortoomanyrecipients', $exception->errorcode);
        self::assertEquals('local_mail', $exception->module);
        self::assertEquals(123, $exception->a);
        self::assertEquals('', $exception->link);
        self::assertEquals('debug info', $exception->debuginfo);
    }
}
