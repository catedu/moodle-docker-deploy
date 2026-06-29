<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\message_data
 */
final class message_data_test extends test\testcase {
    public function test_draft(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $course = new course($generator->create_course());

        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = make_timestamp(2021, 10, 11, 12, 0);
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');

        $message = message::create($data);

        $data = message_data::draft($message);
        self::assertEquals($message->sender(), $data->sender);
        self::assertNull($data->reference);
        self::assertEquals($message->course->id, $data->course->id);
        self::assertEqualsCanonicalizing([$user2, $user3], $data->to);
        self::assertEqualsCanonicalizing([$user4], $data->cc);
        self::assertEqualsCanonicalizing([$user5], $data->bcc);
        self::assertEquals('Subject', $data->subject);
        self::assertEquals('Content', $data->content);
        self::assertEquals((int) FORMAT_PLAIN, $data->format);
        self::assert_draft_files(['file1.txt' => 'File 1', 'file2.txt' => 'File 2'], $data->draftitemid);
        self::assertEquals($message->time, $data->time);
    }

    public function test_forward(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $now = time();

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message = message::create($data);
        $message->send($time);

        $data = message_data::forward($message, $user2);
        self::assertEquals($user2, $data->sender);
        self::assertNull($data->reference);
        self::assertEquals($message->course->id, $data->course->id);
        self::assertEqualsCanonicalizing([], $data->to);
        self::assertEqualsCanonicalizing([], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);
        self::assertEquals('FW: Subject', $data->subject);
        $expected = '<p><br></p>'
            . '<p>'
            . '--------- ' . output\strings::get('forwardedmessage') . ' ---------<br>'
            . output\strings::get('from') . ': '
            . $message->sender()->fullname() . '<br>'
            . output\strings::get('date') . ': '
            . userdate($message->time, get_string('strftimedatetime', 'langconfig')) . '<br>'
            . output\strings::get('subject') . ': '
            . s($message->subject)
            . '</p>'
            . format_text($message->content, $message->format, ['filter' => false, 'para' => false]);
        self::assertEquals($expected, $data->content);
        self::assertEquals((int) FORMAT_HTML, $data->format);
        self::assertGreaterThan(0, $data->draftitemid);
        self::assert_draft_files(['file1.txt' => 'File 1', 'file2.txt' => 'File 2'], $data->draftitemid);
        self::assertGreaterThanOrEqual($now, $data->time);

        // Forward forwarded message.
        $data->to = [$user1];
        $message = message::create($data);
        $message->send($time);
        $data = message_data::forward($message, $user2);
        self::assertEquals('FW: Subject', $data->subject);
    }

    public function test_reply(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 13, 0);
        $now = time();
        $data = message_data::new($course, $user1);
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = FORMAT_PLAIN;
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $message = message::create($data);
        $message->send($time1);

        // Reply to sender.

        $data = message_data::reply($message, $user2, false);

        self::assertEquals($user2, $data->sender);
        self::assertEquals($message, $data->reference);
        self::assertEquals($message->course->id, $data->course->id);
        self::assertEqualsCanonicalizing([$user1], $data->to);
        self::assertEqualsCanonicalizing([], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);
        self::assertEquals('RE: Subject', $data->subject);
        self::assertEquals('', $data->content);
        self::assertEquals((int) FORMAT_HTML, $data->format);
        self::assertGreaterThan(0, $data->draftitemid);
        self::assert_draft_files([], $data->draftitemid);
        self::assertGreaterThanOrEqual($now, $data->time);

        // Reply to sender (all).

        $data = message_data::reply($message, $user2, true);

        self::assertEquals($user2, $data->sender);
        self::assertEqualsCanonicalizing([$user1], $data->to);
        self::assertEqualsCanonicalizing([$user3, $user4], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);

        // Reply to self.

        $data = message_data::reply($message, $user1, false);

        self::assertEquals($user1, $data->sender);
        self::assertEqualsCanonicalizing([$user2, $user3], $data->to);
        self::assertEqualsCanonicalizing([], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);

        // Reply to self (all).

        $data = message_data::reply($message, $user1, true);

        self::assertEquals($user1, $data->sender);
        self::assertEqualsCanonicalizing([$user2, $user3], $data->to);
        self::assertEqualsCanonicalizing([$user4], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);

        // Reply to replied message.

        $data = message_data::reply($message, $user2, false);
        $message = message::create($data);
        $message->send($time2);

        $data = message_data::reply($message, $user1, false);

        self::assertEquals($user1, $data->sender);
        self::assertEqualsCanonicalizing([$user2], $data->to);
        self::assertEqualsCanonicalizing([], $data->cc);
        self::assertEqualsCanonicalizing([], $data->bcc);
        self::assertEquals('RE: Subject', $data->subject);
    }

    public function test_new(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user = new user($generator->create_user());
        $now = time();

        $data = message_data::new($course, $user);
        self::assertEquals($user, $data->sender);
        self::assertNull($data->reference);
        self::assertEquals($course, $data->course);
        self::assertEmpty($data->to);
        self::assertEmpty($data->cc);
        self::assertEmpty($data->bcc);
        self::assertEquals('', $data->subject);
        self::assertEquals('', $data->content);
        self::assertEquals((int) FORMAT_HTML, $data->format);
        self::assertGreaterThan(0, $data->draftitemid);
        self::assert_draft_files([], $data->draftitemid);
        self::assertGreaterThanOrEqual($now, $data->time);
    }
}
