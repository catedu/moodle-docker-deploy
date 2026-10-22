<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\output\renderer
 */
final class output_renderer_test extends test\testcase {
    public function test_file_url(): void {
        global $CFG, $PAGE;

        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        self::setUser($user->id);
        $course = new course($generator->create_course());
        $data = message_data::new($course, $user);
        $file = self::create_draft_file($data->draftitemid, 'file.txt', 'File content');
        $context = \context_user::instance($user->id);

        $renderer = $PAGE->get_renderer('local_mail');

        $expected = "$CFG->wwwroot/pluginfile.php/$context->id/user/draft/$data->draftitemid/file.txt";
        self::assertEquals($expected, $renderer->file_url($file));
    }

    public function test_file_icon_url(): void {
        global $CFG, $PAGE;

        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        self::setUser($user->id);
        $course = new course($generator->create_course());
        $data = message_data::new($course, $user);
        $file1 = self::create_draft_file($data->draftitemid, 'file1.txt', 'File content');
        $file2 = self::create_draft_file($data->draftitemid, 'file2.html', 'File content');

        $renderer = $PAGE->get_renderer('local_mail');

        $size = $CFG->branch >= 403 ? null : 24; // Size is deprecated since Moodle 4.3.
        self::assertEquals($renderer->image_url(file_extension_icon('file1.txt', $size)), $renderer->file_icon_url($file1));
        self::assertEquals($renderer->image_url(file_extension_icon('file2.html', $size)), $renderer->file_icon_url($file2));
    }

    public function test_formatted_message_content(): void {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_mail');
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user = new user($generator->create_user());
        $data = message_data::new($course, $user);
        $data->subject = 'Subject';
        $data->content = '<p>@@PLUGINFILE@@/file.txt</p>';
        self::create_draft_file($data->draftitemid, 'file.txt', 'File content');
        $message = message::create($data);

        $result = $renderer->formatted_message_content($message);

        $context = $message->course->get_context();
        $fileurl = new \moodle_url("/pluginfile.php/$context->id/local_mail/message/$message->id/file.txt");
        $filelink = '<a href="' . $fileurl->out() . '" class="_blanktarget">' . $fileurl->out(false) . '</a>';
        self::assertEquals("<p>$filelink</p>", $result);
    }

    public function test_formatted_time(): void {
        global $PAGE;

        $generator = self::getDataGenerator();
        $user = new user($generator->create_user());
        self::setUser($user->id);

        $tz = \core_date::get_user_timezone();

        $renderer = $PAGE->get_renderer('local_mail');

        $now = new \DateTime('2021-10-11 12:13:14', new \DateTimeZone($tz));

        // Today.
        $date = new \DateTime('2021-10-11 01:02:03', new \DateTimeZone($tz));
        $shorttime = userdate($date->getTimestamp(), get_string('strftimetime', 'langconfig'));
        $fulltime = userdate($date->getTimestamp(), get_string('strftimedatetime', 'langconfig'));
        self::assertEquals($shorttime, $renderer->formatted_time($date->getTimestamp(), false, $now->getTimestamp()));
        self::assertEquals($fulltime, $renderer->formatted_time($date->getTimestamp(), true, $now->getTimestamp()));

        // Yesterday.
        $date = new \DateTime('2021-10-10 23:59:58', new \DateTimeZone($tz));
        $shorttime = userdate($date->getTimestamp(), get_string('strftimedateshortmonthabbr', 'langconfig'));
        $fulltime = userdate($date->getTimestamp(), get_string('strftimedatetime', 'langconfig'));
        self::assertEquals($shorttime, $renderer->formatted_time($date->getTimestamp(), false, $now->getTimestamp()));
        self::assertEquals($fulltime, $renderer->formatted_time($date->getTimestamp(), true, $now->getTimestamp()));

        // Previous year.
        $date = new \DateTime('2020-12-31 23:59:58', new \DateTimeZone($tz));
        $shorttime = userdate($date->getTimestamp(), get_string('strftimedatefullshort', 'langconfig'));
        $fulltime = userdate($date->getTimestamp(), get_string('strftimedatetime', 'langconfig'));
        self::assertEquals($shorttime, $renderer->formatted_time($date->getTimestamp(), false, $now->getTimestamp()));
        self::assertEquals($fulltime, $renderer->formatted_time($date->getTimestamp(), true, $now->getTimestamp()));

        // Future.
        $date = new \DateTime('2021-10-11 12:13:15', new \DateTimeZone($tz));
        $shorttime = userdate($date->getTimestamp(), get_string('strftimedatefullshort', 'langconfig'));
        $fulltime = userdate($date->getTimestamp(), get_string('strftimedatetime', 'langconfig'));
        self::assertEquals($shorttime, $renderer->formatted_time($date->getTimestamp(), false, $now->getTimestamp()));
        self::assertEquals($fulltime, $renderer->formatted_time($date->getTimestamp(), true, $now->getTimestamp()));
    }

