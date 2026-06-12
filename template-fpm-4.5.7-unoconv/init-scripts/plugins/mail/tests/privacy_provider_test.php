<?php
/*
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_mail\privacy\provider;
use local_mail\output\strings;

/**
 * @covers \local_mail\privacy\provider
 */
final class privacy_provider_test extends test\testcase {

    public function test_get_metadata(): void {
        global $CFG;

        $xmldbfile = new \xmldb_file("$CFG->dirroot/local/mail/db/install.xml");
        $xmldbfile->loadXMLStructure();
        $expectedtables = [];
        foreach ($xmldbfile->getStructure()->getTables() as $table) {
            $fields = [];
            foreach ($table->getFields() as $field) {
                if ($field->getName() !== 'id') {
                    $fields[] = $field->getName();
                }
            }
            $expectedtables[$table->getName()] = $fields;
        }

        $collection = new collection('local_mail');

        provider::get_metadata($collection);

        $tables = [];
        $preferences = [];
        $subsystems = [];
        foreach ($collection->get_collection() as $type) {
            if (is_a($type, \core_privacy\local\metadata\types\database_table::class)) {
                $tables[$type->get_name()] = array_keys($type->get_privacy_fields());
            } else if (is_a($type, \core_privacy\local\metadata\types\subsystem_link::class)) {
                $subsystems[] = $type->get_name();
            } else if (is_a($type, \core_privacy\local\metadata\types\user_preference::class)) {
                $preferences[] = $type->get_name();
            }
        }

        self::assertEquals($expectedtables, $tables);
        foreach ($tables as $table => $fields) {
            self::assert_string_exists("privacy:metadata:$table");
            foreach ($fields as $field) {
                self::assert_string_exists("privacy:metadata:$table:$field");
            }
        }
        self::assertEqualsCanonicalizing(['local_mail_mailsperpage', 'local_mail_markasread'], $preferences);
        foreach ($preferences as $preference) {
            self::assert_string_exists("privacy:metadata:preference:$preference");
        }
        self::assertEqualsCanonicalizing(['core_files'], $subsystems);
        foreach ($subsystems as $subsystem) {
            self::assert_string_exists("privacy:metadata:$subsystem");
        }
    }

    public function test_get_contexts_for_userid(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach ($users as $user) {
            $courses = [];
            foreach ($messages as $message) {
                $hassenderdata = $message->sender()->id == $user->id &&
                    $message->deleted($user) != message::DELETED_CONTENT;
                $hasrecipientdata = !$message->draft && $message->has_recipient($user) &&
                    $message->deleted($user) != message::DELETED_FOREVER;
                if ($hassenderdata || $hasrecipientdata) {
                    $courses[$message->course->id] = true;
                }
            }

            $expectedcontextids = [];
            foreach (array_keys($courses) as $courseid) {
                $expectedcontextids[] = \context_course::instance($courseid)->id;
            }
            if (label::get_by_user(($user))) {
                $expectedcontextids[] = \context_user::instance($user->id)->id;
            }

            $contextlist = provider::get_contexts_for_userid($user->id);

            self::assertEqualsCanonicalizing($expectedcontextids, $contextlist->get_contextids());

            // User with already deleted data.

            provider::delete_data_for_user(
                new approved_contextlist(\core_user::get_user($user->id), 'local_mail', $expectedcontextids)
            );

            $contextlist = provider::get_contexts_for_userid($user->id);

            self::assertEquals([], $contextlist->get_contextids());
        }
    }

    public function test_get_users_in_context(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach (get_courses() as $course) {
            $courseusers = [];
            foreach ($messages as $message) {
                if ($message->course->id != $course->id) {
                    continue;
                }
                $sender = $message->sender();
                if ($message->deleted($sender) != message::DELETED_CONTENT) {
                    $courseusers[$sender->id] = true;
                }
                if ($message->draft) {
                    continue;
                }
                foreach ($message->recipients() as $user) {
                    if ($message->deleted($user) != message::DELETED_FOREVER) {
                        $courseusers[$user->id] = true;
                    }
                }
            }
            $context = \context_course::instance($course->id);
            $userlist = new userlist($context, 'local_mail');

            provider::get_users_in_context($userlist);

            self::assertEqualsCanonicalizing(array_keys($courseusers), $userlist->get_userids());

            // All data of users already deleted.

            provider::delete_data_for_users(
                new approved_userlist($context, 'local_mail', array_keys($courseusers))
            );
            $userlist = new userlist($context, 'local_mail');

            provider::get_users_in_context($userlist);

            self::assertEquals([], $userlist->get_userids());
        }

        // Other context levels.
        $userlist = new userlist(\context_system::instance(), 'local_mail');
        provider::get_users_in_context($userlist);
        self::assertEqualsCanonicalizing([], $userlist->get_userids());
    }

