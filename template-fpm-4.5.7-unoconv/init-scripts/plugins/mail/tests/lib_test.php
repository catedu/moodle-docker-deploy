<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail_pluginfile
 * @covers \local_mail_render_navbar_output
 * @runTestsInSeparateProcesses
 */
final class lib_test extends test\testcase {
    public function test_pluginfile(): void {
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user3->id, $course->id);
        $generator->enrol_user($user4->id, $course->id);
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        $message1 = message::create($data);
        $message1->send(time());
        $data = message_data::reply($message1, $user2, false);
        $data->to = [$user1, $user3];
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message2 = message::create($data);
        $message2->send(time());
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        self::create_draft_file($data->draftitemid, 'file3.txt', 'File 3');
        $message3 = message::create($data);

        // User can view attachments.
        self::setUser($user2->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message1->id, 'file1.txt'], null);
        self::assertInstanceOf('\stored_file', $result);
        self::assertEquals($course->get_context()->id, $result->get_contextid());
        self::assertEquals('local_mail', $result->get_component());
        self::assertEquals('message', $result->get_filearea());
        self::assertEquals($message1->id, $result->get_itemid());
        self::assertEquals('/', $result->get_filepath());
        self::assertEquals('file1.txt', $result->get_filename());
        self::assertEquals('File 1', $result->get_content());

        // User can view attachments.
        self::setUser($user3->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message1->id, 'file1.txt'], null);
        self::assertNotFalse($result);

        // User cannot view message.
        self::setUser($user4->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message1->id, 'file1.txt'], null);
        self::assertFalse($result);

        // User cannot view draft.
        self::setUser($user2->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message3->id, 'file3.txt'], null);
        self::assertFalse($result);

        // Inexistent message.
        self::setUser($user2->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message1->id, 'file2.txt'], null);
        self::assertFalse($result);

        // Inexistent message.
        self::setUser($user2->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [-1, 'file1.txt'], null);
        self::assertFalse($result);

        // Not installed.
        unset_config('version', 'local_mail');
        self::setUser($user2->id);
        $result = local_mail_pluginfile(null, null, $course->get_context(), 'message', [$message1->id, 'file1.txt'], null);
        self::assertFalse($result);
    }

    public function test_render_navbar_output(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label1 = label::create($user1, 'Label 1');
        $label2 = label::create($user1, 'Label 2');
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user1->id, $course2->id);
        $data = message_data::new($course1, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message1 = message::create($data);
        $message1->send(time());
        $data = message_data::new($course1, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message2 = message::create($data);
        $message2->send(time());
        $data = message_data::new($course1, $user1);
        message::create($data);

        $PAGE->set_course(get_course($course1->id));
        $output = new \core_renderer($PAGE, RENDERER_TARGET_GENERAL);
        $renderer = $PAGE->get_renderer('local_mail');

        // View page.

        $PAGE->set_url(new \moodle_url('/local/mail/view.php'));
        self::setUser($user1->id);

        $result = local_mail_render_navbar_output($output);

        self::assertStringContainsString('<div class="popover-region" id="local-mail-navbar">', $result);
        self::assertStringNotContainsString('<script>', $result);

        // Course page.

        $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => $course1->id]));
        self::setUser($user1->id);

        $result = local_mail_render_navbar_output($output);

        self::assertStringContainsString('<div class="popover-region" id="local-mail-navbar">', $result);
        self::assertStringContainsString($renderer->svelte_script('src/navigation.ts'), $result);
        $expected = \html_writer::script('window.local_mail_navbar_data = ' . json_encode([
            'userid' => $user1->id,
            'courseid' => $course1->id,
            'settings' => (array) settings::get(),
            'strings' => output\strings::get_many([
                'allcourses',
                'bcc',
                'cc',
                'changecourse',
                'compose',
                'course',
                'drafts',
                'inbox',
                'nocoursematchestext',
                'pluginname',
                'preferences',
                'sendmail',
                'sentplural',
                'starredplural',
                'to',
                'trash',
            ]),
            'courses' => external::get_courses_raw(),
            'labels' => external::get_labels_raw(),
        ]));
        self::assertStringContainsString($expected, $result);

        // User has no courses.

        self::setUser($user2->id);

        $result = local_mail_render_navbar_output($output);

        self::assertEquals('', $result);

        // User not logged in.

        self::setUser(null);

        $result = local_mail_render_navbar_output($output);

        self::assertEquals('', $result);

        // Not installed.

        unset_config('version', 'local_mail');
        self::setUser($user1->id);

        $result = local_mail_render_navbar_output($output);

        self::assertEquals('', $result);
    }
}
