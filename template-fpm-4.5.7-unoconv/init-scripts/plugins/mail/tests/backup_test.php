<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \backup_local_mail_plugin
 * @covers \restore_local_mail_plugin
 */
final class backup_test extends test\testcase {
    public function setUp(): void {
        global $CFG;

        parent::setUp();

        require_once("$CFG->dirroot/backup/util/includes/backup_includes.php");
        require_once("$CFG->dirroot/backup/util/includes/restore_includes.php");
    }

    public function test_backup_and_restore(): void {
        global $DB;

        set_config('enablebackup', 1, 'local_mail');

        self::generate_random_data(true);
        self::setAdminUser();

        foreach (array_keys(get_courses()) as $oldcourseid) {
            $fs = get_file_storage();

            if ($oldcourseid == SITEID) {
                continue;
            }

            // Fetch old records.
            $oldlabels = $DB->get_records('local_mail_labels', [], 'userid, name');
            $oldmessages = $DB->get_records('local_mail_messages', ['courseid' => $oldcourseid], 'id');
            $oldmessagerefs = $DB->get_records_list('local_mail_message_refs', 'messageid', array_keys($oldmessages), 'id');
            $oldmessageusers = $DB->get_records('local_mail_message_users', ['courseid' => $oldcourseid], 'id');
            $oldmessagelabels = $DB->get_records('local_mail_message_labels', ['courseid' => $oldcourseid], 'id');
            $oldfiles = $fs->get_area_files(\context_course::instance($oldcourseid)->id, 'local_mail', 'message');

            // Backup course.
            $backupid = self::backup_course($oldcourseid, true);

            // Delete the course and a random label.
            delete_course($oldcourseid, false);
            if ($oldmessagelabels) {
                label::get(self::random_item($oldmessagelabels)->labelid)->delete();
            }

            // Restore course.
            $newcourseid = self::restore_course($backupid, true);

            // Fetch new records.
            $idmap = ['courseid' => [$oldcourseid => $newcourseid]];
            $newlabels = $DB->get_records('local_mail_labels', [], 'userid, name');
            $newmessages = $DB->get_records('local_mail_messages', ['courseid' => $newcourseid], 'id');
            $newmessagerefs = $DB->get_records_list('local_mail_message_refs', 'messageid', array_keys($newmessages), 'id');
            $newmessageusers = $DB->get_records('local_mail_message_users', ['courseid' => $newcourseid], 'id');
            $newmessagelabels = $DB->get_records('local_mail_message_labels', ['courseid' => $newcourseid], 'id');
            $newfiles = $fs->get_area_files(\context_course::instance($newcourseid)->id, 'local_mail', 'message');

            // Check restored records and files.
            self::assert_restored_records($oldlabels, $newlabels, $idmap, ['labelid']);
            self::assert_restored_records($oldmessages, $newmessages, $idmap, ['messageid', 'reference']);
            self::assert_restored_records($oldmessagerefs, $newmessagerefs, $idmap);
            self::assert_restored_records($oldmessageusers, $newmessageusers, $idmap);
            self::assert_restored_records($oldmessagelabels, $newmessagelabels, $idmap);
            self::assert_restored_files($oldfiles, $newfiles, $idmap);
        }
    }

    public function test_backup_disabled(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user1, 'Label', 'blue');
        $label2 = label::create($user2, 'Label', 'blue');
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = $time;