    public function test_notification(): void {
        global $PAGE, $SITE;

        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $course = new course($generator->create_course());
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->cc = [$user3, $user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = '<p>Content</p>';
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File content');
        self::create_draft_file($data->draftitemid, 'file2.html', 'File content');
        $message = message::create($data);
        $message->send(time());
        $url = new \moodle_url('local/mail/view.php', ['t' => 'inbox', 'm' => $message->id]);

        $renderer = $PAGE->get_renderer('local_mail');
        $notification = $renderer->notification($message, $user2);

        self::assertEquals($course->id, $notification->courseid);
        self::assertEquals('local_mail', $notification->component);
        self::assertEquals('mail', $notification->name);
        self::assertEquals($user1->id, $notification->userfrom);
        self::assertEquals($user2->id, $notification->userto);
        self::assertEquals(output\strings::get('notificationsubject', $SITE->shortname), $notification->subject);
        self::assertStringContainsString($url->out(false), $notification->fullmessage);
        self::assertStringContainsString($course->fullname, $notification->fullmessage);
        self::assertStringContainsString($user1->fullname(), $notification->fullmessage);
        self::assertStringContainsString($renderer->formatted_time($message->time), $notification->fullmessage);
        self::assertStringContainsString('Subject', $notification->fullmessage);
        self::assertStringContainsString('Content', $notification->fullmessage);
        self::assertStringNotContainsString('<p>', $notification->fullmessage);
        self::assertStringContainsString('file1.txt', $notification->fullmessagehtml);
        self::assertStringContainsString('file2.html', $notification->fullmessagehtml);
        self::assertEquals(FORMAT_PLAIN, $notification->fullmessageformat);
        self::assertStringContainsString($url->out(true), $notification->fullmessagehtml);
        self::assertStringContainsString($course->fullname, $notification->fullmessagehtml);
        self::assertStringContainsString($user1->fullname(), $notification->fullmessagehtml);
        self::assertStringContainsString($renderer->formatted_time($message->time), $notification->fullmessagehtml);
        self::assertStringContainsString('Subject', $notification->fullmessagehtml);
        self::assertStringContainsString('<p>Content</p>', $notification->fullmessagehtml);
        self::assertStringContainsString('file1.txt', $notification->fullmessagehtml);
        self::assertStringContainsString('file2.html', $notification->fullmessagehtml);
        self::assertEquals(1, $notification->notification);
        $a = ['user' => $user1->fullname(), 'course' => $course->fullname];
        self::assertEquals(output\strings::get('notificationsmallmessage', $a), $notification->smallmessage);
        $contexturl = new \moodle_url('/local/mail/view.php', ['t' => 'inbox', 'm' => $message->id]);
        self::assertEquals($contexturl->out(false), $notification->contexturl);
        self::assertEquals('Subject', $notification->contexturlname);
    }

    public function test_svelte_script(): void {
        global $CFG, $PAGE, $OUTPUT;

        $renderer = $PAGE->get_renderer('local_mail');

        // Head not written.

        $html = $renderer->svelte_script('src/view.ts');
        $head = $OUTPUT->standard_head_html();

        $url = preg_quote($CFG->wwwroot . '/local/mail/svelte/build/', '/') . 'view-[\w-]+\.js';
        $pattern = '/^<script type="module" src="' . $url . '"><\/script>$/';
        self::assertMatchesRegularExpression($pattern, $html);

        $url = preg_quote($CFG->wwwroot . '/local/mail/svelte/build/', '/') . 'view-[\w-]+\.css';
        $pattern = '/<link rel="stylesheet" type="text\/css" href="' . $url . '" \/>/';
        self::assertMatchesRegularExpression($pattern, $head);

        // Head already written.

        $html = $renderer->svelte_script('src/view.ts');

        $jsurl = preg_quote($CFG->wwwroot . '/local/mail/svelte/build/', '/') . 'view-[\w-]+\.js';
        $cssurl = preg_quote($CFG->wwwroot . '/local/mail/svelte/build/', '/') . 'view-[\w-]+\.css';
        $pattern = '/^<script>.*"' . $cssurl . '".*<\/script>\s*<script type="module" src="' . $jsurl . '"><\/script>$/s';
        self::assertMatchesRegularExpression($pattern, $html);

        // Invalid script name.

        try {
            $renderer->svelte_script('src/inexistent.ts');
            self::fail();
        } catch (\moodle_exception $e) {
            self::assertEquals('codingerror', $e->errorcode);
        }

        // Developement server.

        set_config('local_mail_devserver', 'http://localhost:5173');
        $url = preg_quote('http://localhost:5173/src/view.ts', '/');
        $pattern = '/^<script type="module" src="' . $url . '"><\/script>$/';
        $result = $renderer->svelte_script('src/view.ts');
        self::assertMatchesRegularExpression($pattern, $result);
    }
}