    public function test_export_user_data(): void {
        [$users, $messages] = self::generate_random_data(true);

        $contexts = [\context_system::instance()];
        foreach (get_courses() as $course) {
            if ($course->id != SITEID) {
                $context = \context_course::instance($course->id);
                $contexts[] = $context;
            }
        }

        foreach ($users as $user) {
            $contextlist = new approved_contextlist(
                \core_user::get_user($user->id),
                'local_mail',
                array_column($contexts, 'id')
            );
            provider::export_user_data($contextlist);

            // Labels.
            $writer = writer::with_context(\context_user::instance($user->id));
            foreach (label::get_by_user($user) as $label) {
                $subcontext = [strings::get('pluginname'), strings::get('labels'), $label->id];
                self::assertIsObject($writer->get_data($subcontext));
            }

            // Messages.
            foreach ($contexts as $context) {
                $writer = writer::with_context($context);
                foreach ($messages as $message) {
                    $subcontext = [strings::get('pluginname'), strings::get('messages'), $message->id];
                    $iscourse = $message->course->id == $context->instanceid;
                    $hassenderdata = $message->sender()->id == $user->id &&
                        $message->deleted($user) != message::DELETED_CONTENT;
                    $hasrecipientdata = !$message->draft && $message->has_recipient($user) &&
                        $message->deleted($user) != message::DELETED_FOREVER;
                    if ($iscourse && ($hassenderdata || $hasrecipientdata)) {
                        self::assertIsObject($writer->get_data($subcontext));
                        self::assertCount($message->attachments, $writer->get_files($subcontext));
                    } else {
                        self::assertEmpty($writer->get_data($subcontext));
                    }
                }
            }

            writer::reset();
        }
    }

    public function test_export_user_preferences(): void {
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $writer = writer::with_context(\context_system::instance());

        // Defaults.

        provider::export_user_preferences($user->id);

        $this->assertFalse($writer->has_any_data());

        // Custom.

        set_user_preference('local_mail_mailsperpage', 20, $user);
        set_user_preference('local_mail_markasread', true, $user);

        provider::export_user_preferences($user->id);

        $this->assertTrue($writer->has_any_data());

        $preferences = (array) $writer->get_user_preferences('local_mail');

        $this->assertEquals((object) [
            'value' => 20,
            'description' => get_string('privacy:metadata:preference:local_mail_mailsperpage', 'local_mail'),
        ], $preferences['local_mail_mailsperpage']);

        $this->assertEquals((object) [
            'value' => get_string('yes'),
            'description' => get_string('privacy:metadata:preference:local_mail_markasread', 'local_mail'),
        ], $preferences['local_mail_markasread']);
    }

    public function test_delete_data_for_all_users_in_context(): void {
        [$users, $messages] = self::generate_random_data(true);

        $course = $messages[0]->course;
        $context = $course->get_context();

        $fs = get_file_storage();

        provider::delete_data_for_all_users_in_context($context);

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

    public function test_delete_data_for_user(): void {
        [$users, $messages] = self::generate_random_data(true);

        // Course context.

        $user = $messages[0]->sender();
        $course = $messages[0]->course;
        $contextlist = new approved_contextlist(
            \core_user::get_user($user->id),
            'local_mail',
            [$course->get_context()->id]
        );

        provider::delete_data_for_user($contextlist);

        self::assert_deleted_messages($messages, $user, $course);

        // User context.

        $contextlist = new approved_contextlist(
            \core_user::get_user($user->id),
            'local_mail',
            [\context_user::instance($user->id)->id]
        );

        provider::delete_data_for_user($contextlist);

        self::assertEquals([], label::get_by_user($user));
    }

    public function test_delete_data_for_users(): void {
        [$users, $messages] = self::generate_random_data(true);

        $course = $messages[0]->course;
        $context = $course->get_context();
        $user = $messages[0]->sender();
        $userlist = new approved_userlist($context, 'local_mail', [$user->id]);

        provider::delete_data_for_users($userlist);

        self::assert_deleted_messages($messages, $user, $course);
    }

    /**
     * Asserts that message for a user in a course has been deleted.
     *
     * @param array $messages Array of all messages.
     * @param user $user User.
     * @param course $course Course.
     * @return void
     */
    private static function assert_deleted_messages(array $messages, user $user, course $course): void {
        $userlabels = label::get_by_user($user);
        foreach ($messages as $message) {
            $sender = $message->sender();
            if ($message->course->id != $course->id) {
                self::assert_message($message);
            } else if ($message->draft && $user->id == $sender->id) {
                self::assert_record_count(0, 'messages', ['id' => $message->id]);
                self::assert_record_count(0, 'message_refs', ['messageid' => $message->id]);
                self::assert_record_count(0, 'message_users', ['messageid' => $message->id]);
                self::assert_record_count(0, 'message_labels', ['messageid' => $message->id]);
                self::assert_attachments([], $message);
            } else if ($user->id == $sender->id || !$message->draft && $message->has_recipient($user)) {
                self::assert_record_data('message_users', [
                    'messageid' => $message->id,
                    'userid' => $user->id,
                ], [
                    'unread' => 0,
                    'starred' => 0,
                    'deleted' => $user->id == $sender->id ? message::DELETED_CONTENT : message::DELETED_FOREVER,
                ]);
                foreach ($userlabels as $label) {
                    self::assert_record_count(0, 'message_labels', [
                        'messageid' => $message->id,
                        'labelid' => $label->id,
                    ]);
                }
                if ($user->id == $sender->id) {
                    self::assert_record_data('messages', [
                        'id' => $message->id,
                    ], [
                        'subject' => '',
                        'content' => '',
                        'format' => FORMAT_PLAIN,
                        'attachments' => 0,
                        'normalizedsubject' => '',
                        'normalizedcontent' => '',
                    ]);
                    self::assert_attachments([], $message);
                }
            } else {
                self::assert_message($message);
            }
        }
    }
}
