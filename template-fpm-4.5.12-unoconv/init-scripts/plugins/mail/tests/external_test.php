<?php
/*
 * SPDX-FileCopyrightText: 2017-2025 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2021 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\external
 * @runTestsInSeparateProcesses
 */
final class external_test extends test\testcase {
    public function test_get_settings(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        set_config('enablebackup', '0', 'local_mail');
        set_config('maxrecipients', '20', 'local_mail');
        set_config('usersearchlimit', '50', 'local_mail');
        set_config('maxfiles', '5', 'local_mail');
        set_config('maxbytes', '45000', 'local_mail');
        set_config('autosaveinterval', '3', 'local_mail');
        set_config('globaltrays', 'drafts,trash', 'local_mail');
        set_config('coursetrays', 'unread', 'local_mail');
        set_config('coursetraysname', 'shortname', 'local_mail');
        set_config('coursebadges', 'hidden', 'local_mail');
        set_config('coursebadgeslength', '10', 'local_mail');
        set_config('filterbycourse', 'hidden', 'local_mail');
        set_config('incrementalsearch', '0', 'local_mail');
        set_config('incrementalsearchlimit', '2000', 'local_mail');
        set_config('courselink', 'fullname', 'local_mail');
        \core_plugin_manager::resolve_plugininfo_class('message')::enable_plugin('airnotifier', true);
        set_config('airnotifieraccesskey', random_string());
        set_config('message_provider_local_mail_mail_enabled', 'email,airnotifier', 'message');
        set_config('email_provider_local_mail_mail_locked', '1', 'message');
        set_config('popup_provider_local_mail_mail_locked', '1', 'message');
        $this->setUser($user);

        $result = external::get_settings();

        external::validate_parameters(external::get_settings_returns(), $result);
        $expected = [
            'enablebackup' => false,
            'maxrecipients' => 20,
            'usersearchlimit' => 50,
            'maxfiles' => 5,
            'maxbytes' => 45000,
            'autosaveinterval' => 3,
            'globaltrays' => ['drafts', 'trash'],
            'coursetrays' => 'unread',
            'coursetraysname' => 'shortname',
            'coursebadges' => 'hidden',
            'coursebadgeslength' => 10,
            'filterbycourse' => 'hidden',
            'incrementalsearch' => false,
            'incrementalsearchlimit' => 2000,
            'courselink' => 'fullname',
            'messageprocessors' => [
                [
                    'name' => 'email',
                    'displayname' => get_string('pluginname', 'message_email'),
                    'locked' => true,
                    'enabled' => true,
                ],
                [
                    'name' => 'airnotifier',
                    'displayname' => get_string('pluginname', 'message_airnotifier'),
                    'locked' => false,
                    'enabled' => true,
                ],
            ],
        ];
        self::assertEquals($expected, $result);

        // Default settings.

        set_config('maxbytes', 123000);
        unset_config('enablebackup', 'local_mail');
        unset_config('maxrecipients', 'local_mail');
        unset_config('usersearchlimit', 'local_mail');
        unset_config('maxfiles', 'local_mail');
        unset_config('maxbytes', 'local_mail');
        unset_config('autosaveinterval', 'local_mail');
        unset_config('globaltrays', 'local_mail');
        unset_config('coursetrays', 'local_mail');
        unset_config('coursetraysname', 'local_mail');
        unset_config('coursebadges', 'local_mail');
        unset_config('coursebadgeslength', 'local_mail');
        unset_config('filterbycourse', 'local_mail');
        unset_config('incrementalsearch', 'local_mail');
        unset_config('incrementalsearchlimit', 'local_mail');
        unset_config('courselink', 'local_mail');

        $result = external::get_settings();

        external::validate_parameters(external::get_settings_returns(), $result);
        self::assertEquals(true, $result['enablebackup']);
        self::assertEquals(100, $result['maxrecipients']);
        self::assertEquals(100, $result['usersearchlimit']);
        self::assertEquals(20, $result['maxfiles']);
        self::assertEquals(123000, $result['maxbytes']);
        self::assertEquals(5, $result['autosaveinterval']);
        self::assertEquals(['starred', 'sent', 'drafts', 'trash'], $result['globaltrays']);
        self::assertEquals('none', $result['coursetrays']);
        self::assertEquals('fullname', $result['coursetraysname']);
        self::assertEquals('fullname', $result['coursebadges']);
        self::assertEquals(20, $result['coursebadgeslength']);
        self::assertEquals('fullname', $result['filterbycourse']);
        self::assertEquals('hidden', $result['courselink']);
        self::assertEquals(true, $result['incrementalsearch']);
        self::assertEquals(1000, $result['incrementalsearchlimit']);

        // Empty global trays.

        set_config('globaltrays', '', 'local_mail');

        $result = external::get_settings();

        external::validate_parameters(external::get_settings_returns(), $result);
        self::assertEquals([], $result['globaltrays']);

        // Plugin not installed.

        unset_config('version', 'local_mail');
        try {
            external::get_settings();
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorpluginnotinstalled', $e->errorcode);
        }
    }

    public function test_get_strings(): void {
        $result = external::get_strings();

        external::validate_parameters(external::get_strings_returns(), $result);
        self::assertEquals(output\strings::get_all(), $result);
    }

    public function test_get_preferences(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);
        set_config('message_provider_local_mail_mail_enabled', 'email', 'message');
        set_user_preference('local_mail_mailsperpage', 20);
        set_user_preference('local_mail_markasread', 1);
        set_user_preference('message_provider_local_mail_mail_enabled', 'popup,unknown');

        $result = external::get_preferences();

        external::validate_parameters(external::get_preferences_returns(), $result);

        $expected = [
            'perpage' => 20,
            'markasread' => true,
            'notifications' => ['popup'],
        ];
        self::assertEquals($expected, $result);

        // Default preferences.

        unset_user_preference('local_mail_mailsperpage');
        unset_user_preference('local_mail_markasread');
        unset_user_preference('message_provider_local_mail_mail_enabled');

        $result = external::get_preferences();

        external::validate_parameters(external::get_preferences_returns(), $result);

        $expected = [
            'perpage' => 10,
            'markasread' => false,
            'notifications' => ['email'],
        ];
        self::assertEquals($expected, $result);

        // Invalid perpage preference.

        set_user_preference('local_mail_mailsperpage', 4);

        $result = external::get_preferences();

        external::validate_parameters(external::get_preferences_returns(), $result);
        self::assertEquals(5, $result['perpage']);

        set_user_preference('local_mail_mailsperpage', 101);

        $result = external::get_preferences();

