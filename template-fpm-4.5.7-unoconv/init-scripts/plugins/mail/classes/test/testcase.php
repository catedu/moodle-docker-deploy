<?php
/*
 * SPDX-FileCopyrightText: 2012-2013 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2014-2021 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail\test;

use local_mail\course;
use local_mail\label;
use local_mail\message;
use local_mail\message_data;
use local_mail\message_search;
use local_mail\user;
use local_mail\user_search;
use local_mail\output\strings;

abstract class testcase extends \advanced_testcase {
    public function setUp(): void {
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->setAdminUser();
        course::cache()->purge();
        label::cache()->purge();
    }

    /**
     * Asserts that an array of objects has the correct values and is indexed by the id property.
     *
     * @param mixed[] $expected Expected array of objects in the given order.
     * @param mixed[] $actual Actual array.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_array_of_objects(array $expected, array $actual, string $message = '') {
        $ids = array_column($expected, 'id');

        self::assertEquals(
            array_combine($ids, $expected),
            $actual,
            'Array of objects with incorrect items.' . ($message ? "\n$message" : ''),
        );
        self::assertEquals(
            $ids,
            array_keys($actual),
            'Array of objects with incorrect order.' . ($message ? "\n$message" : ''),
        );
    }

    /**
     * Assert that a language strings exists.
     *
     * @param string $identifier String identifier.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_string_exists(string $identifier): void {
        self::assertTrue(
            get_string_manager()->string_exists($identifier, 'local_mail'),
            "String '$identifier' does not exist."
        );
    }

    /**
     * Asserts stored attachments.
     *
     * @param string[] $expected Files: filename => content.
     * @param message $message Message.
     * @param string $component Component.
     * @param string $filearea File area.
     * @param string $itemid Item ID.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_attachments(array $expected, message $message) {
        $fs = get_file_storage();
        $contextid = $message->course->get_context()->id;
        $files = $fs->get_area_files($contextid, 'local_mail', 'message', $message->id, 'id', false);
        $actual = [];
        foreach ($files as $file) {
            $actual[$file->get_filename()] = $file->get_content();
        }
        self::assertEquals($expected, $actual);
    }

    /**
     * Asserts stored files.
     *
     * @param string[] $expected Files: filename => content.
     * @param int $userid Draft item ID.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_draft_files(array $expected, int $draftitemid) {
        global $USER;

        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        $actual = [];
        foreach ($fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id', false) as $file) {
            $actual[$file->get_filename()] = $file->get_content();
        }
        self::assertEquals($expected, $actual);
    }

    /**
     * Asserts that a message is stored correctly in the database.
     *
     * @param message $message Message.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_message(message $message): void {
        $deleted = $message->deleted($message->sender()) == message::DELETED_CONTENT;
        self::assert_record_data('messages', [
            'id' => $message->id,
        ], [
            'courseid' => $message->course->id,
            'subject' => $deleted ? '' : $message->subject,
            'content' => $deleted ? '' : $message->content,
            'format' => $message->format,
            'attachments' => $message->attachments,
            'draft' => (int) $message->draft,
            'time' => $message->time,
            'normalizedsubject' => $deleted ? '' : message::normalize_text($message->subject, FORMAT_PLAIN),
            'normalizedcontent' => $deleted ? '' : message::normalize_text($message->content, $message->format),
        ]);

        $numusers = count($message->recipients()) + 1;
        self::assert_record_count($numusers, 'message_users', ['messageid' => $message->id]);

        $numlabels = count($message->get_labels($message->sender()));
        foreach ($message->recipients() as $user) {
            $numlabels += count($message->get_labels($user));
        }
        self::assert_record_count($numlabels, 'message_labels', ['messageid' => $message->id]);

        foreach ([$message->sender(), ...$message->recipients()] as $user) {
            $data = [
                'courseid' => $message->course->id,
                'draft' => (int) $message->draft,
                'time' => $message->time,
                'role' => $message->role($user),
                'unread' => (int) $message->unread($user),
                'starred' => (int) $message->starred($user),
                'deleted' => $message->deleted($user),
            ];
            self::assert_record_data('message_users', [
                'messageid' => $message->id,
                'userid' => $user->id,
            ], $data);
            foreach ($message->get_labels($user) as $label) {
                self::assert_record_data('message_labels', [
                    'messageid' => $message->id,
                    'labelid' => $label->id,
                ], $data);
            }
        }
    }

    /**
     * Asserts that sink eventc contains an event that matches a name and message.
     *
     * @param string $eventname Expected event name.
     * @param message $message Expected Message.
     * @param \phpunit_event_sink $sink Event sink.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_message_event(string $eventname, message $message, \phpunit_event_sink $sink): void {
        global $USER;

        $events = array_filter(
            $sink->get_events(),
            fn (\core\event\base $event) =>  $event->eventname != '\core\event\notification_viewed'
        );

        self::assertEquals(1, count($events));
        self::assertEquals($eventname, $events[0]->eventname);
        self::assertEquals($USER->id, $events[0]->userid);
        self::assertEquals($message->id, $events[0]->objectid);
        if ($message->draft) {
            self::assertEquals(0, $events[0]->courseid);
            self::assertEquals(\context_user::instance($USER->id)->id, $events[0]->contextid);
        } else {
            self::assertEquals($message->course->id, $events[0]->courseid);
            self::assertEquals($message->course->get_context()->id, $events[0]->contextid);
        }

        $sink->close();
    }

    /**
     * Asserts that the table contains this number of records matching the conditions.
     *
     * @param int $expected Expected number of rows.
     * @param string $table Table name without the "local_mail_" prefix.
     * @param mixed[] $conditions Array of field => value.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_record_count(int $expected, string $table, array $conditions = []) {
        global $DB;

        $actual = $DB->count_records('local_mail_' . $table, $conditions);

        self::assertEquals($expected, $actual);
    }

    /**
     * Asserts that the table contains a record matching the givem conditions and data.
     *
     * @param string $table Table name without the "local_mail_" prefix.
     * @param mixed[] $conditions Array of field => value.
     * @param mixed[] $data Array of field => value.
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected static function assert_record_data($table, array $conditions, array $data): void {
        global $DB;

        $records = $DB->get_records('local_mail_' . $table, $conditions);

        self::assertCount(1, $records);

        foreach ($records as $record) {
            $actualdata = [];
            foreach (array_keys($data) as $field) {
                $actualdata[$field] = $record->$field;
            }
            self::assertEquals($data, $actualdata);
        }
    }

    /**
     * Creates a draft stored file.
     *
     * @param int $draftitemid Draft item ID.
     * @param string $filename File name.
     * @param string $content Content of the file.
     * @return \stored_file
     */
    protected static function create_draft_file(int $draftitemid, string $filename, string $content): \stored_file {
        global $USER;

        $fs = get_file_storage();

        $context = \context_user::instance($USER->id);

        $record = [
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => $filename,
        ];

        return $fs->create_file_from_string($record, $content);
    }

    /**
     * Deletes draft stored files.
     *
     * @param int $draftitemid Draft item ID.
     */
    protected static function delete_draft_files(int $draftitemid) {
        global $USER;

        $fs = get_file_storage();

        $context = \context_user::instance($USER->id);

        return $fs->delete_area_files($context->id, 'user', 'draft', $draftitemid);
    }


    /**
     * Generates data for searching messages.
     *
     * @param bool $withmessages
     * @return array Array users and messages.
     */
    public static function generate_random_data(bool $withmessages): array {
        global $DB;

         /* Constants used for generating random mail data. */
        $numcourses = $withmessages ? 5 : 3;
        $numgroupspercourse = 3;
        $numusers = $withmessages ? 10 : 50;
        $numdeletedusers = $numusers / 5;
        $numlabelsperuser = 3;
        $nummessages = $withmessages ? 1000 : 0;
        $replyfreq = 0.5;
        $draftfreq = 0.2;
        $recipientfreq = 0.2;
        $unreadfreq = 0.2;
        $starredfreq = 0.2;
        $deletedfreq = 0.2;
        $deletedforeverfreq = 0.1;
        $deletedcontentfreq = 0.1;
        $attachmentfreq = 0.2;
        $inctimefreq = 0.9;
        $words = [
            'Xiuxiuejar', 'Aixopluc', 'Caliu', 'Tendresa', 'Llibertat',
            'Moixaina', 'Amanyagar', 'Enraonar', 'Ginesta', 'Atzavara', 'Paral·lel',
        ];

        $generator = self::getDataGenerator();

        $courses = [];
        $users = [];
        $roleids = [];
        $groupids = [];
        $userlabels = [];
        $messages = [];
        $sentmessages = [];
        $time = make_timestamp(2021, 10, 11, 12, 0);

        // Generate roles.
        foreach (get_roles_with_capability('local/mail:usemail') as $role) {
            unassign_capability('local/mail:usemail', $role->id);
            unassign_capability('local/mail:mailsamerole', $role->id);
        }
        $rolecaps = [
            [],
            ['local/mail:usemail' => 'allow'],
            ['local/mail:usemail' => 'allow', 'local/mail:mailsamerole' => 'allow'],
        ];
        foreach ($rolecaps as $caps) {
            $roleid = $generator->create_role();
            $generator->create_role_capability($roleid, $caps, \context_system::instance());
            $roleids[] = $roleid;
        }

        // Generate courses and group.
        for ($i = 0; $i < $numcourses; $i++) {
            $groupmode = $i == 0 ? VISIBLEGROUPS : ($i == 1 ? SEPARATEGROUPS : NOGROUPS);
            $course = new course($generator->create_course(['groupmode' => $groupmode]));
            $courses[] = $course;
            $groupids[$course->id] = [0];
            for ($j = 0; $j < $numgroupspercourse; $j++) {
                $group = $generator->create_group(['courseid' => $course->id]);
                $groupids[$course->id][] = $group->id;
            }
        }

        // Generate users.
        for ($i = 0; $i < $numusers; $i++) {
            $user = new user($generator->create_user());
            $userlabels[$user->id] = [];

            // One user with no courses and no labels.
            if ($i > 0) {
                // Enrol user to some courses.
                foreach (self::random_items($courses, count($courses) - 1) as $course) {
                    $roleid = self::random_item($roleids);
                    $generator->enrol_user($user->id, $course->id, $roleid);

                    // Add user to a group.
                    $groupid = self::random_item($groupids[$course->id]);
                    if ($groupid) {
                        $generator->create_group_member(['userid' => $user->id, 'groupid' => $groupid]);
                    }
                }

                // Create some labels.
                foreach (self::random_items($words, $numlabelsperuser) as $name) {
                    $userlabels[$user->id][] = label::create($user, $name);
                }

                // Create one label with no messages.
                $userlabels[$user->id] = array_slice($userlabels[$user->id], 1);

                // Mark some users as deleted.
                if ($i >= $numusers - $numdeletedusers) {
                    $DB->set_field('user', 'deleted', 1, ['id' => $user->id]);
                    $user = new user((object) ['id' => $user->id, 'deleted' => 1]);
                }
            }

            $users[] = $user;
        }

        // Sort users.
        \core_collator::asort_objects_by_method($users, 'sortorder');

        // One user and one course with no messages.
        $participants = array_slice($users, 0, count($users) - 1);
        $courses = array_slice($courses, 1);

        // Generate messages.
        for ($i = 0; $i < $nummessages; $i++) {
            $transaction = $DB->start_delegated_transaction();

            if (self::random_bool($inctimefreq)) {
                $time++;
            }

            if (count($sentmessages) > 0 && self::random_bool($replyfreq)) {
                $reference = self::random_item($sentmessages);
                $sender = self::random_item($reference->recipients());
                $data = message_data::reply($reference, $sender, false);
            } else {
                $data = message_data::new(self::random_item($courses), self::random_item($participants));
            }

            if (self::random_bool($attachmentfreq)) {
                $filename = self::random_item($words) . '.txt';
                $content = self::random_item($words) . ' ' . self::random_item($words);
                self::create_draft_file($data->draftitemid, $filename, $content);
            }

            $data->subject = self::random_item($words);
            $data->content = ' <p> ' . self::random_item($words) . '   ' . self::random_item($words) . ' </p> ';
            $data->format = FORMAT_HTML;
            $data->time = $time;

            if ($data->course) {
                foreach ($participants as $user) {
                    if ($user->id != $data->sender->id && self::random_bool($recipientfreq)) {
                        $rolename = self::random_item(['to', 'cc', 'bcc']);
                        $data->{$rolename}[] = $user;
                    }
                }
            }

            $message = message::create($data);

            $message->set_starred($data->sender, self::random_bool($starredfreq));
            $message->set_labels($data->sender, self::random_items($userlabels[$data->sender->id]));

            $messages[] = $message;

            if (!self::random_bool($draftfreq) && $message->recipients()) {
                $message->send($time);
                $sentmessages[] = $message;

                $message->set_unread($data->sender, self::random_bool($unreadfreq));

                foreach ([$data->sender, ...$message->recipients()] as $user) {
                    $message->set_unread($user, self::random_bool($unreadfreq));
                    if ($user->id != $data->sender->id) {
                        $message->set_starred($user, self::random_bool($starredfreq));
                        $message->set_labels($user, self::random_items($userlabels[$user->id]));
                    }
                    if (self::random_bool($deletedfreq)) {
                        $message->set_deleted($user, message::DELETED);
                    } else if (self::random_bool($deletedforeverfreq)) {
                        $message->set_deleted($user, message::DELETED_FOREVER);
                    }
                }

                if (self::random_bool($deletedcontentfreq)) {
                    $message->set_deleted($data->sender, message::DELETED_CONTENT);
                }
            }

            $transaction->allow_commit();
        }

        course::cache()->purge();
        label::cache()->purge();

        return [$users, $messages];
    }

    /**
     * Returns different message search casses for the givem users and messages.
     *
     * @param user[] $users All users.
     * @param message[] $messages All messages.
     * @return message_search[] Array of search parameters.
     */
    public static function messages_search_cases(array $users, array $messages): array {
        $cases = [];

        foreach (array_slice($users, 0, 10) as $user) {
            // All messages.
            $cases[] = new message_search($user);

            // Inbox.
            $search = new message_search($user);
            $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
            $cases[] = $search;

            // Unread.
            $search = new message_search($user);
            $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
            $search->unread = true;
            $cases[] = $search;

            // Starred.
            $search = new message_search($user);
            $search->starred = true;
            $cases[] = $search;

            // Sent.
            $search = new message_search($user);
            $search->draft = false;
            $search->roles = [message::ROLE_FROM];
            $cases[] = $search;

            // Drafts.
            $search = new message_search($user);
            $search->draft = true;
            $search->roles = [message::ROLE_FROM];
            $cases[] = $search;

            // Trash.
            $search = new message_search($user);
            $search->deleted = true;
            $cases[] = $search;

            // Course.
            foreach (course::get_by_user($user) as $course) {
                $search = new message_search($user);
                $search->course = $course;
                $cases[] = $search;
            }

            // Label.
            foreach (label::get_by_user($user) as $label) {
                $search = new message_search($user);
                $search->label = $label;
                $cases[] = $search;
            }

            // Content.
            $search = new message_search($user);
            do {
                $search->content = self::random_item($messages)->subject;
            } while ($search->content == strings::get('deletedmessagesubject'));

            $cases[] = $search;

            // Sender name.
            $search = new message_search($user);
            do {
                $sender = self::random_item($users);
            } while ($sender->deleted);
            $search->sendername = $sender->fullname();
            $cases[] = $search;

            // Recipient name.
            $search = new message_search($user);
            do {
                $recipient = self::random_item($users);
            } while ($recipient->deleted);
            $search->recipientname = $recipient->fullname();
            $cases[] = $search;

            // With files only.
            $search = new message_search($user);
            $search->withfilesonly = true;
            $cases[] = $search;

            // Max time.
            $search = new message_search($user);
            $search->maxtime = self::random_item($messages)->time;
            $cases[] = $search;

            // Start message.
            $search = new message_search($user);
            $search->startid = self::random_item($messages)->id;
            $cases[] = $search;

            // Stop message.
            $search = new message_search($user);
            $search->stopid = self::random_item($messages)->id;
            $cases[] = $search;

            // Reverse.
            $search = new message_search($user);
            $search->reverse = true;
            $cases[] = $search;

            // Start and reverse.
            $search = new message_search($user);
            $search->startid = self::random_item($messages)->id;
            $search->reverse = true;
            $cases[] = $search;

            // Stop and reverse.
            $search = new message_search($user);
            $search->stopid = self::random_item($messages)->id;
            $search->reverse = true;
            $cases[] = $search;

            // Impossible search, always results in no messages.
            $search = new message_search($user);
            $search->roles = [message::ROLE_TO];
            $search->draft = true;
            $cases[] = $search;
        }

        return $cases;
    }

    /**
     * Returns different search casses for the givem users.
     *
     * @param user[] $users All users.
     * @return user_search[] Array of search parameters.
     */
    public static function user_search_cases(array $users): array {
        $cases = [];

        foreach ($users as $user) {
            foreach (course::get_by_user($user) as $course) {
                // All users.
                $cases[] = new user_search($user, $course);

                // Roles.
                foreach (array_keys($course->get_viewable_roles($user)) as $roleid) {
                    $search = new user_search($user, $course);
                    $search->roleid = $roleid;
                    $cases[] = $search;
                }

                // Groups.
                foreach (array_keys(groups_get_all_groups($course->id, 0)) as $groupid) {
                    $search = new user_search($user, $course);
                    $search->groupid = $groupid;
                    $cases[] = $search;
                }

                // Full name.
                $search = new user_search($user, $course);
                while ($search->fullname === '') {
                    $search->fullname = self::random_item($users)->firstname;
                }
                $cases[] = $search;

                // Include.
                $search = new user_search($user, $course);
                $search->include = array_column(self::random_items($users, count($users) / 2), 'id');
                $cases[] = $search;
            }
        }

        return $cases;
    }

    /**
     * Returns a random item of an array.
     *
     * @param mixed[] $items Array of items
     * @return mixed
     */
    protected static function random_item(array $items) {
        return $items ? $items[array_rand($items)] : null;
    }

    /**
     * Returns random items of an array.
     *
     * @param mixed[] $items Array of items.
     * @param int $min Minimum number of items.
     * @param int $max Maximum number of items.
     * @return mixed[]
     */
    protected static function random_items(array $items, int $min = 0, int $max = 0): array {
        assert($min >= 0 && $max >= 0 && (!$max || $max >= $min));
        $min = min($min, count($items) - 1);
        $max = $max ?: count($items) - 1;
        $num = rand($min, $max);
        if (!$items || $num <= 0) {
            return [];
        } else if ($num == 1) {
            return [self::random_item($items)];
        } else {
            $keys = array_rand($items, $num);
            $r = array_map(fn ($key) => $items[$key], $keys);
            return $r;
        }
    }

    /**
     * Returns a random boolean.
     *
     * @param float $truefreq Frequency of return true values.
     * @return bool
     */
    protected static function random_bool(float $truefreq): bool {
        return rand() / getrandmax() < $truefreq;
    }
}
