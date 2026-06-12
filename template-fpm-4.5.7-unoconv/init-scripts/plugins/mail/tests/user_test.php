<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\user
 */
final class user_test extends test\testcase {
    public function test_can_view_files(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user4->id, $course->id);
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 12, 0);

        // Draft.

        $data1 = message_data::new($course, $user1);
        $data1->subject = 'Subject 1';
        $data1->to = [$user2];
        $data1->cc = [$user3];
        $message1 = message::create($data1);

        self::assertTrue($user1->can_view_files($message1));
        self::assertFalse($user2->can_view_files($message1));
        self::assertFalse($user3->can_view_files($message1));
        self::assertFalse($user4->can_view_files($message1));

        // Sent message.

        $data1 = message_data::draft($message1);
        $data1->course = $course;
        $message1->update($data1);
        $message1->send($time1);

        self::assertTrue($user1->can_view_files($message1));
        self::assertTrue($user2->can_view_files($message1));
        self::assertFalse($user3->can_view_files($message1));
        self::assertFalse($user4->can_view_files($message1));

        // Deleted message.

        $message1->set_deleted($user1, message::DELETED_FOREVER);
        $message1->set_deleted($user2, message::DELETED_FOREVER);

        self::assertFalse($user1->can_view_files($message1));
        self::assertFalse($user2->can_view_files($message1));

        // Reference of a draft.

        $data2 = message_data::reply($message1, $user2, false);
        $data2->to = [$user4];
        $data2->time = $time2;
        $message2 = message::create($data2);

        self::assertFalse($user4->can_view_files($message1));

        // Reference of a sent message.

        $message2->send($time2);

        self::assertTrue($user4->can_view_files($message1));
    }

    public function test_can_view_group(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course(['groupmode' => NOGROUPS]));
        $course2 = new course($generator->create_course(['groupmode' => VISIBLEGROUPS]));
        $course3 = new course($generator->create_course(['groupmode' => SEPARATEGROUPS]));
        $group1 = $generator->create_group(['courseid' => $course1->id]);
        $group2 = $generator->create_group(['courseid' => $course2->id]);
        $group3 = $generator->create_group(['courseid' => $course2->id]);
        $group4 = $generator->create_group(['courseid' => $course3->id]);
        $group5 = $generator->create_group(['courseid' => $course3->id]);

        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course1->id, 'student');
        $generator->enrol_user($user1->id, $course2->id, 'student');
        $generator->enrol_user($user1->id, $course3->id, 'student');
        $generator->enrol_user($user2->id, $course1->id, 'editingteacher');
        $generator->enrol_user($user2->id, $course2->id, 'editingteacher');
        $generator->enrol_user($user2->id, $course3->id, 'editingteacher');
        $generator->enrol_user($user3->id, $course1->id, 'guest');
        $generator->create_group_member(['userid' => $user1->id, 'groupid' => $group1->id]);
        $generator->create_group_member(['userid' => $user2->id, 'groupid' => $group1->id]);
        $generator->create_group_member(['userid' => $user1->id, 'groupid' => $group2->id]);
        $generator->create_group_member(['userid' => $user1->id, 'groupid' => $group4->id]);
        $generator->create_group_member(['userid' => $user2->id, 'groupid' => $group5->id]);
        $generator->create_group_member(['userid' => $user3->id, 'groupid' => $group1->id]);

        // Student in course with no groups.
        $this->assertTrue($user1->can_view_group($course1, 0));
        $this->assertFalse($user1->can_view_group($course1, $group1->id));
        $this->assertFalse($user1->can_view_group($course1, $group2->id));

        // Teacher in course with no groups.
        $this->assertTrue($user2->can_view_group($course1, 0));
        $this->assertFalse($user2->can_view_group($course1, $group1->id));
        $this->assertFalse($user2->can_view_group($course1, $group2->id));

        // Student in course with visible groups.
        $this->assertTrue($user1->can_view_group($course2, 0));
        $this->assertTrue($user1->can_view_group($course2, $group2->id));
        $this->assertTrue($user1->can_view_group($course2, $group3->id));
        $this->assertFalse($user1->can_view_group($course2, $group4->id));

        // Teacher in course with visible groups.
        $this->assertTrue($user2->can_view_group($course2, 0));
        $this->assertTrue($user2->can_view_group($course2, $group2->id));
        $this->assertTrue($user2->can_view_group($course2, $group3->id));
        $this->assertFalse($user2->can_view_group($course2, $group4->id));

        // Student in course with separate groups.
        $this->assertFalse($user1->can_view_group($course3, 0));
        $this->assertTrue($user1->can_view_group($course3, $group4->id));
        $this->assertFalse($user1->can_view_group($course3, $group5->id));

        // Teacher in course with separate groups (access all groups capability).
        $this->assertTrue($user2->can_view_group($course3, 0));
        $this->assertTrue($user2->can_view_group($course3, $group4->id));
        $this->assertTrue($user2->can_view_group($course3, $group5->id));

        // User with no permission.
        $this->assertFalse($user3->can_view_group($course1, $group1->id));
    }

    public function test_can_edit_message(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user2->id, $course1->id);
        $time1 = make_timestamp(2021, 10, 11, 12, 0);
        $time2 = make_timestamp(2021, 10, 11, 12, 0);

        // Draft.

        $data1 = message_data::new($course1, $user1);
        $data1->subject = 'Subject 1';
        $data1->to = [$user2];
        $message1 = message::create($data1);
        self::assertTrue($user1->can_edit_message($message1));
        self::assertFalse($user2->can_edit_message($message1));

        // Sent message.

        $message1->send($time1);
        self::assertFalse($user1->can_edit_message($message1));
        self::assertFalse($user2->can_edit_message($message1));

        // Draft of a course the sender is not enrolled in.

        $data2 = message_data::new($course2, $user1);
        $data2->subject = 'Subject 2';
        $data2->to = [$user2];
        $data2->time = $time2;
        $message2 = message::create($data2);
        self::assertFalse($user1->can_edit_message($message2));
        self::assertFalse($user2->can_edit_message($message2));
    }

    public function test_can_use_mail(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $course3 = new course($generator->create_course(['visible' => false]));
        $course4 = new course($generator->create_course());
        $user = new user($generator->create_user());

        $generator->enrol_user($user->id, $course1->id);
        $generator->enrol_user($user->id, $course3->id, 'student');
        $generator->enrol_user($user->id, $course4->id, 'guest');

        self::assertTrue($user->can_use_mail($course1));
        self::assertFalse($user->can_use_mail($course2));
        self::assertFalse($user->can_use_mail($course3));
        self::assertFalse($user->can_use_mail($course4));
    }

    public function test_can_view_message(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course(['visible' => 0]));
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user2->id, $course1->id);
        $generator->enrol_user($user4->id, $course1->id);
        $generator->enrol_user($user1->id, $course2->id);
        $generator->enrol_user($user2->id, $course2->id, 'editingteacher');
        $generator->enrol_user($user3->id, $course2->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);

        set_config('siteadmins', $user3->id);

        // Draft.

        $data = message_data::new($course1, $user1);
        $data->subject = 'Subject';
        $data->to = [$user2, $user3];
        $data->time = $time;
        $message1 = message::create($data);
        self::assertTrue($user1->can_view_message($message1));
        self::assertFalse($user2->can_view_message($message1));
        self::assertFalse($user3->can_view_message($message1));
        self::assertFalse($user4->can_view_message($message1));

        // Sent message.

        $message1->send($time);
        self::assertTrue($user1->can_view_message($message1));
        self::assertTrue($user2->can_view_message($message1));
        self::assertFalse($user3->can_view_message($message1));
        self::assertFalse($user4->can_view_message($message1));

        // Deleted message.

        $message1->set_deleted($user1, message::DELETED_FOREVER);
        $message1->set_deleted($user2, message::DELETED_FOREVER);
        self::assertFalse($user1->can_view_message($message1));
        self::assertFalse($user2->can_view_message($message1));

        // Sent message in hidden course.

        $data = message_data::new($course2, $user1);
        $data->subject = 'Subject';
        $data->to = [$user2, $user3];
        $data->time = $time;
        $message2 = message::create($data);
        $message2->send($time);
        self::assertFalse($user1->can_view_message($message2));
        self::assertTrue($user2->can_view_message($message2)); // Teacher.
        self::assertTrue($user3->can_view_message($message2)); // Site administrator.
    }

    public function test_current(): void {
        $generator = self::getDataGenerator();
        $record = $generator->create_user();
        self::setUser($record->id);

        $user = user::current();

        self::assertEquals(new user($record), $user);

        // Not logged in.

        self::setUser(null);
        self::assertNull(user::current());
    }

    public function test_fullname(): void {
        $generator = self::getDataGenerator();
        $record = $generator->create_user();
        $user = new user($record);

        self::assertEquals(fullname($record), $user->fullname());

        // Deleted user.
        $user = new user($generator->create_user(['deleted' => 1]));
        self::assertEquals(get_string('deleteduser', 'local_mail'), $user->fullname());
    }

    public function test_get(): void {
        $generator = self::getDataGenerator();
        $record = $generator->create_user();

        $user = user::get($record->id);

        self::assertInstanceOf(user::class, $user);
        self::assertEquals((int) $record->id, $user->id);
        self::assertFalse($user->deleted);
        self::assertEquals($record->firstname, $user->firstname);
        self::assertEquals($record->lastname, $user->lastname);
        self::assertEquals($record->email, $user->email);
        self::assertEquals((int) $record->picture, $user->picture);
        self::assertEquals($record->imagealt, $user->imagealt);
        self::assertEquals($record->firstnamephonetic, $user->firstnamephonetic);
        self::assertEquals($record->lastnamephonetic, $user->lastnamephonetic);
        self::assertEquals($record->middlename, $user->middlename);
        self::assertEquals($record->alternatename, $user->alternatename);

        // Deleted user.

        $record = $generator->create_user(['deleted' => 1]);

        $user = user::get($record->id);

        self::assertInstanceOf(user::class, $user);
        self::assertEquals((int) $record->id, $user->id);
        self::assertTrue($user->deleted);
        self::assertEquals('', $user->firstname);
        self::assertEquals('', $user->lastname);
        self::assertEquals('', $user->email);
        self::assertEquals(0, $user->picture);
        self::assertEquals(null, $user->imagealt);
        self::assertEquals('', $user->firstnamephonetic);
        self::assertEquals('', $user->lastnamephonetic);
        self::assertEquals('', $user->middlename);
        self::assertEquals('', $user->alternatename);

        // Missing user.

        $user = user::get(123);

        self::assertInstanceOf(user::class, $user);
        self::assertEquals(123, $user->id);
        self::assertTrue($user->deleted);
        self::assertEquals('', $user->firstname);
        self::assertEquals('', $user->lastname);
        self::assertEquals('', $user->email);
        self::assertEquals(0, $user->picture);
        self::assertEquals(null, $user->imagealt);
        self::assertEquals('', $user->firstnamephonetic);
        self::assertEquals('', $user->lastnamephonetic);
        self::assertEquals('', $user->middlename);
        self::assertEquals('', $user->alternatename);
    }

    public function test_get_many(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user(['deleted' => 1]));
        $user4 = new user((object) ['id' => 123, 'deleted' => 1]);

        $result = user::get_many([$user3->id, $user1->id, $user3->id, $user2->id, $user3->id, $user4->id]);

        self::assert_array_of_objects([$user3, $user1, $user2, $user4], $result);

        // No IDs.
        self::assertEquals([], user::get_many([]));
    }

    public function test_picture_url(): void {
        global $PAGE;

        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user(['picture' => 123]));
        $user2 = new user($generator->create_user(['picture' => 123, 'deleted' => true]));
        $user3 = new user($generator->create_user());

        // User with picture.
        $userpicture = new \user_picture((object) (array) $user1);
        $url = $userpicture->get_url($PAGE);
        self::assertEquals($url->out(false), $user1->picture_url());

        // Deleted user.
        self::assertEquals('', $user2->picture_url());

        // User with ID 0.
        self::assertEquals('', user::get(0)->picture_url());

        // User without picture.
        self::assertEquals('', $user3->picture_url());
    }

    public function test_profile_url(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        $course = new course($generator->create_course());

        $url = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]);
        self::assertEquals($url->out(false), $user->profile_url($course));

        // Deleted user.
        $user = new user($generator->create_user(['deleted' => 1]));
        self::assertEquals('', $user->profile_url($course));
    }

    public function test_sortorder(): void {
        $generator = self::getDataGenerator();
        $user = new user($generator->create_user(['firstname' => 'Lena', 'lastname' => 'Becker']));

        self::assertEquals(sprintf("0\nBecker\nLena\n%010d", $user->id), $user->sortorder());
    }
}