        external::validate_parameters(external::get_preferences_returns(), $result);
        self::assertEquals(100, $result['perpage']);
    }

    public function test_set_preferences(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);

        set_user_preference('local_mail_mailsperpage', 10);
        set_user_preference('local_mail_markasread', 0);
        set_user_preference('message_provider_local_mail_mail_enabled', 'popup,unknown');

        $result = external::set_preferences([
            'perpage' => '20',
            'markasread' => true,
            'notifications' => ['email'],
        ]);

        self::assertNull(external::set_preferences_returns());
        self::assertNull($result);
        self::assertEquals('20', get_user_preferences('local_mail_mailsperpage'));
        self::assertEquals('1', get_user_preferences('local_mail_markasread'));
        self::assertEquals('email', get_user_preferences('message_provider_local_mail_mail_enabled'));

        // Optional preferences.

        $result = external::set_preferences([]);

        self::assertNull($result);
        self::assertEquals('20', get_user_preferences('local_mail_mailsperpage'));
        self::assertEquals('1', get_user_preferences('local_mail_markasread'));

        // Invalid perpage.

        try {
            external::set_preferences(['perpage' => '4']);
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('"perpage" must be between 5 and 100', $e->debuginfo);
        }

        try {
            external::set_preferences(['perpage' => '101']);
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('"perpage" must be between 5 and 100', $e->debuginfo);
        }

        // Invalid processor name.

        try {
            $result = external::set_preferences(['notifications' => ['invalud']]);
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('"notifications" must contain message processor names', $e->debuginfo);
        }
    }

    public function test_get_courses(): void {
        $generator = $this->getDataGenerator();
        [$users] = self::generate_random_data(true);

        foreach ($users as $user) {
            $this->setUser($user->id);
            $expected = [];
            foreach (course::get_by_user($user) as $course) {
                $search = new message_search($user);
                $search->course = $course;
                $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
                $search->unread = true;
                $unread = $search->count();
                $search = new message_search($user);
                $search->course = $course;
                $search->roles = [message::ROLE_FROM];
                $search->draft = true;
                $drafts = $search->count();
                $expected[] = [
                    'id' => $course->id,
                    'shortname' => $course->shortname,
                    'fullname' => $course->fullname,
                    'visible' => $course->visible,
                    'groupmode' => $course->groupmode,
                    'unread' => $unread,
                    'drafts' => $drafts,
                ];
            }
            $result = external::get_courses();
            external::validate_parameters(external::get_courses_returns(), $result);
            self::assertEquals($expected, $result);
        }

        // User with no courses.

        $user = new user($generator->create_user());
        $this->setUser($user->id);
        self::assertEquals([], external::get_courses());
    }

    public function test_get_labels(): void {
        $generator = $this->getDataGenerator();
        [$users] = self::generate_random_data(true);

        foreach ($users as $user) {
            if ($user->deleted) {
                continue;
            }
            $this->setUser($user->id);

            $expected = [];
            foreach (label::get_by_user($user) as $label) {
                $search = new message_search($user);
                $search->label = $label;
                $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
                $search->unread = true;
                $expectedlabel = [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                    'unread' => $search->count(),
                    'courses' => [],
                ];
                foreach ($search->count_per_course() as $id => $unread) {
                    $expectedlabel['courses'][] = ['id' => $id, 'unread' => $unread];
                }
                $expected[] = $expectedlabel;
            }
            $result = external::get_labels();
            external::validate_parameters(external::get_labels_returns(), $result);
            self::assertEquals($expected, $result);
        }

        // User with no labels.

        $user = new user($generator->create_user());
        $this->setUser($user->id);
        self::assertEquals([], external::get_labels());
    }

    public function test_count_messages(): void {
        $generator = self::getDataGenerator();

        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            if ($search->user->deleted) {
                continue;
            }
            $this->setUser($search->user->id);
            $query = [];
            if ($search->course) {
                $query['courseid'] = $search->course->id;
            }
            if ($search->label) {
                $query['labelid'] = $search->label->id;
            }
            if ($search->draft !== null) {
                $query['draft'] = $search->draft;
            }
            if ($search->roles) {
                $query['roles'] = [];
                foreach ($search->roles as $role) {
                    $query['roles'][] = message::role_names()[$role];
                }
            }
            if ($search->unread !== null) {
                $query['unread'] = $search->unread;
            }
            if ($search->starred !== null) {
                $query['starred'] = $search->starred;
            }
            if ($search->deleted) {
                $query['deleted'] = true;
            }
            if ($search->content != '') {
                $query['content'] = $search->content;
            }
            if ($search->sendername != '') {
                $query['sendername'] = $search->sendername;
            }
            if ($search->recipientname != '') {
                $query['recipientname'] = $search->recipientname;
            }
            if ($search->withfilesonly) {
                $query['withfilesonly'] = true;
            }
            if ($search->maxtime) {
                $query['maxtime'] = $search->maxtime;
            }
            if ($search->startid) {
                $query['startid'] = $search->startid;
            }
            if ($search->stopid) {
                $query['stopid'] = $search->stopid;
            }
            if ($search->reverse) {
                $query['reverse'] = true;
            }

            $result = external::count_messages($query);
            external::validate_parameters(external::count_messages_returns(), $result);
            self::assertEquals($search->count(), $result, $search);
        }

        // Invalid course.
        self::setUser($users[0]->id);
        $course = $generator->create_course();
        $query = ['courseid' => $course->id];
        try {
            external::count_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($query['courseid'], $e->a);
        }

        // Invalid label.
        self::setUser($users[0]->id);
        $labels = label::get_by_user($users[1]);
        $query = ['labelid' => reset($labels)->id];
        try {
            external::count_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals($query['labelid'], $e->a);
        }

        // Invalid role.
        self::setUser($users[0]->id);
        $query = ['roles' => ['xx']];
        try {
            external::count_messages($query);
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('invalid role: xx', $e->debuginfo);
        }

        // Invalid startid.
        self::setUser($users[0]->id);
        $query = ['startid' => '123'];
        try {
            external::count_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($query['startid'], $e->a);
        }

        // Invalid stopid.
        self::setUser($users[0]->id);
        $query = ['stopid' => '123'];
        try {
            external::count_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($query['stopid'], $e->a);
        }
    }

    public function test_search_messages(): void {
        $generator = self::getDataGenerator();

        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            if ($search->user->deleted) {
                continue;
            }
            $this->setUser($search->user->id);
            $query = [];
            if ($search->course) {
                $query['courseid'] = $search->course->id;
            }
            if ($search->label) {
                $query['labelid'] = $search->label->id;
            }
            if ($search->draft !== null) {
                $query['draft'] = $search->draft;
            }
            if ($search->roles) {
                $query['roles'] = [];
                foreach ($search->roles as $role) {
                    $query['roles'][] = message::role_names()[$role];
                }
            }
            if ($search->unread !== null) {
                $query['unread'] = $search->unread;
            }
            if ($search->starred !== null) {
                $query['starred'] = $search->starred;
            }
            if ($search->deleted) {
                $query['deleted'] = true;
            }
            if ($search->content != '') {
                $query['content'] = $search->content;
            }
            if ($search->sendername != '') {
                $query['sendername'] = $search->sendername;
            }
            if ($search->recipientname != '') {
                $query['recipientname'] = $search->recipientname;
            }
            if ($search->withfilesonly) {
                $query['withfilesonly'] = true;
            }
            if ($search->maxtime) {
                $query['maxtime'] = $search->maxtime;
            }
            if ($search->startid) {
                $query['startid'] = $search->startid;
            }
            if ($search->stopid) {
                $query['stopid'] = $search->stopid;
            }
            if ($search->reverse) {
                $query['reverse'] = true;
            }

            $expected = external::search_messages_response($search->user, $search->get());
            $result = external::search_messages($query);
            external::validate_parameters(external::search_messages_returns(), $result);
            self::assertEquals($expected, $result, $search);

            // Offset and limit.
            $expected = external::search_messages_response($search->user, $search->get(5, 10));
            $result = external::search_messages($query, 5, 10);
            external::validate_parameters(external::search_messages_returns(), $result);
            self::assertEquals($expected, $result, $search . "\noffset: 5\n limit: 10");
        }

        // Invalid course.
        self::setUser($users[0]->id);
        $course = $generator->create_course();
        $query = ['courseid' => $course->id];
        try {
            external::search_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($query['courseid'], $e->a);
        }

        // Invalid label.
        self::setUser($users[0]->id);
        $labels = label::get_by_user($users[1]);
        $query = ['labelid' => reset($labels)->id];
        try {
            external::search_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals($query['labelid'], $e->a);
        }

        // Invalid startid.
        self::setUser($users[0]->id);
        $query = ['startid' => '123'];
        try {
            external::search_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($query['startid'], $e->a);
        }

        // Invalid stopid.
        self::setUser($users[0]->id);
        $query = ['stopid' => '123'];
        try {
            external::search_messages($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($query['stopid'], $e->a);
        }
    }

    public function test_get_message(): void {
        global $PAGE;

        $fs = get_file_storage();
        $renderer = $PAGE->get_renderer('local_mail');
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $context = $course->get_context();
        $user1 = new user($generator->create_user(['firstname' => 'Ada']));
        $user2 = new user($generator->create_user(['firstname' => 'Bob']));
        $user3 = new user($generator->create_user(['firstname' => 'Chloe']));
        $user4 = new user($generator->create_user(['firstname' => 'David']));
        $user5 = new user($generator->create_user(['firstname' => 'Emma']));
        $user6 = new user($generator->create_user(['firstname' => 'Felix']));
        $label1 = label::create($user1, 'Label 1');
        $label2 = label::create($user1, 'Label 2');
        $label3 = label::create($user2, 'Label 3');
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user3->id, $course->id);
        $generator->enrol_user($user4->id, $course->id);
        $generator->enrol_user($user5->id, $course->id);
        $time1 = time() - 24 * 3600;
        $time2 = time() - 3600;
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Message content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time1;
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        $message1 = message::create($data);
        $message1->send($time1);
        $data = message_data::reply($message1, $user2, false);
        $data->to = [$user1, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5, $user6];
        $data->content = 'Response content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time2;
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message2 = message::create($data);
        $message2->send($time2);
        $message2->set_labels($user1, [$label1, $label2]);
        $message2->set_labels($user2, [$label3]);
        self::setUser($user1->id);

        $result = external::get_message($message2->id);

        external::validate_parameters(external::get_message_returns(), $result);

        [$file1] = array_values($fs->get_area_files($context->id, 'local_mail', 'message', $message1->id, '', false));
        [$file2] = array_values($fs->get_area_files($context->id, 'local_mail', 'message', $message2->id, '', false));

        $PAGE->start_collecting_javascript_requirements();
        $expected = [
            'id' => $message2->id,
            'subject' => $message2->subject,
            'content' => $renderer->formatted_message_content($message2),
            'format' => FORMAT_HTML,
            'numattachments' => $message2->attachments,
            'draft' => $message2->draft,
            'time' => $message2->time,
            'shorttime' => $renderer->formatted_time($message2->time),
            'fulltime' => $renderer->formatted_time($message2->time, true),
            'unread' => $message2->unread($user1),
            'starred' => $message2->starred($user1),
            'deleted' => (bool) $message2->deleted($user1),
            'course' => [
                'id' => $course->id,
                'shortname' => external_format_string($course->shortname, $course->get_context()),
                'fullname' => external_format_string($course->fullname, $course->get_context()),
                'visible' => $course->visible,
                'groupmode' => $course->groupmode,
            ],
            'sender' => [
                'id' => $user2->id,
                'firstname' => $user2->firstname,
                'lastname' => $user2->lastname,
                'fullname' => $user2->fullname(),
                'pictureurl' => $user2->picture_url(),
                'profileurl' => $user2->profile_url($course),
                'sortorder' => $user2->sortorder(),
            ],
            'recipients' => [
                [
                    'type' => 'to',
                    'id' => $user1->id,
                    'firstname' => $user1->firstname,
                    'lastname' => $user1->lastname,
                    'fullname' => $user1->fullname(),
                    'pictureurl' => $user1->picture_url(),
                    'profileurl' => $user1->profile_url($course),
                    'sortorder' => $user1->sortorder(),
                    'isvalid' => false,
                ],
                [
                    'type' => 'to',
                    'id' => $user3->id,
                    'firstname' => $user3->firstname,
                    'lastname' => $user3->lastname,
                    'fullname' => $user3->fullname(),
                    'pictureurl' => $user3->picture_url(),
                    'profileurl' => $user3->profile_url($course),
                    'sortorder' => $user3->sortorder(),
                    'isvalid' => true,
                ],
                [
                    'type' => 'cc',
                    'id' => $user4->id,
                    'firstname' => $user4->firstname,
                    'lastname' => $user4->lastname,
                    'fullname' => $user4->fullname(),
                    'pictureurl' => $user4->picture_url(),
                    'profileurl' => $user4->profile_url($course),
                    'sortorder' => $user4->sortorder(),
                    'isvalid' => true,
                ],
            ],
            'attachments' => [
                [
                    'filepath' => $file2->get_filepath(),
                    'filename' => $file2->get_filename(),
                    'filesize' => (int) $file2->get_filesize(),
                    'mimetype' => $file2->get_mimetype(),
                    'fileurl' => $renderer->file_url($file2),
                    'iconurl' => $renderer->file_icon_url($file2),
                ],
            ],
            'references' => [
                [
                    'id' => $message1->id,
                    'subject' => $message1->subject,
                    'content' => $renderer->formatted_message_content($message1),
                    'format' => FORMAT_HTML,
                    'time' => $message1->time,
                    'shorttime' => $renderer->formatted_time($message1->time),
                    'fulltime' => $renderer->formatted_time($message1->time, true),
                    'sender' => [
                        'id' => $user1->id,
                        'firstname' => $user1->firstname,
                        'lastname' => $user1->lastname,
                        'fullname' => $user1->fullname(),
                        'pictureurl' => $user1->picture_url(),
                        'profileurl' => $user1->profile_url($course),
                        'sortorder' => $user1->sortorder(),
                    ],
                    'attachments' => [
                        [
                            'filepath' => $file1->get_filepath(),
                            'filename' => $file1->get_filename(),
                            'filesize' => (int) $file1->get_filesize(),
                            'mimetype' => $file1->get_mimetype(),
                            'fileurl' => $renderer->file_url($file1),
                            'iconurl' => $renderer->file_icon_url($file1),
                        ],
                    ],
                ],
            ],
            'labels' => [
                [
                    'id' => $label1->id,
                    'name' => $label1->name,
                    'color' => $label1->color,
                ],
                [
                    'id' => $label2->id,
                    'name' => $label2->name,
                    'color' => $label2->color,
                ],
            ],
            'javascript' => $PAGE->requires->get_end_code(),
        ];
        $PAGE->end_collecting_javascript_requirements();

        self::assertEquals($expected, $result);

        // User cannot view message.

        self::setUser($user6->id);
        try {
            external::get_message($message2->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message2->id, $e->a);
        }

        // Inexistent message.

        try {
            external::get_message(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_view_message(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);

        // Message from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);
        $message->set_unread($user1, true);
        $this->setUser($user1->id);
        $eventsink = $this->redirectEvents();

        $result = external::view_message($message->id);

        self::assertNull(external::view_message_returns());
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->unread($user1));
        self::assert_message_event('\local_mail\event\message_viewed', $message, $eventsink);

        // Message sent to the user.

        $message->set_unread($user2, true);
        $this->setUser($user2->id);
        $eventsink = $this->redirectEvents();

        $result = external::view_message($message->id);

        self::assertNull($result);
        self::assertFalse(message::get($message->id)->unread($user2));
        self::assert_message_event('\local_mail\event\message_viewed', $message, $eventsink);

        // Draft from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $draft = message::create($data);
        $draft->set_unread($user1, true);
        $this->setUser($user1->id);
        $eventsink = $this->redirectEvents();

        $result = external::view_message($draft->id);

        self::assertNull($result);
        self::assertFalse(message::get($draft->id)->unread($user1));
        self::assert_message_event('\local_mail\event\draft_viewed', $draft, $eventsink);

        // Draft to the user (no permission).

        $this->setUser($user2->id);

        try {
            external::view_message($draft->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($draft->id, $e->a);
        }

        // Invalid message.

        try {
            external::view_message(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_set_unread(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $this->setUser($user1->id);

        // Message from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::set_unread($message->id, '1');
        self::assertNull(external::set_unread_returns());
        self::assertNull($result);
        self::assertTrue(message::get($message->id)->unread($user1));

        $result = external::set_unread($message->id, '0');
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->unread($user1));

        // Message sent to the user.

        $this->setUser($user2->id);

        $result = external::set_unread($message->id, '0');
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->unread($user2));

        $result = external::set_unread($message->id, '1');
        self::assertNull($result);
        self::assertTrue(message::get($message->id)->unread($user2));

        $result = external::set_unread($message->id, '0');
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->unread($user2));

        // Draft to the user (no permission).

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $draft = message::create($data);

        try {
            external::set_unread($draft->id, '0');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($draft->id, $e->a);
        }

        // Invalid message.

        try {
            external::set_unread(123, '1');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_set_starred(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $this->setUser($user1->id);

        // Message from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::set_starred($message->id, '1');
        self::assertNull(external::set_starred_returns());
        self::assertNull($result);
        self::assertTrue(message::get($message->id)->starred($user1));

        $result = external::set_starred($message->id, '0');
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->starred($user1));

        // Message sent to the user.

        $this->setUser($user2->id);

        $result = external::set_starred($message->id, '1');
        self::assertNull($result);
        self::assertTrue(message::get($message->id)->starred($user2));

        $result = external::set_starred($message->id, '0');
        self::assertNull($result);
        self::assertFalse(message::get($message->id)->starred($user2));

        $result = external::set_starred($message->id, '1');
        self::assertNull($result);
        self::assertTrue(message::get($message->id)->starred($user2));

        // Draft to the user (no permission).

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $draft = message::create($data);

        try {
            external::set_starred($draft->id, '1');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($draft->id, $e->a);
        }

        // Invalid message.

        try {
            external::set_starred(123, '1');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_set_deleted(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $this->setUser($user1->id);

        // Message from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::set_deleted($message->id, '1');
        self::assertNull(external::set_deleted_returns());
        self::assertNull($result);
        self::assertEquals(message::DELETED, message::get($message->id)->deleted($user1));

        $result = external::set_deleted($message->id, '0');
        self::assertNull($result);
        self::assertEquals(message::NOT_DELETED, message::get($message->id)->deleted($user1));

        $result = external::set_deleted($message->id, '2');
        self::assertNull($result);
        self::assertEquals(message::DELETED_FOREVER, message::get($message->id)->deleted($user1));

        try {
            external::set_deleted($message->id, '0');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Message sent to the user.

        $this->setUser($user2->id);

        $result = external::set_deleted($message->id, '1');
        self::assertNull($result);
        self::assertEquals(message::DELETED, message::get($message->id)->deleted($user2));

        $result = external::set_deleted($message->id, '0');
        self::assertNull($result);
        self::assertEquals(message::NOT_DELETED, message::get($message->id)->deleted($user2));

        $result = external::set_deleted($message->id, '2');
        self::assertNull($result);
        self::assertEquals(message::DELETED_FOREVER, message::get($message->id)->deleted($user2));

        try {
            external::set_deleted($message->id, '0');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Draft to the user (no permission).

        $this->setUser($user2->id);

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $draft = message::create($data);

        try {
            external::set_deleted($message->id, '1');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Draft from the user.

        $this->setUser($user1->id);

        $eventsink = $this->redirectEvents();

        $result = external::set_deleted($draft->id, '1');
        self::assertNull($result);
        self::assertEquals(message::DELETED, message::get($draft->id)->deleted($user1));

        $result = external::set_deleted($draft->id, '0');
        self::assertNull($result);
        self::assertEquals(message::NOT_DELETED, message::get($draft->id)->deleted($user1));

        $result = external::set_deleted($draft->id, '2');
        self::assertNull($result);
        self::assert_record_count(0, 'messages', ['id' => $draft->id]);

        self::assert_message_event('\local_mail\event\draft_deleted', $draft, $eventsink);

        // Invalid message.

        try {
            external::set_deleted(123, '1');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Invalid deleted status.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        try {
            external::set_deleted($message->id, '4');
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('Invalid deleted status', $e->debuginfo);
        }
    }

    public function test_empty_trash(): void {
        $generator = self::getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $course3 = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user1->id, $course2->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);

        $data1 = message_data::new($course1, $user1);
        $data1->subject = 'Subject 1';
        $data1->to = [$user2];
        $message1 = message::create($data1);
        $message1->send($time);
        $message1->set_deleted($user1, message::DELETED);
        $message1->set_deleted($user2, message::DELETED);

        $data2 = message_data::new($course1, $user2);
        $data2->subject = 'Subject 2';
        $data2->to = [$user1];
        $message2 = message::create($data2);
        $message2->send($time);
        $message2->set_deleted($user1, message::DELETED);

        $data3 = message_data::new($course1, $user2);
        $data3->subject = 'Subject 3';
        $data3->to = [$user1];
        $message3 = message::create($data3);
        $message3->send($time);

        $data4 = message_data::new($course1, $user2);
        $data4->subject = 'Subject 4';
        $data4->to = [$user1];
        $message4 = message::create($data4);
        $message4->send($time);
        $message4->set_deleted($user1, message::DELETED_FOREVER);

        $data5 = message_data::new($course2, $user2);
        $data5->subject = 'Subject 5';
        $data5->to = [$user1];
        $message5 = message::create($data5);
        $message5->send($time);
        $message5->set_deleted($user1, message::DELETED);

        $data6 = message_data::new($course3, $user2);
        $data6->subject = 'Subject 6';
        $data6->to = [$user1];
        $message6 = message::create($data6);
        $message6->send($time);
        $message6->set_deleted($user1, message::DELETED);

        $draftdata = message_data::new($course1, $user1);
        $draftdata->subject = 'Draft';
        $draftdata->to = [$user2];
        $draft = message::create($draftdata);
        $draft->set_deleted($user1, message::DELETED);

        $this->setUser($user1->id);
        $eventsink = $this->redirectEvents();

        $result = external::empty_trash();

        self::assertNull(external::empty_trash_returns());
        self::assertNull($result);
        self::assertEquals(message::DELETED_FOREVER, message::get($message1->id)->deleted($user1));
        self::assertEquals(message::DELETED_FOREVER, message::get($message2->id)->deleted($user1));
        self::assertEquals(message::NOT_DELETED, message::get($message3->id)->deleted($user1));
        self::assertEquals(message::DELETED_FOREVER, message::get($message4->id)->deleted($user1));
        self::assertEquals(message::DELETED_FOREVER, message::get($message5->id)->deleted($user1));
        self::assertEquals(message::DELETED, message::get($message6->id)->deleted($user1));
        self::assert_record_count(0, 'messages', ['id' => $draft->id]);
        self::assert_message_event('\local_mail\event\draft_deleted', $draft, $eventsink);

        // Empty course trash.

        $data7 = message_data::new($course1, $user2);
        $data7->subject = 'Subject 7';
        $data7->to = [$user1];
        $message7 = message::create($data7);
        $message7->send($time);
        $message7->set_deleted($user1, message::DELETED);

        $data8 = message_data::new($course2, $user2);
        $data8->subject = 'Subject 8';
        $data8->to = [$user1];
        $message8 = message::create($data8);
        $message8->send($time);
        $message8->set_deleted($user1, message::DELETED);

        $result = external::empty_trash($course1->id);

        self::assertNull($result);
        self::assertEquals(message::DELETED_FOREVER, message::get($message7->id)->deleted($user1));
        self::assertEquals(message::DELETED, message::get($message8->id)->deleted($user1));

        // User not enrolled in course.

        try {
            external::empty_trash($course3->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($course3->id, $e->a);
        }

        // Inexistent course.

        try {
            external::empty_trash(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_create_label(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $this->setUser($user1->id);

        // Empty color.

        $result = external::create_label('Label 1');

        external::validate_parameters(external::create_label_returns(), $result);
        $label1 = label::get($result);
        self::assertNotNull($label1);
        self::assertEquals($user1->id, $label1->userid);
        self::assertEquals('Label 1', $label1->name);
        self::assertEquals('', $label1->color);

        // Messages from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message1 = message::create($data);
        $message1->send($time);
        $message1->set_labels($user1, [$label1]);
        $message2 = message::create($data);
        $message2->send($time);
        $message3 = message::create($data);
        $message3->send($time);

        $result = external::create_label('Label 2', 'blue', [$message1->id, $message2->id]);

        external::validate_parameters(external::create_label_returns(), $result);
        $label2 = label::get($result);
        self::assertNotNull($label2);
        self::assertEquals($user1->id, $label2->userid);
        self::assertEquals('Label 2', $label2->name);
        self::assertEquals('blue', $label2->color);
        self::assertEquals([$label1, $label2], message::get($message1->id)->get_labels($user1));
        self::assertEquals([$label2], message::get($message2->id)->get_labels($user1));
        self::assertEquals([], message::get($message3->id)->get_labels($user1));

        // Message sent to the user.

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::create_label('Label 3', 'red', [$message->id]);

        external::validate_parameters(external::create_label_returns(), $result);
        $label3 = label::get($result);
        self::assertNotNull($label3);
        self::assertEquals($user1->id, $label3->userid);
        self::assertEquals('Label 3', $label3->name);
        self::assertEquals('red', $label3->color);
        self::assertTrue(message::get($message->id)->has_label($label3));

        // Draft from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $message = message::create($data);

        $result = external::create_label('Label 4', 'green', [$message->id]);

        external::validate_parameters(external::create_label_returns(), $result);
        $label4 = label::get($result);
        self::assertNotNull($label4);
        self::assertEquals($user1->id, $label4->userid);
        self::assertEquals('Label 4', $label4->name);
        self::assertEquals('green', $label4->color);
        self::assertTrue(message::get($message->id)->has_label($label4));

        // Draft to the user (no permission).

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);

        try {
            $result = external::create_label('Label 5', 'yellow', [$message->id]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
            self::assertCount(4, label::get_by_user($user1));
        }

        // Empty name.

        try {
            external::create_label('', 'blue');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('erroremptylabelname', $e->errorcode);
        }

        // Duplicated name.

        try {
            external::create_label('Label 1', 'blue');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorrepeatedlabelname', $e->errorcode);
        }

        // Invalid color.

        try {
            external::create_label('Label 6', 'abc');
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('invalid color: abc', $e->debuginfo);
        }

        // Invalid message.

        try {
            external::create_label('Label 7', 'blue', [123]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_update_label(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        self::setUser($user1->id);

        $label1 = label::create($user1, 'Label 1', 'blue');
        $label2 = label::create($user1, 'Label 2', 'red');
        $label3 = label::create($user2, 'Label 3', 'yellow');

        $result = external::update_label($label1->id, 'Updated 1', 'green');

        self::assertNull($result);
        $label1 = label::get($label1->id);
        self::assertEquals($user1->id, $label1->userid);
        self::assertEquals('Updated 1', $label1->name);
        self::assertEquals('green', $label1->color);

        // Unchaged name.

        $result = external::update_label($label1->id, 'Updated 1', 'yellow');

        self::assertNull(external::update_label_returns());
        self::assertNull($result);
        $label1 = label::get($label1->id);
        self::assertEquals($user1->id, $label1->userid);
        self::assertEquals('Updated 1', $label1->name);
        self::assertEquals('yellow', $label1->color);

        // Empty color.

        $result = external::update_label($label1->id, 'Label 1');

        self::assertNull($result);
        $label1 = label::get($label1->id);
        self::assertEquals($user1->id, $label1->userid);
        self::assertEquals('Label 1', $label1->name);
        self::assertEquals('', $label1->color);

        // Invalid label.

        try {
            external::update_label(123, 'Label 1', 'blue');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Label of another user.

        try {
            external::update_label($label3->id, 'Label 3', 'yellow');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals($label3->id, $e->a);
        }

        // Empty name.

        try {
            external::update_label($label1->id, '', 'blue');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('erroremptylabelname', $e->errorcode);
        }

        // Duplicated name.

        try {
            external::update_label($label1->id, 'Label 2', 'blue');
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorrepeatedlabelname', $e->errorcode);
        }

        // Invalid color.

        try {
            external::update_label($label1->id, 'Label 1', 'abc');
            self::fail();
        } catch (\invalid_parameter_exception $e) {
            self::assertEquals('invalid color: abc', $e->debuginfo);
        }
    }

    public function test_delete_label(): void {
        $generator = self::getDataGenerator();
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $label1 = label::create($user1, 'Label 1', 'blue');
        $label2 = label::create($user2, 'Label 2', 'red');
        self::setUser($user1->id);

        $result = external::delete_label($label1->id);

        self::assertNull(external::delete_label_returns());
        self::assertNull($result);
        self::assert_record_count(0, 'labels', ['id' => $label1->id]);

        // Invalid label.

        try {
            external::delete_label(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Label of another user.

        try {
            external::delete_label($label2->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals($label2->id, $e->a);
        }
    }

    public function test_set_labels(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $label1 = label::create($user1, 'Label 1');
        $label2 = label::create($user1, 'Label 2');
        $label3 = label::create($user1, 'Label 3');
        $label4 = label::create($user2, 'Label 4');
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $this->setUser($user1->id);

        // Message from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::set_labels($message->id, [$label1->id, $label2->id]);
        self::assertNull(external::set_labels_returns());
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label1, $label2], $message->get_labels($user1));

        $result = external::set_labels($message->id, [$label2->id, $label3->id]);
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label2, $label3], $message->get_labels($user1));

        // Message sent to the user.

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);

        $result = external::set_labels($message->id, [$label1->id, $label2->id]);
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label1, $label2], $message->get_labels($user1));

        $result = external::set_labels($message->id, [$label2->id, $label3->id]);
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label2, $label3], $message->get_labels($user1));

        // Draft from the user.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $message = message::create($data);

        $result = external::set_labels($message->id, [$label1->id, $label2->id]);
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label1, $label2], $message->get_labels($user1));

        $result = external::set_labels($message->id, [$label2->id, $label3->id]);
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals([$label2, $label3], $message->get_labels($user1));

        // Draft to the user (no permission).

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);

        try {
            $result = external::set_labels($message->id, [$label1->id, $label2->id]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Label of another user.

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);
        try {
            external::set_labels($message->id, [$label4->id]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals($label4->id, $e->a);
        }

        // Invalid label.

        $data = message_data::new($course, $user2);
        $data->to = [$user1];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($time);
        try {
            external::set_labels($message->id, [123]);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorlabelnotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Invalid message.

        try {
            external::set_labels(123, ['1']);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_get_roles(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user = new user($generator->create_user());
        $generator->enrol_user($user->id, $course->id);
        $this->setUser($user->id);

        $expected = [];
        foreach ($course->get_viewable_roles($user) as $id => $name) {
            $expected[] = ['id' => $id, 'name' => $name];
        }
        $result = external::get_roles($course->id);
        external::validate_parameters(external::get_roles_returns(), $result);
        self::assertEquals($expected, $result);

        // User not enrolled in course.

        $course = new course($generator->create_course());
        try {
            external::get_roles($course->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($course->id, $e->a);
        }

        // Inexistent course.

        try {
            external::get_roles(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_get_groups(): void {
        $generator = self::getDataGenerator();
        $course = new course($generator->create_course(['groupmode' => SEPARATEGROUPS]));
        $group1 = $generator->create_group(['courseid' => $course->id]);
        $group2 = $generator->create_group(['courseid' => $course->id]);
        $group3 = $generator->create_group(['courseid' => $course->id]);
        $user = new user($generator->create_user());
        $generator->enrol_user($user->id, $course->id);
        $generator->create_group_member(['userid' => $user->id, 'groupid' => $group1->id]);
        $generator->create_group_member(['userid' => $user->id, 'groupid' => $group2->id]);
        self::setUser($user->id);

        $expected = [
            ['id' => $group1->id, 'name' => $group1->name],
            ['id' => $group2->id, 'name' => $group2->name],
        ];
        $result = external::get_groups($course->id);
        external::validate_parameters(external::get_groups_returns(), $result);
        self::assertEquals($expected, $result);

        // User not enrolled in course.

        $course = new course($generator->create_course());
        try {
            external::get_groups($course->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($course->id, $e->a);
        }

        // Inexistent course.

        try {
            external::get_groups(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_search_users(): void {
        $generator = self::getDataGenerator();

        [$users] = self::generate_random_data(false);

        foreach (self::user_search_cases($users) as $search) {
            if ($search->user->deleted) {
                continue;
            }
            $this->setUser($search->user->id);
            $query = ['courseid' => $search->course->id];
            if ($search->roleid) {
                $query['roleid'] = $search->roleid;
            }
            if ($search->groupid) {
                $query['groupid'] = $search->groupid;
            }
            if (\core_text::strlen($search->fullname)) {
                $query['fullname'] = $search->fullname;
            }
            if ($search->include) {
                $query['include'] = $search->include;
            }

            if (!$search->user->can_view_group($search->course, $search->groupid)) {
                // Invalid group.
                try {
                    external::search_users($query);
                    self::fail();
                } catch (exception $e) {
                    self::assertEquals('errorgroupnotfound', $e->errorcode);
                }
                continue;
            }

            // No offset or limit.

            $expected = external::search_users_response($search->course, $search->get());
            $result = external::search_users($query);
            external::validate_parameters(external::search_users_returns(), $result);
            self::assertEquals($expected, $result, $search);

            // Offset and limit.

            $expected = external::search_users_response($search->course, $search->get(5, 10));
            $result = external::search_users($query, 5, 10);
            external::validate_parameters(external::search_users_returns(), $result);
            self::assertEquals($expected, $result, $search . "\noffset: 5\n limit: 10");
        }

        // User not enrolled in course.

        $course = new course($generator->create_course());
        $query = ['courseid' => $course->id];
        try {
            external::search_users($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($query['courseid'], $e->a);
        }

        // Inexistent course.

        $query = ['courseid' => 123];
        try {
            external::search_users($query);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($query['courseid'], $e->a);
        }
    }

    public function test_get_message_form(): void {
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = 'Message content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time;
        self::create_draft_file($data->draftitemid, 'file.txt', 'File content');
        $message = message::create($data);
        self::setUser($user1->id);

        $editortextpattern = '/.*<textarea[^>]* name="content\[text\]"[^>]*>([^<]*)<\/textarea>.*/';
        $editorformatpattern = '/.*<input[^>]* name="content\[format\]"[^>]* value="(\d+)".*/';
        $editoritemidpattern = '/.*<input[^>]* name="content\[itemid\]"[^>]* value="(\d+)".*/';
        $filemanagerpattern = '/.*<input[^>]* value="(\d+)"[^>]* name="attachments".*/';

        $result = external::get_message_form($message->id);

        external::validate_parameters(external::get_message_form_returns(), $result);
        $html = format_text($data->content, $data->format, ['filter' => false, 'para' => false]);
        self::assertGreaterThan(0, $result['draftitemid']);
        self::assert_draft_files(['file.txt' => 'File content'], $result['draftitemid']);
        preg_match($editortextpattern, $result['editorhtml'], $matches);
        self::assertCount(2, $matches);
        self::assertEquals($html, $matches[1]);
        preg_match($editorformatpattern, $result['editorhtml'], $matches);
        self::assertCount(2, $matches);
        self::assertEquals(FORMAT_HTML, $matches[1]);
        preg_match($editoritemidpattern, $result['editorhtml'], $matches);
        self::assertCount(2, $matches);
        self::assertEquals($result['draftitemid'], $matches[1]);
        preg_match($filemanagerpattern, $result['filemanagerhtml'], $matches);
        self::assertCount(2, $matches);
        self::assertEquals($result['draftitemid'], $matches[1]);
        self::assertStringContainsString('<script', $result['javascript']);

        // Inexistent message.

        try {
            external::get_message_form(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Non-editable message.

        $message->send($time);
        try {
            external::get_message_form($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }
    }

    public function test_create_message(): void {
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user = new user($generator->create_user());
        $generator->enrol_user($user->id, $course->id);
        $now = time();
        self::setUser($user->id);
        $eventsink = $this->redirectEvents();

        $result = external::create_message($course->id);

        external::validate_parameters(external::create_message_returns(), $result);
        $draft = message::get($result);
        self::assertNotNull($draft);
        self::assertTrue($draft->draft);
        self::assertEquals($course, $draft->course);
        self::assertEquals('', $draft->subject);
        self::assertEquals('', $draft->content);
        self::assertEquals(FORMAT_HTML, $draft->format);
        self::assertEquals($user->id, $draft->sender()->id);
        self::assertGreaterThanOrEqual($now, $draft->time);

        self::assert_message_event('\local_mail\event\draft_created', $draft, $eventsink);

        // User not enrolled in course.

        $course = new course($generator->create_course());
        try {
            external::create_message($course->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($course->id, $e->a);
        }

        // Inexistent course.

        try {
            external::create_message(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }

    public function test_reply_message(): void {
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $now = time();
        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = 'Message content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time;
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message = message::create($data);
        $message->send($time);
        self::setUser($user2->id);

        // Reply to sender.

        $result = external::reply_message($message->id, false);

        external::validate_parameters(external::reply_message_returns(), $result);
        $draft = message::get($result);
        self::assertNotNull($draft);
        self::assertTrue($draft->draft);
        self::assertEquals($data->course, $draft->course);
        self::assertEquals('RE: ' . $data->subject, $draft->subject);
        self::assertEquals('', $draft->content);
        self::assertEquals(FORMAT_HTML, $draft->format);
        self::assertEquals($user2, $draft->sender());
        self::assertEqualsCanonicalizing([$user1], $draft->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_BCC));
        self::assertGreaterThanOrEqual($now, $draft->time);
        self::assertEquals([$message->id => $message], $draft->get_references());
        self::assert_attachments([], $draft);

        // Reply to all.

        $result = external::reply_message($message->id, true);

        external::validate_parameters(external::reply_message_returns(), $result);
        $draft = message::get($result);
        self::assertNotNull($draft);
        self::assertEquals($user2, $draft->sender());
        self::assertEqualsCanonicalizing([$user1], $draft->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([$user3, $user4], $draft->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_BCC));

        // User cannot view message.

        $course = new course($generator->create_course());
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($data->time);
        try {
            external::reply_message($message->id, false);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Inexistent message.

        try {
            external::reply_message(123, false);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // User not enrolled in course.

        $course = new course($generator->create_course());
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($data->time);
        try {
            external::reply_message($message->id, false);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }
    }

    public function test_forward_message(): void {
        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $now = time();
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $data->content = 'Message content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time;
        self::create_draft_file($data->draftitemid, 'file1.txt', 'File 1');
        self::create_draft_file($data->draftitemid, 'file2.txt', 'File 2');
        $message = message::create($data);
        $message->send($time);
        self::setUser($user2->id);

        $result = external::forward_message($message->id);

        external::validate_parameters(external::forward_message_returns(), $result);
        $draft = message::get($result);
        self::assertNotNull($draft);
        self::assertTrue($draft->draft);
        self::assertEquals($data->course, $draft->course);
        self::assertEquals('FW: ' . $data->subject, $draft->subject);
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
        self::assertEquals($expected, $draft->content);
        self::assertEquals(FORMAT_HTML, $draft->format);
        self::assertEquals($user2, $draft->sender());
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([], $draft->recipients(message::ROLE_BCC));
        self::assertGreaterThanOrEqual($now, $draft->time);
        self::assertEquals([], $draft->get_references());
        self::assert_attachments(['file1.txt' => 'File 1', 'file2.txt' => 'File 2'], $draft);

        // User cannot view message.

        $course = new course($generator->create_course());
        $data = message_data::new($course, $user1);
        $data->to = [$user3];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($data->time);
        try {
            external::forward_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Inexistent message.

        try {
            external::forward_message(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // User not enrolled in course.

        $course = new course($generator->create_course());
        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $data->subject = 'Subject';
        $message = message::create($data);
        $message->send($data->time);
        try {
            external::forward_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }
    }

    public function test_update_message(): void {
        $generator = $this->getDataGenerator();
        $course1 = new course($generator->create_course());
        $course2 = new course($generator->create_course());
        $course3 = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user1->id, $course2->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $now = time();
        self::setUser($user1->id);

        $data = message_data::new($course1, $user1);
        $data->time = $time;
        $message = message::create($data);

        $data = [
            'courseid' => $course2->id,
            'to' => [$user2->id, $user3->id],
            'cc' => [$user4->id],
            'bcc' => [$user5->id],
            'subject' => 'Message subject',
            'content' => 'Message content',
            'format' => FORMAT_HTML,
            'draftitemid' => file_get_unused_draft_itemid(),
        ];
        self::create_draft_file($data['draftitemid'], 'file1.txt', 'File 1');
        self::create_draft_file($data['draftitemid'], 'file2.txt', 'File 2');
        $eventsink = $this->redirectEvents();

        $result = external::update_message($message->id, $data);

        self::assertNull(external::update_message_returns());
        self::assertNull($result);
        $message = message::get($message->id);
        self::assertEquals($course2, $message->course);
        self::assertEquals('Message subject', $message->subject);
        self::assertEquals('Message content', $message->content);
        self::assertEquals(FORMAT_HTML, $message->format);
        self::assertGreaterThanOrEqual($now, $message->time);
        self::assertEquals($user1, $message->sender());
        self::assertEqualsCanonicalizing([$user2, $user3], $message->recipients(message::ROLE_TO));
        self::assertEqualsCanonicalizing([$user4], $message->recipients(message::ROLE_CC));
        self::assertEqualsCanonicalizing([$user5], $message->recipients(message::ROLE_BCC));
        self::assert_attachments([
            'file1.txt' => 'File 1',
            'file2.txt' => 'File 2',
        ], $message);

        self::assert_message_event('\local_mail\event\draft_updated', $message, $eventsink);

        // User cannot view message.

        $message = message::create(message_data::new($course3, $user1));
        try {
            external::update_message($message->id, $data);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Inexistent message.

        try {
            external::update_message(123, $data);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // User not enrolled in course.

        $message = message::create(message_data::new($course1, $user1));
        $data['courseid'] = $course3->id;
        try {
            external::update_message($message->id, $data);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorcoursenotfound', $e->errorcode);
            self::assertEquals($data['courseid'], $e->a);
        }

        // Reply with a different course.

        $data = message_data::new($course1, $user1);
        $data->to = [$user2];
        $data->subject = 'Message subject';
        $message = message::create($data);
        $message->send($time);
        $reply = message::create(message_data::reply($message, $user1, false));
        $data = [
            'courseid' => $course2->id,
            'to' => [],
            'cc' => [],
            'bcc' => [],
            'subject' => 'Message subject',
            'content' => 'Message content',
            'format' => FORMAT_HTML,
            'draftitemid' => file_get_unused_draft_itemid(),
        ];

        external::update_message($reply->id, $data);

        self::assertEquals($message->course->id, message::get($reply->id)->course->id);
    }

    public function test_send_message(): void {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_mail');

        $generator = $this->getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $user3 = new user($generator->create_user());
        $user4 = new user($generator->create_user());
        $user5 = new user($generator->create_user());
        $user6 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user3->id, $course->id);
        $generator->enrol_user($user4->id, $course->id);
        $generator->enrol_user($user5->id, $course->id);
        set_user_preference('local_mail_markasread', 1, $user3->id);
        $time = make_timestamp(2021, 10, 11, 12, 0);
        $now = time();
        $data = message_data::new($course, $user1);
        $data->to = [$user2, $user3];
        $data->cc = [$user4];
        $data->bcc = [$user5];
        $data->subject = 'Subject';
        $data->content = 'Message content';
        $data->format = FORMAT_PLAIN;
        $data->time = $time;
        self::create_draft_file($data->draftitemid, 'file.txt', 'File content');
        $message = message::create($data);
        self::setUser($user1->id);
        $notificationsink = $this->redirectMessages();
        $eventsink = $this->redirectEvents();

        $result = external::send_message($message->id);

        self::assertNull($result);
        self::assertNull(external::send_message_returns());

        $message = message::get($message->id);
        self::assertFalse($message->draft);
        self::assertGreaterThanOrEqual($now, $message->time);
        self::assertTrue($message->unread($user2));
        self::assertFalse($message->unread($user3));
        self::assertTrue($message->unread($user4));
        self::assertTrue($message->unread($user5));

        $notificationsink->close();
        $notifications = $notificationsink->get_messages();
        $recipients = $message->recipients();
        self::assertEquals(count($recipients), count($notifications));
        foreach ($recipients as $i => $user) {
            $expected = $renderer->notification($message, $user);
            self::assertEquals($expected->notification, $notifications[$i]->notification);
            self::assertEquals($expected->component, $notifications[$i]->component);
            self::assertEquals($expected->name, $notifications[$i]->eventtype);
            self::assertEquals($expected->userfrom, $notifications[$i]->useridfrom);
            self::assertEquals($expected->userto, $notifications[$i]->useridto);
            self::assertEquals($expected->subject, $notifications[$i]->subject);
            self::assertEquals($expected->fullmessage, $notifications[$i]->fullmessage);
            self::assertEquals($expected->fullmessageformat, $notifications[$i]->fullmessageformat);
            self::assertEquals($expected->fullmessagehtml, $notifications[$i]->fullmessagehtml);
            self::assertEquals($expected->smallmessage, $notifications[$i]->smallmessage);
            self::assertEquals($expected->contexturl, $notifications[$i]->contexturl);
            self::assertEquals($expected->contexturlname, $notifications[$i]->contexturlname);
        }

        self::assert_message_event('\local_mail\event\message_sent', $message, $eventsink);

        // User cannot edit message.

        try {
            external::send_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals($message->id, $e->a);
        }

        // Inexistent message.

        try {
            external::send_message(123);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Empty subject.

        $data = message_data::new($course, $user1);
        $data->to = [$user2];
        $message = message::create($data);
        try {
            external::send_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('erroremptysubject', $e->errorcode);
        }

        // No recipients.

        $data = message_data::new($course, $user1);
        $data->subject = 'Subject';
        $message = message::create($data);
        try {
            external::send_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('erroremptyrecipients', $e->errorcode);
        }

        // Invalid recipients.

        $data = message_data::new($course, $user1);
        $data->subject = 'Subject';
        $data->to = [$user6];
        $message = message::create($data);
        try {
            external::send_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errorinvalidrecipients', $e->errorcode);
        }

        // Too many recipients.

        set_config('maxrecipients', '3', 'local_mail');
        $data = message_data::new($course, $user1);
        $data->subject = 'Subject';
        $data->to = [$user2, $user3, $user4, $user5];
        $message = message::create($data);
        try {
            external::send_message($message->id);
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errortoomanyrecipients', $e->errorcode);
            self::assertEquals(3, $e->a);
        }
    }
}