        $message = message::create($data);
        $message->send(time());
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);

        // Backup course with mail backup disabled.
        set_config('enablebackup', 0, 'local_mail');
        $backupid = self::backup_course($course->id, true);

        // Delete labels and courses.
        delete_course($course->id, false);
        $label1->delete();
        $label2->delete();

        // Restore course with mail backup enabled.
        set_config('enablebackup', 1, 'local_mail');
        self::restore_course($backupid, true);

        // Check nothing is restored.
        self::assert_record_count(0, 'messages');
        self::assert_record_count(0, 'message_refs');
        self::assert_record_count(0, 'message_users');
        self::assert_record_count(0, 'message_labels');
        self::assert_record_count(0, 'labels');
    }

    public function test_backup_without_users(): void {
        set_config('enablebackup', 1, 'local_mail');

        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user1, 'Label', 'blue');
        $label2 = label::create($user2, 'Label', 'blue');
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = $time;

        $message = message::create($data);
        $message->send(time());
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);

        // Backup course without users.
        $backupid = self::backup_course($course->id, false);

        // Delete labels and courses.
        delete_course($course->id, false);
        $label1->delete();
        $label2->delete();

        // Restore course.
        self::restore_course($backupid, false);

        // Check nothing is restored.
        self::assert_record_count(0, 'messages');
        self::assert_record_count(0, 'message_refs');
        self::assert_record_count(0, 'message_users');
        self::assert_record_count(0, 'message_labels');
        self::assert_record_count(0, 'labels');
    }

    public function test_restore_disabled(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user1, 'Label', 'blue');
        $label2 = label::create($user2, 'Label', 'blue');
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = $time;

        $message = message::create($data);
        $message->send(time());
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);

        // Backup course with mail backup enabled.
        set_config('enablebackup', 1, 'local_mail');
        $backupid = self::backup_course($course->id, true);

        // Delete labels and courses.
        delete_course($course->id, false);
        $label1->delete();
        $label2->delete();

        // Restore course with mail backup disabled.
        set_config('enablebackup', 0, 'local_mail');
        self::restore_course($backupid, true);

        // Check nothing is restored.
        self::assert_record_count(0, 'messages');
        self::assert_record_count(0, 'message_refs');
        self::assert_record_count(0, 'message_users');
        self::assert_record_count(0, 'message_labels');
        self::assert_record_count(0, 'labels');
    }

    public function test_restore_without_users(): void {
        set_config('enablebackup', 1, 'local_mail');

        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $label1 = label::create($user1, 'Label', 'blue');
        $label2 = label::create($user2, 'Label', 'blue');
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Content';
        $data->format = (int) FORMAT_PLAIN;
        $data->time = $time;

        $message = message::create($data);
        $message->send(time());
        $message->set_labels($user1, [$label1]);
        $message->set_labels($user2, [$label2]);

        // Backup course with users.
        $backupid = self::backup_course($course->id, true);

        // Delete labels and courses.
        delete_course($course->id, false);
        $label1->delete();
        $label2->delete();

        // Restore course without users.
        self::restore_course($backupid, false);

        // Check nothing is restored.
        self::assert_record_count(0, 'messages');
        self::assert_record_count(0, 'message_refs');
        self::assert_record_count(0, 'message_users');
        self::assert_record_count(0, 'message_labels');
        self::assert_record_count(0, 'labels');
    }

    /**
     * Checks that restored files match original files.
     *
     * @param \stored_file[] $oldfiles Original files.
     * @param \stored_file[] $newfiles Restored files, with the same order as original files.
     * @param int[][] $idmap Map of fields to arrays of old IDs to new IDs.
     */
    private static function assert_restored_files(array $oldfiles, array $newfiles, array $idmap) {
        self::assertCount(count($oldfiles), $newfiles);

        foreach ($oldfiles as $oldfile) {
            $newfile = current($newfiles);
            $messageid = $idmap['messageid'][$oldfile->get_itemid()];
            self::assertEquals($messageid, $newfile->get_itemid());
            self::assertEquals($oldfile->get_filename(), $newfile->get_filename());
            self::assertEquals($oldfile->get_content(), $newfile->get_content());
            next($newfiles);
        }
    }

    /**
     * Checks that restored records match original records.
     *
     * @param \stdClass[] $oldrecords Original records.
     * @param \stdClass[] $newrecords Restored records, with the same order as original records.
     * @param int[][] $idmap Map of fields to arrays of old IDs to new IDs.
     * @param string[] $idmapfields Fields to add to the ID map with the IDs of the new records.
     */
    private static function assert_restored_records(
        array $oldrecords,
        array $newrecords,
        array &$idmap,
        array $idmapfields = []
    ) {
        self::assertCount(count($oldrecords), $newrecords);

        foreach ($oldrecords as $oldrecord) {
            $newrecord = current($newrecords);
            foreach ($idmapfields as $field) {
                $idmap[$field][$oldrecord->id] = $newrecord->id;
            }
            unset($oldrecord->id);
            unset($newrecord->id);
            foreach ($oldrecord as $field => $value) {
                if (isset($idmap[$field])) {
                    $oldrecord->$field = $idmap[$field][$value];
                }
            }
            self::assertEquals($oldrecord, $newrecord);
            next($newrecords);
        }
    }

    /**
     * Makes a backup of the course.
     *
     * @param int $courseid Course ID.
     * @param bool $userdata Include user data.
     * @return string Unique identifier for this backup.
     */
    private static function backup_course(int $courseid, bool $userdata): string {
        global $CFG, $USER;

        // Workaround for bug introduced in MDL-81119.
        $CFG->forced_plugin_settings['backup'] ??= [];

        // Do backup with default settings. MODE_IMPORT means it will just
        // create the directory and not zip it.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $courseid,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value($userdata);
        $backupid = $bc->get_backupid();

        $bc->execute_plan();
        $bc->destroy();

        return $backupid;
    }

    /**
     * Restores a backup that has been made earlier.
     *
     * @param string $backupid The unique identifier of the backup.
     * @param bool $userdata Include user data.
     * @return int The new course id.
     */
    private static function restore_course(string $backupid, bool $userdata) {
        global $DB, $USER;

        static $coursenumber = 0;

        $coursenumber++;
        $fullname = "Restored course $coursenumber";
        $shortname = "Restored $coursenumber";
        $categoryid = $DB->get_field_select('course_categories', "MIN(id)", "parent=0");

        $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $categoryid);
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $rc->get_plan()->get_setting('users')->set_value($userdata);

        self::assertTrue($rc->execute_precheck());

        ob_start();
        $rc->execute_plan();
        ob_end_clean();

        $rc->destroy();

        return $newcourseid;
    }
}
