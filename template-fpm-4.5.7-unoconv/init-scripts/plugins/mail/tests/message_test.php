<?php
/*
 * SPDX-FileCopyrightText: 2012-2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2014-2021 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2016-2025 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

use local_mail\output\strings;

/**
 * @covers \local_mail\message
 */
final class message_test extends test\testcase {
    public function test_create(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user1, 'Label', 'blue');
        $label2 = label::create($user2, 'Label', 'blue');
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = ' <p> Content of   the  message </p>  ';
        $data->format = (int) FORMAT_HTML;
        $data->time = $time;
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');

        $message = message::create($data);

        self::assertGreaterThan(0, $message->id);
        self::assertEquals($data->course, $message->course);
        self::assertEquals($data->subject, $message->subject);
        self::assertEquals($data->content, $message->content);
        self::assertEquals($data->format, $message->format);
        self::assertEquals(2, $message->attachments);
        self::assertEquals($data->time, $message->time);
        self::assertEquals($user1, $message->sender());
        self::assertEqualsCanonicalizing([$user2, $user3], $message->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([$user4], $message->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([$user5], $message->recipients(message::ROLE_BCC));
        self::assertFalse($message->unread($user1));
        self::assertTrue($message->unread($user2));
        self::assertTrue($message->unread($user3));
        self::assertTrue($message->unread($user4));
        self::assertTrue($message->unread($user5));
        self::assertFalse($message->starred($user1));
        self::assertFalse($message->starred($user2));
        self::assertFalse($message->starred($user3));
        self::assertFalse($message->starred($user4));
        self::assertFalse($message->starred($user5));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user1));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user2));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user3));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user4));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user5));
        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assertEquals([], $message->get_labels($user3));
        self::assertEquals([], $message->get_labels($user4));
        self::assertEquals([], $message->get_labels($user5));
        self::assert_message($message);
        self::assert_attachments(['file1.txt' => 'File 1', 'file2.txt' => 'File 2'], $message);
        self::assertEquals([], $message->get_references());

        // Reference.

        $message->send($time);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);
        $data = message_data::new($message->course, $user2);
        $data->reference = $message;
        $data->to = [$user1];
        $data->time = make_timestamp(2021, 10, 11, 13, 0);

        $message = message::create($data);

        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([$label2], $message->get_labels($user2));
        self::assertEquals([$data->reference->id => $data->reference], $message->get_references());
    }

    public function test_delete_course_data(): void {
        [$users, $messages] = self::generate_random_data(true);

        $course = $messages[0]->course;
        $context = $course->get_context();

        $fs = get_file_storage();

        message::delete_course_data($context);

        self::assert_record_count(0, 'messages', ['courseid' => $course->id]);
        self::assert_record_count(0, 'message_users', ['courseid' => $course->id]);
        self::assert_record_count(0, 'message_labels', ['courseid' => $course->id]);
        foreach ($messages as $message) {
            if ($message->course->id == $course->id) {
                self::assert_record_count(0, 'message_refs', ['messageid' => $message->id]);
                self::assert_record_count(0, 'message_refs', ['reference' => $message->id]);
            } else {
                self::assert_message($message);
            }
        }
        self::assertEmpty($fs->get_area_files($context->id, 'local_mail', 'message'));
    }

    public function test_get(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 13, 0);
        $time3 = make_timestamp(2021, 10, 11, 14, 0);
        $label1 = label::create($user1, 'label1');
        $label2 = label::create($user2, 'label2');
        $data1 = message_data::new($course, $user1);
        $data1->subject = 'Subject 1';
        $data1->content = 'Content 1';
        $data1->format = FORMAT_PLAIN;
        $data1->to = [$user2];
        $data1->cc = [$user3];
        $message1 = message::create($data1);
        $message1->send($time1);
        $data2 = message_data::reply($message1, $user2, true);
        $data2->subject = 'Subject 2';
        $data2->content = 'Content 2';
        $data2->format = FORMAT_MOODLE;
        $message2 = message::create($data2);
        $message2->send($time2);
        $message2->set_labels($user1, [$label1]);
        $message2->set_labels($user2, [$label2]);
        $message2 = message::create($data2);

        self::assertEquals($message1, message::get($message1->id));

        // Missing message.
        try {
            message::get(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_get_many(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 13, 0);
        $time3 = make_timestamp(2021, 10, 11, 14, 0);
        $time4 = make_timestamp(2021, 10, 11, 15, 0);
        $label1 = label::create($user1, 'label1');
        $label2 = label::create($user2, 'label2');
        $data1 = message_data::new($course1, $user1);
        $data1->subject = 'Subject 1';
        $data1->content = 'Content 1';
        $data1->format = FORMAT_PLAIN;
        $data1->to = [$user2];
        $data1->cc = [$user3];
        $message1 = message::create($data1);
        $message1->send($time1);
        $data2 = message_data::reply($message1, $user2, true);
        $data2->subject = 'Subject 2';
        $data2->content = 'Content 2';
        $data2->format = FORMAT_MOODLE;
        $message2 = message::create($data2);
        $message2->send($time2);
        $message2->set_labels($user1, [$label1]);
        $message2->set_labels($user2, [$label2]);
        $data3 = message_data::new($course2, $user2);
        $data3->subject = 'Subject 3';
        $data3->content = 'Content 3';
        $data3->time = $time3;
        $message3 = message::create($data3);
        $data4 = message_data::new($course2, $user3);
        $data4->subject = 'Subject 4';
        $data4->content = 'Content 4';
        $data4->time = $time4;
        $message4 = message::create($data4);

        $result = message::get_many([$message1->id, $message2->id, $message1->id, $message4->id]);

        self::assert_array_of_objects([$message4, $message2, $message1], $result);

        // Missing message.
        try {
            message::get_many([$message1->id, 123, $message2->id]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // No IDs.
        self::assertEquals([], message::get_many([]));
    }

    public function test_get_labels(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $label3 = label::create($user1, 'Label 3');
        $label1 = label::create($user1, 'Label 1');
        $label4 = label::create($user2, 'Label 4');
        $label2 = label::create($user1, 'Label 2');
        $label5 = label::create($user1, 'Label 5');
        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send(time());
        $message->set_labels($user1, [$label3, $label1, $label2]);
        $message->set_labels($user2, [$label4]);

        self::assertEquals([$label1, $label2, $label3], $message->get_labels($user1));
        self::assertEquals([$label4], $message->get_labels($user2));
        self::assertEquals([], $message->get_labels($user3));
    }

    public function test_get_references(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 13, 0);
        $time3 = make_timestamp(2021, 10, 11, 14, 0);

        $data1 = message_data::new($course, $user1);
        $data1->subject = 'Subject 1';
        $data1->content = 'Content 1';
        $data1->to = [$user2];
        $message1 = message::create($data1);
        $message1->send($time1);

        $data2 = message_data::reply($message1, $user2, true);
        $data2->subject = 'Subject 2';
        $data2->content = 'Content 2';
        $message2 = message::create($data2);
        $message2->send($time2);

        $data3 = message_data::reply($message2, $user2, false);
        $data3->to = [$user3];
        $message3 = message::create($data3);
        $message3->send($time2);

        $data4 = message_data::reply($message3, $user3, false);
        $data4->to = [$user2];
        $message4 = message::create($data4);
        $message4->send($time3);

        $data5 = message_data::reply($message4, $user2, false);
        $data5->to = [$user3];
        $message5 = message::create($data5);
        $message5->send($time3);

        self::assert_array_of_objects([], $message1->get_references());
        self::assert_array_of_objects([$message1], $message2->get_references());
        self::assert_array_of_objects([$message2, $message1], $message3->get_references());
        self::assert_array_of_objects([$message3, $message2, $message1], $message4->get_references());
        self::assert_array_of_objects([$message4, $message3, $message2, $message1], $message5->get_references());
        self::assert_array_of_objects([$message3, $message2], $message5->get_references(false, 1, 2));
        self::assert_array_of_objects([], $message5->get_references(true));
        self::assert_array_of_objects([$message5], $message4->get_references(true));
        self::assert_array_of_objects([$message5, $message4], $message3->get_references(true));
        self::assert_array_of_objects([$message5, $message4, $message3], $message2->get_references(true));
        self::assert_array_of_objects([$message5, $message4, $message3, $message2], $message1->get_references(true));
        self::assert_array_of_objects([$message4, $message3], $message1->get_references(true, 1, 2));
    }

    public function test_has_label(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user = new user($generator->create_user());
        $label1 = label::create($user, 'Label 1');
        $label2 = label::create($user, 'Label 2');
        $label3 = label::create($user, 'Label 3');
        $data = message_data::new($course, $user);
        $message = message::create($data);
        $message->set_labels($user, [$label1, $label2]);

        self::assertTrue($message->has_label($label1));
        self::assertTrue($message->has_label($label2));
        self::assertFalse($message->has_label($label3));
    }

    public function test_has_recipient(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->cc = [$user3];
        $data->bcc = [$user4];
        $message = message::create($data);

        self::assertFalse($message->has_recipient($user1));
        self::assertTrue($message->has_recipient($user2));
        self::assertTrue($message->has_recipient($user3));
        self::assertTrue($message->has_recipient($user4));
        self::assertFalse($message->has_recipient($user5));
    }

    public function test_normalize_text(): void {
        self::assertEquals('', message::normalize_text('', FORMAT_PLAIN));
        self::assertEquals('text', message::normalize_text('   text   ', FORMAT_PLAIN));
        self::assertEquals('text text', message::normalize_text('text     text', FORMAT_PLAIN));
        self::assertEquals('text text', message::normalize_text('text😛😛text', FORMAT_PLAIN));
        self::assertEquals('text text', message::normalize_text('text @@PLUGINFILE@@/ text', FORMAT_HTML));
        self::assertEquals('text text', message::normalize_text(' <p> text    text </p>', FORMAT_HTML));
    }

    public function test_recipients(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());

        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $message = message::create($data);

        // All recipients.
        $recipients = [$user2, $user3, $user4, $user5];
        \core_collator::asort_objects_by_method($recipients, 'sortorder');
        self::assertEquals(array_values($recipients), $message->recipients());

        // To recipients.
        $recipients = [$user2, $user3];
        \core_collator::asort_objects_by_method($recipients, 'sortorder');
        self::assertEquals(array_values($recipients), $message->recipients(message::ROLE_TO));

        // Cc recipients.
        $recipients = [$user4];
        \core_collator::asort_objects_by_method($recipients, 'sortorder');
        self::assertEquals(array_values($recipients), $message->recipients(message::ROLE_CC));

        // Bcc recipients.
        $recipients = [$user5];
        \core_collator::asort_objects_by_method($recipients, 'sortorder');
        self::assertEquals(array_values($recipients), $message->recipients(message::ROLE_BCC));

        // To and Bcc recipients.
        $recipients = [$user2, $user3, $user4];
        \core_collator::asort_objects_by_method($recipients, 'sortorder');
        self::assertEquals(array_values($recipients), $message->recipients(message::ROLE_TO, message::ROLE_CC));
    }

    public function test_role(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());

        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $message = message::create($data);

        self::assertEquals(message::ROLE_FROM, $message->role($user1));
        self::assertEquals(message::ROLE_TO, $message->role($user2));
        self::assertEquals(message::ROLE_TO, $message->role($user3));
        self::assertEquals(message::ROLE_CC, $message->role($user4));
        self::assertEquals(message::ROLE_BCC, $message->role($user5));
    }

    public function test_role_names(): void {
        $expected = [
            message::ROLE_FROM => 'from',
            message::ROLE_TO => 'to',
            message::ROLE_CC => 'cc',
            message::ROLE_BCC => 'bcc',
        ];
        self::assertEquals($expected, message::role_names());
    }

    public function test_send(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $label1 = label::create($user1, 'Label 1', 'blue');
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 13, 0);
        $time3 = make_timestamp(2021, 10, 11, 14, 0);
        $time4 = make_timestamp(2021, 10, 11, 15, 0);
        $data1 = message_data::new($course, $user1);
        $data1->subject = 'subject';
        $data1->content = 'content';
        $data1->format = FORMAT_PLAIN;
        $data1->time = $time1;
        $data1->to = [$user2];
        $data1->cc = [$user3];
        $data1->bcc = [$user4];
        $message1 = message::create($data1);

        // Send message without references.

        $message1->send($time2);

        self::assertFalse($message1->draft);
        self::assertEquals($time2, $message1->time);
        self::assert_message($message1);

        // Send message with references.

        $message1->set_labels($user1, [$label1]);
        $data2 = message_data::reply($message1, $user2, false);
        $message2 = message::create($data2);
        $message2->send($time4);

        self::assertFalse($message2->draft);
        self::assertEquals($time4, $message2->time);
        self::assertEquals([$label1], $message2->get_labels($user1));
        self::assertEquals([], $message2->get_labels($user2));
        self::assert_message($message2);
    }

    public function test_sender(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $message = message::create($data);

        self::assertEquals($user1, $message->sender());
    }

    public function test_set_deleted(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label1 = label::create($user1, 'Label 1');
        $label2 = label::create($user2, 'Label 2');
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $data = message_data::new($course, $user1);
        $data->subject = 'subject';
        $data->content = 'content';
        $data->format = FORMAT_HTML;
        $data->to = [$user2];
        self::create_draft_file($data->draftitemid, 'file.txt', 'File');

        // Delete message.

        $message = message::create($data);
        $message->send($time);
        $message->set_unread($user1, true);
        $message->set_unread($user2, true);
        $message->set_starred($user1, true);
        $message->set_starred($user2, true);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);
        $message->set_deleted($user2, message::DELETED);

        self::assertEquals(message::NOT_DELETED, $message->deleted($user1));
        self::assertEquals(message::DELETED, $message->deleted($user2));
        self::assertTrue($message->unread($user1));
        self::assertTrue($message->unread($user2));
        self::assertTrue($message->starred($user1));
        self::assertTrue($message->starred($user2));
        self::assertEquals([$label1], $message->get_labels($user1));
        self::assertEquals([$label2], $message->get_labels($user2));
        self::assertEquals('subject', $message->subject);
        self::assertEquals('content', $message->content);
        self::assertEquals(FORMAT_HTML, $message->format);
        self::assertEquals(1, $message->attachments);
        self::assert_attachments(['file.txt' => 'File'], $message);
        self::assert_message($message);

        // Restore deleted message.

        $message = message::create($data);
        $message->send($time);
        $message->set_unread($user1, true);
        $message->set_unread($user2, true);
        $message->set_starred($user1, true);
        $message->set_starred($user2, true);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);
        $message->set_deleted($user2, message::NOT_DELETED);

        self::assertEquals(message::NOT_DELETED, $message->deleted($user2));
        self::assert_message($message);

        // Delete sent message forever.

        $message = message::create($data);
        $message->send($time);
        $message->set_unread($user1, true);
        $message->set_unread($user2, true);
        $message->set_starred($user1, true);
        $message->set_starred($user2, true);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);
        $message->set_deleted($user1, message::DELETED_FOREVER);

        self::assertEquals(message::DELETED_FOREVER, $message->deleted($user1));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user2));
        self::assertFalse($message->unread($user1));
        self::assertTrue($message->unread($user2));
        self::assertFalse($message->starred($user1));
        self::assertTrue($message->starred($user2));
        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([$label2], $message->get_labels($user2));
        self::assertEquals('subject', $message->subject);
        self::assertEquals('content', $message->content);
        self::assertEquals(FORMAT_HTML, $message->format);
        self::assertEquals(1, $message->attachments);
        self::assert_attachments(['file.txt' => 'File'], $message);
        self::assert_message($message);

        // Delete received message forever.

        $message = message::create($data);
        $message->send($time);
        $message->set_unread($user1, true);
        $message->set_unread($user2, true);
        $message->set_starred($user1, true);
        $message->set_starred($user2, true);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);
        $message->set_deleted($user2, message::DELETED_FOREVER);

        self::assertEquals(message::NOT_DELETED, $message->deleted($user1));
        self::assertEquals(message::DELETED_FOREVER, $message->deleted($user2));
        self::assertTrue($message->unread($user1));
        self::assertFalse($message->unread($user2));
        self::assertTrue($message->starred($user1));
        self::assertFalse($message->starred($user2));
        self::assertEquals([$label1], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assertEquals('subject', $message->subject);
        self::assertEquals('content', $message->content);
        self::assertEquals(FORMAT_HTML, $message->format);
        self::assertEquals(1, $message->attachments);
        self::assert_attachments(['file.txt' => 'File'], $message);
        self::assert_message($message);

        // Delete draft forever.

        $draft = message::create($data);
        $draft->set_labels($user1, [$label1]);

        $draft->set_deleted($user1, message::DELETED_FOREVER);

        self::assert_record_count(0, 'messages', ['id' => $draft->id]);
        self::assert_record_count(0, 'message_refs', ['messageid' => $draft->id]);
        self::assert_record_count(0, 'message_users', ['messageid' => $draft->id]);
        self::assert_record_count(0, 'message_labels', ['messageid' => $draft->id]);
        self::assert_attachments([], $draft);
        self::assertEquals(message::DELETED_FOREVER, $draft->deleted($user1));
        self::assertEquals([], $draft->get_labels($user1));
        self::assertEquals(strings::get('deletedmessagesubject'), $draft->subject);
        self::assertEquals(strings::get('deletedmessagecontent'), $draft->content);
        self::assertEquals(FORMAT_PLAIN, $draft->format);
        self::assertEquals(0, $draft->attachments);

        // Delete content data of sent message.

        $message = message::create($data);
        $message->send($time);
        $message->set_starred($user1, true);
        $message->set_unread($user1, true);
        $message->set_starred($user2, true);
        $message->set_unread($user2, true);
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);

        $message->set_deleted($user1, message::DELETED_CONTENT);

        self::assertEquals(message::DELETED_CONTENT, $message->deleted($user1));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user2));
        self::assertFalse($message->unread($user1));
        self::assertTrue($message->unread($user2));
        self::assertFalse($message->starred($user1));
        self::assertTrue($message->starred($user2));
        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([$label2], $message->get_labels($user2));
        self::assertEquals(strings::get('deletedmessagesubject'), $message->subject);
        self::assertEquals(strings::get('deletedmessagecontent'), $message->content);
        self::assertEquals(FORMAT_PLAIN, $message->format);
        self::assertEquals(0, $message->attachments);
        self::assert_attachments([], $message);
        self::assert_message($message);

        // Delete content of draft.

        $draft = message::create($data);
        $draft->set_labels($user1, [$label1]);

        $draft->set_deleted($user1, message::DELETED_CONTENT);

        self::assert_record_count(0, 'messages', ['id' => $draft->id]);
        self::assert_record_count(0, 'message_refs', ['messageid' => $draft->id]);
        self::assert_record_count(0, 'message_users', ['messageid' => $draft->id]);
        self::assert_record_count(0, 'message_labels', ['messageid' => $draft->id]);
        self::assert_attachments([], $draft);
        self::assertEquals(message::DELETED_CONTENT, $draft->deleted($user1));
        self::assertEquals([], $draft->get_labels($user1));
        self::assertEquals(strings::get('deletedmessagesubject'), $draft->subject);
        self::assertEquals(strings::get('deletedmessagecontent'), $draft->content);
        self::assertEquals(FORMAT_PLAIN, $draft->format);
        self::assertEquals(0, $draft->attachments);
    }

    public function test_set_labels(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $label1 = label::create($user1, 'Label 1', 'blue');
        $label2 = label::create($user1, 'Label 2', 'red');
        $label3 = label::create($user1, 'Label 3', 'green');
        $label4 = label::create($user2, 'Label 4', 'purple');
        $data = message_data::new($course, $user1);
        $data->subject = 'subject';
        $data->to = [$user2];
        $message = message::create($data);
        $message->send($time);

        $message->set_labels($user1, [$label1, $label2]);

        self::assertEquals([$label1, $label2], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assert_message($message);

        $message->set_labels($user1, [$label2, $label3]);
        self::assertEquals([$label2, $label3], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assert_message($message);

        $message->set_labels($user2, [$label4]);
        self::assertEquals([$label2, $label3], $message->get_labels($user1));
        self::assertEquals([$label4], $message->get_labels($user2));
        self::assert_message($message);

        $message->set_labels($user1, []);
        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([$label4], $message->get_labels($user2));
        self::assert_message($message);

        $message->set_labels($user2, []);
        self::assertEquals([], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assert_message($message);
    }

    public function test_set_starred(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label = label::create($user2, 'label');
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $data = message_data::new($course, $user1);
        $data->subject = 'subject';
        $data->to = [$user2];
        $message = message::create($data);
        $message->send($time);
        $message->set_labels($user2, [$label]);

        // Set starred.

        $message->set_starred($user2, true);

        self::assertTrue($message->starred($user2));
        self::assert_message($message);

        // Set unstarred.

        $message->set_starred($user2, false);

        self::assertFalse($message->starred($user2));
        self::assert_message($message);
    }

    public function test_set_unread(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label = label::create($user2, 'label');
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $data = message_data::new($course, $user1);
        $data->subject = 'subject';
        $data->to = [$user2];
        $message = message::create($data);
        $message->send($time);
        $message->set_labels($user2, [$label]);

        // Set unread.

        $message->set_unread($user2, false);

        self::assertFalse($message->unread($user2));
        self::assert_message($message);

        // Set read.

        $message->set_unread($user2, true);

        self::assertTrue($message->unread($user2));
        self::assert_message($message);
    }

    public function test_update(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $label1 = label::create($user1, 'Label 1');
        $label2 = label::create($user1, 'Label 2');

        $data = message_data::new($course1, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = make_timestamp(2021, 10, 11, 12, 0);
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message = message::create($data);
        $message->set_labels($user1, [$label1, $label2]);
        $message->set_deleted($user1, message::DELETED);

        $data = message_data::draft($message);
        $data->course = $course2;
        $data->to = [$user2];
        $data->cc = [$user3];
        $data->bcc = [$user5, $user2, $user3, $user1];
        $data->subject = 'Updated subject';
        $data->content = '    <p>   Updated     content   </p>  ';
        $data->format = (int) FORMAT_HTML;
        $data->time = make_timestamp(2021, 10, 11, 13, 0);

        self::delete_draft_files($data->draftitemid);
        self::create_draft_file($data->draftitemid, 'file3.txt', 'File 3');

        $message->update($data);

        self::assertGreaterThan(0, $message->id);
        self::assertEquals($data->course, $message->course);
        self::assertEquals($data->subject, $message->subject);
        self::assertEquals($data->content, $message->content);
        self::assertEquals($data->format, $message->format);
        self::assertEquals(1, $message->attachments);
        self::assertEquals($data->time, $message->time);
        self::assertEquals($user1, $message->sender());
        self::assertEqualsCanonicalizing([$user2], $message->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([$user3], $message->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([$user5], $message->recipients(message::ROLE_BCC));
        self::assertFalse($message->unread($user1));
        self::assertTrue($message->unread($user2));
        self::assertTrue($message->unread($user3));
        self::assertTrue($message->unread($user5));
        self::assertFalse($message->starred($user1));
        self::assertFalse($message->starred($user2));
        self::assertFalse($message->starred($user3));
        self::assertFalse($message->starred($user5));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user1));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user2));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user3));
        self::assertEquals(message::NOT_DELETED, $message->deleted($user5));
        self::assertEquals([$label1, $label2], $message->get_labels($user1));
        self::assertEquals([], $message->get_labels($user2));
        self::assertEquals([], $message->get_labels($user3));
        self::assertEquals([], $message->get_labels($user5));
        self::assert_message($message);
        self::assert_attachments(['file3.txt' => 'File 3'], $message);
        self::assertEquals([], $message->get_references());

        // Subject longer than 100 characters.

        $data->subject = str_repeat('X', 95) . 'ABCDEF';

        $message->update($data);

        self::assertEquals(str_repeat('X', 95) . 'AB...', $message->subject);
        self::assert_message($message);

        // Course of replies cannot be changed.

        $message->send($data->time);
        $data = message_data::reply($message, $user2, false);
        $reply = message::create($data);
        $data = message_data::draft($reply);
        $data->course = $course1;
        $reply->update($data);
        self::assertEquals($course2, $reply->course);
        self::assert_message($reply);
    }
}
