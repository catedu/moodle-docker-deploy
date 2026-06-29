<?php
/*
 * SPDX-FileCopyrightText: 2012-2013 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2021 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\label
 */
final class label_test extends test\testcase {
    public function test_create(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());

        $label1 = label::create($user, 'name1', 'red');

        self::assertInstanceOf(label::class, $label1);
        self::assertGreaterThan(0, $label1->id);
        self::assertEquals($user->id, $label1->userid);
        self::assertEquals('name1', $label1->name);
        self::assertEquals('red', $label1->color);
        self::assert_label($label1);

        // Check cache.

        label::cache()->set('userid', $user->id);
        label::cache()->set('labelids', [$label1->id]);
        label::cache()->set($label1->id, $label1);

        $label2 = label::create($user, 'name2', 'blue');

        self::assertFalse(label::cache()->get('userid'));
        self::assertFalse(label::cache()->get('labelids'));
        self::assertFalse(label::cache()->get($label1->id));
        self::assertFalse(label::cache()->get($label2->id));
    }

    public function test_delete(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user, 'name 1', 'red');
        $label2 = label::create($user, 'name 2');
        $data = message_data::new($course, $user);
        $message = message::create($data);
        $message->set_labels($user, [$label1, $label2]);
        label::cache()->set('userid', $user->id);
        label::cache()->set('labelids', [$label1->id, $label2->id]);
        label::cache()->set($label1->id, $label1);
        label::cache()->set($label2->id, $label2);

        $label1->delete();

        self::assert_record_count(0, 'labels', ['id' => $label1->id]);
        self::assert_record_count(0, 'message_labels', ['labelid' => $label1->id]);
        self::assertEquals($label2, label::get($label2->id));
        self::assertFalse(label::cache()->get('userid'));
        self::assertFalse(label::cache()->get('labelids'));
        self::assertFalse(label::cache()->get($label1->id));
        self::assertFalse(label::cache()->get($label2->id));
    }

    public function test_get(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        $label = label::create($user, 'name 1', 'red');

        $result = label::get($label->id);

        self::assertEquals($label, $result);

        // Missing label.
        try {
            label::get(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Get from cache.
        $label = new label((object) [
            'id' => 123,
            'userid' => $user->id,
            'name' => 'Label 123',
            'color' => 'red',
        ]);
        label::cache()->set($label->id, $label);
        self::assertEquals($label, label::get($label->id));
    }

    public function test_get_by_user(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $label2 = label::create($user1, 'name 2', 'blue');
        $label4 = label::create($user1, 'name 4', 'purple');
        $label3 = label::create($user2, 'name 3', 'yellow');
        $label1 = label::create($user1, 'name 1', 'red');

        $result = label::get_by_user($user1);

        self::assert_array_of_objects([$label1, $label2, $label4], $result);
        self::assertEquals($user1->id, label::cache()->get('userid'));
        self::assertEquals([$label1->id, $label2->id, $label4->id], label::cache()->get('labelids'));
        self::assertEquals($label1, label::cache()->get($label1->id));
        self::assertEquals($label2, label::cache()->get($label2->id));
        self::assertEquals($label4, label::cache()->get($label4->id));
        self::assertFalse(label::cache()->get($label3->id));

        // User with no labels.

        $result = label::get_by_user($user3);

        self::assertEquals([], $result);
        self::assertEquals($user3->id, label::cache()->get('userid'));
        self::assertEquals([], label::cache()->get('labelids'));
        self::assertFalse(label::cache()->get($label1->id));
        self::assertFalse(label::cache()->get($label2->id));
        self::assertFalse(label::cache()->get($label4->id));

        // Get from cache.

        $label1->delete();
        $label2->delete();
        label::cache()->set('userid', $user1->id);
        label::cache()->set('labelids', [$label1->id, $label2->id]);
        label::cache()->set($label1->id, $label1);
        label::cache()->set($label2->id, $label2);

        $result = label::get_by_user($user1);

        self::assert_array_of_objects([$label1, $label2], $result);
    }

    public function test_get_many(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label1 = label::create($user1, 'name 1', 'red');
        $label2 = label::create($user2, 'name 2', 'blue');
        $label3 = label::create($user1, 'name 3', 'yellow');

        $result = label::get_many([$label1->id, $label2->id, $label1->id]);

        self::assert_array_of_objects([$label1, $label2], $result);
        self::assertFalse(label::cache()->get($label1->id));
        self::assertFalse(label::cache()->get($label2->id));
        self::assertFalse(label::cache()->get($label3->id));

        // Missing label.
        try {
            label::get_many([$label1->id, 123, $label2->id]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // No IDs.
        self::assertEquals([], label::get_many([]));

        // Get from cache.
        $label = new label((object) [
            'id' => 123,
            'userid' => $user1->id,
            'name' => 'Label 123',
            'color' => 'red',
        ]);
        label::cache()->set($label->id, $label);
        self::assert_array_of_objects([$label], label::get_many([$label->id]));
    }

    public function test_update(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        $label1 = label::create($user, 'name 1', 'red');
        $label2 = label::create($user, 'name 2');
        label::cache()->set('userid', $user->id);
        label::cache()->set('labelids', [$label1->id, $label2->id]);
        label::cache()->set($label1->id, $label1);
        label::cache()->set($label2->id, $label2);

        $label1->update('new name', 'indigo');

        self::assertEquals('new name', $label1->name);
        self::assertEquals('indigo', $label1->color);
        self::assert_label($label1);
        self::assert_label($label2);
        self::assertFalse(label::cache()->get('userid'));
        self::assertFalse(label::cache()->get('labelids'));
        self::assertFalse(label::cache()->get($label1->id));
        self::assertFalse(label::cache()->get($label2->id));
    }

    public function test_normalized_name(): void {
        self::assertEquals('', label::nromalized_name(''));
        self::assertEquals('word', label::nromalized_name('word'));
        self::assertEquals('multiple words', label::nromalized_name('multiple words'));
        self::assertEquals('collapse space', label::nromalized_name('collapse     space'));
        self::assertEquals('replace line breaks', label::nromalized_name("replace\nline\rbreaks"));
        self::assertEquals('replace tab character', label::nromalized_name("replace\ttab\tcharacter"));
        self::assertEquals('trim text', label::nromalized_name('  trim text  '));
    }

    /**
     * Asserts that a label is stored correctly in the database.
     *
     * @param label $label Label.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_label(label $label): void {
        self::assert_record_data('labels', [
            'id' => $label->id,
        ], [
            'userid' => $label->userid,
            'name' => $label->name,
            'color' => $label->color,
        ]);
    }
}
