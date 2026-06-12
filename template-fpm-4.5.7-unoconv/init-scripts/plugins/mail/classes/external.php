<?php
/*
 * SPDX-FileCopyrightText: 2017-2025 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

class external extends \external_api {
    public static function get_settings_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_settings() {
        self::validate_call(self::get_settings_parameters(), func_get_args());

        return (array) settings::get();
    }

    public static function get_settings_returns() {
        return new \external_single_structure([
            'enablebackup' => new \external_value(
                PARAM_BOOL,
                'Backup and restore enabled'
            ),
            'maxrecipients' => new \external_value(
                PARAM_INT,
                'Maximum number of recipients allowed per message'
            ),
            'usersearchlimit' => new \external_value(
                PARAM_INT,
                'Maximum number of results displayed in the user search'
            ),
            'maxfiles' => new \external_value(
                PARAM_INT,
                'Maximum size of attachments allowed per message'
            ),
            'maxbytes' => new \external_value(
                PARAM_INT,
                'Maximum size of attachments allowed per message'
            ),
            'autosaveinterval' => new \external_value(
                PARAM_INT,
                'Interval in seconds between automatic draft saves'
            ),
            'globaltrays' => new \external_multiple_structure(
                new \external_value(PARAM_ALPHA, 'Type of ray: "starred", "sent", "drafts" or "trash"'),
                'Global trays displayed in menus'
            ),
            'coursetrays' => new \external_value(
                PARAM_ALPHA,
                'Course trays displayed in menus: "none", "unread", or "all"'
            ),
            'coursetraysname' => new \external_value(
                PARAM_ALPHA,
                'Type of course name displayed in menus: "shortname" or "fullname"'
            ),
            'coursebadges' => new \external_value(
                PARAM_ALPHA,
                'Type of course name displayed in messagess: "hidden", "shortname", or "fullname"'
            ),
            'coursebadgeslength' => new \external_value(
                PARAM_INT,
                'Course badges are truncated to this approximate length'
            ),
            'filterbycourse' => new \external_value(
                PARAM_ALPHA,
                'Type of course name used in the filter by course: "hidden", "shortname" or "fullname"'
            ),
            'incrementalsearch' => new \external_value(
                PARAM_BOOL,
                'Incremental search enabled',
            ),
            'incrementalsearchlimit' => new \external_value(
                PARAM_INT,
                'Maximum number of recent messages included in incremental search',
            ),
            'courselink' => new \external_value(
                PARAM_ALPHA,
                'Type of course name displayed in the course link: "hidden", "shortname" or "fullname"'
            ),
            'messageprocessors' => new \external_multiple_structure(
                new \external_single_structure([
                    'name' => new \external_value(PARAM_PLUGIN, 'Name of the message processor'),
                    'displayname' => new \external_value(PARAM_RAW, 'Display name of the message processor'),
                    'enabled' => new \external_value(PARAM_BOOL, 'Message processor is enabled'),
                    'locked' => new \external_value(PARAM_BOOL, 'Message processor is locked'),
                ])
            ),
        ]);
    }

    public static function get_strings_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_strings() {
        self::validate_call(self::get_strings_parameters(), func_get_args());

        return output\strings::get_all();
    }

    public static function get_strings_returns() {
        $stringkeys = [];
        foreach (output\strings::get_ids() as $id) {
            $stringkeys[$id] = new \external_value(PARAM_RAW, 'Localized content of language string "' . $id . '"');
        }
        return new \external_single_structure($stringkeys);
    }

    public static function get_preferences_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_preferences() {
        self::validate_call(self::get_preferences_parameters(), func_get_args());

        return self::get_preferences_raw();
    }

    public static function get_preferences_raw() {
        $result = [
            'perpage' => max(5, min(100, (int) get_user_preferences('local_mail_mailsperpage', 10))),
            'markasread' => (bool) get_user_preferences('local_mail_markasread', 0),
            'notifications' => [],
        ];

        if (!get_config('message', 'local_mail_mail_disable')) {
            $configenabled = get_config('message', 'message_provider_local_mail_mail_enabled');
            $prefenabled = get_user_preferences('message_provider_local_mail_mail_enabled');
            foreach (get_message_processors(true) as $processor) {
                $enabled = explode(',', $prefenabled === null ? $configenabled : $prefenabled);
                if (array_search($processor->name, $enabled) !== false) {
                    $result['notifications'][] = $processor->name;
                }
            }
        }

        return $result;
    }

    public static function get_preferences_returns() {
        return new \external_single_structure([
            'perpage' => new \external_value(PARAM_INT, 'Number of messages to display per page (5-100)'),
            'markasread' => new \external_value(PARAM_BOOL, 'Mark new messages as read if a notification is sent'),
            'notifications' => new \external_multiple_structure(
                new \external_value(PARAM_PLUGIN, 'Name of the message processor')
            ),
        ]);
    }

    public static function set_preferences_parameters() {
        return new \external_function_parameters([
            'preferences' => new \external_single_structure([
                'perpage' => new \external_value(
                    PARAM_INT,
                    'Number of messages to display per page (5-100)',
                    VALUE_OPTIONAL
                ),
                'markasread' => new \external_value(
                    PARAM_BOOL,
                    'Mark new messages as read if a notification is sent',
                    VALUE_OPTIONAL
                ),
                'notifications' => new \external_multiple_structure(
                    new \external_value(PARAM_PLUGIN, 'Name of the message processor'),
                    'Notifications',
                    VALUE_OPTIONAL
                ),
            ]),
        ]);
    }

    public static function set_preferences() {
        $params = self::validate_call(self::set_preferences_parameters(), func_get_args());

        if (isset($params['preferences']['perpage'])) {
            if ($params['preferences']['perpage'] < 5 || $params['preferences']['perpage'] > 100) {
                throw new \invalid_parameter_exception('"perpage" must be between 5 and 100');
            }
        }

        if (isset($params['preferences']['perpage'])) {
            set_user_preference('local_mail_mailsperpage', $params['preferences']['perpage']);
        }

        if (isset($params['preferences']['markasread'])) {
            set_user_preference('local_mail_markasread', $params['preferences']['markasread']);
        }

        if (isset($params['preferences']['notifications'])) {
            $processornames = array_column(get_message_processors(true), 'name');
            if (array_diff($params['preferences']['notifications'], $processornames)) {
                throw new \invalid_parameter_exception('"notifications" must contain message processor names');
            }
            $enabled = implode(',', $params['preferences']['notifications']);
            set_user_preference('message_provider_local_mail_mail_enabled', $enabled);
        }

        return null;
    }

    public static function set_preferences_returns() {
        return null;
    }

    public static function get_courses_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_courses() {
        self::validate_call(self::get_courses_parameters(), func_get_args());

        return self::get_courses_raw();
    }

    public static function get_courses_raw() {
        $user = user::current();
        $courses = course::get_by_user($user);

        if (!$courses) {
            return [];
        }

        $search = new message_search($user);
        $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
        $search->unread = true;
        $unread = $search->count_per_course();

        $search = new message_search($user);
        $search->roles = [message::ROLE_FROM];
        $search->draft = true;
        $drafts = $search->count_per_course();

        $result = [];
        foreach ($courses as $course) {
            $context = $course->get_context();
            $result[] = [
                'id' => $course->id,
                'shortname' => external_format_string($course->shortname, $context),
                'fullname' => external_format_string($course->fullname, $context),
                'visible' => $course->visible,
                'groupmode' => $course->groupmode,
                'unread' => $unread[$course->id] ?? 0,
                'drafts' => $drafts[$course->id] ?? 0,
            ];
        }

        return $result;
    }

    public static function get_courses_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the course'),
                'shortname' => new \external_value(PARAM_RAW, 'Short name of the course'),
                'fullname' => new \external_value(PARAM_RAW, 'Full name of the course'),
                'visible' => new \external_value(PARAM_BOOL, 'Course visibility'),
                'groupmode' => new \external_value(PARAM_INT, 'Group mode: 0 (no), 1 (separate) or 2 (visible)'),
                'unread' => new \external_value(PARAM_INT, 'Number of unread messages'),
                'drafts' => new \external_value(PARAM_INT, 'Number of drafts'),
            ])
        );
    }

    public static function get_labels_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_labels() {
        self::validate_call(self::get_labels_parameters(), func_get_args());

        return self::get_labels_raw();
    }

    public static function get_labels_raw() {
        $result = [];

        $user = user::current();

        $search = new message_search($user);
        $search->roles = [message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC];
        $search->unread = true;
        $unread = $search->count_per_label();

        foreach (label::get_by_user($user) as $label) {
            $labelresult = [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
                'unread' => array_sum($unread[$label->id] ?? []),
                'courses' => [],
            ];
            foreach ($unread[$label->id] ?? [] as $courseid => $courseunread) {
                $labelresult['courses'][] = ['id' => $courseid, 'unread' => $courseunread];
            }
            $result[] = $labelresult;
        }

        return $result;
    }

    public static function get_labels_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the label'),
                'name' => new \external_value(PARAM_RAW, 'Nane of the label'),
                'color' => new \external_value(PARAM_ALPHA, 'Color of the label'),
                'unread' => new \external_value(PARAM_INT, 'Number of unread messages'),
                'courses' => new \external_multiple_structure(
                    new \external_single_structure([
                        'id' => new \external_value(PARAM_INT, 'Id of the course'),
                        'unread' => new \external_value(PARAM_INT, 'Number of unread messages'),
                    ]),
                ),
            ])
        );
    }

    private static function message_query_parameters() {
        return new \external_single_structure([
            'courseid' => new \external_value(
                PARAM_INT,
                'Search messages in this course',
                VALUE_DEFAULT,
                0
            ),
            'labelid' => new \external_value(
                PARAM_INT,
                'Search messages with this label',
                VALUE_DEFAULT,
                0
            ),
            'draft' => new \external_value(
                PARAM_BOOL,
                'Search messages with this draft status',
                VALUE_OPTIONAL
            ),
            'roles' => new \external_multiple_structure(
                new \external_value(PARAM_ALPHA, 'Role: "from", "to", "cc" or "bcc"'),
                'Search messages in which the user has one of these roles',
                VALUE_DEFAULT,
                []
            ),
            'unread' => new \external_value(
                PARAM_BOOL,
                'Search messages with this unread status',
                VALUE_OPTIONAL
            ),
            'starred' => new \external_value(
                PARAM_BOOL,
                'Search messages with this starred status',
                VALUE_OPTIONAL
            ),
            'deleted' => new \external_value(
                PARAM_BOOL,
                'Search deleted messages.',
                VALUE_DEFAULT,
                false
            ),
            'content' => new \external_value(
                PARAM_RAW,
                'Search messages with this text in ',
                VALUE_DEFAULT,
                ''
            ),
            'sendername' => new \external_value(
                PARAM_RAW,
                'Text to search the name of the sender',
                VALUE_DEFAULT,
                ''
            ),
            'recipientname' => new \external_value(
                PARAM_RAW,
                'Text to search the names of the recipients',
                VALUE_DEFAULT,
                ''
            ),
            'withfilesonly' => new \external_value(
                PARAM_BOOL,
                'Search only messages with attachments',
                VALUE_DEFAULT,
                false
            ),
            'maxtime' => new \external_value(
                PARAM_INT,
                'Searh only messages older than this timestamp',
                VALUE_DEFAULT,
                0
            ),
            'startid' => new \external_value(
                PARAM_INT,
                'Start searching from the position of this message (excluded).',
                VALUE_DEFAULT,
                0
            ),
            'stopid' => new \external_value(
                PARAM_INT,
                'Stop serching at the position of this message (excluded).',
                VALUE_DEFAULT,
                0
            ),
            'reverse' => new \external_value(
                PARAM_BOOL,
                'Search messages from older to newer instead of from newer to older.',
                VALUE_DEFAULT,
                false
            ),
        ]);
    }

    private static function validate_query_parameter(array $query): message_search {
        $user = user::current();

        $search = new message_search($user);

        if ($query['courseid']) {
            $search->course = course::get($query['courseid']);
            if (!$user->can_use_mail($search->course)) {
                throw new exception('errorcoursenotfound', $search->course->id);
            }
        }

        if ($query['labelid']) {
            $search->label = label::get($query['labelid']);
            if ($search->label->userid != $user->id) {
                throw new exception('errorlabelnotfound', $search->label->id);
            }
        }

        if (isset($query['draft'])) {
            $search->draft = $query['draft'];
        }

        foreach ($query['roles'] as $rolename) {
            $role = array_search($rolename, message::role_names());
            if ($role === false) {
                throw new \invalid_parameter_exception('invalid role: ' . $rolename);
            }
            $search->roles[] = $role;
        }

        if (isset($query['unread'])) {
            $search->unread = $query['unread'];
        }

        if (isset($query['starred'])) {
            $search->starred = $query['starred'];
        }

        $search->deleted = $query['deleted'];
        $search->content = $query['content'];
        $search->sendername = $query['sendername'];
        $search->recipientname = $query['recipientname'];
        $search->withfilesonly = $query['withfilesonly'];
        $search->maxtime = $query['maxtime'];

        if ($query['startid']) {
            $search->startid = $query['startid'];
        }

        if ($query['stopid']) {
            $search->stopid = $query['stopid'];
        }

        $search->reverse = $query['reverse'];

        return $search;
    }

    public static function count_messages_parameters() {
        return new \external_function_parameters([
            'query' => self::message_query_parameters(),
        ]);
    }

    public static function count_messages() {
        $params = self::validate_call(self::count_messages_parameters(), func_get_args());

        $search = self::validate_query_parameter($params['query']);

        return $search->count();
    }

    public static function count_messages_returns() {
        return new \external_value(PARAM_INT, 'Number of messages');
    }

    public static function search_messages_parameters() {
        return new \external_function_parameters([
            'query' => self::message_query_parameters(),
            'offset' => new \external_value(
                PARAM_INT,
                'Skip this number of messages',
                VALUE_DEFAULT,
                0
            ),
            'limit' => new \external_value(
                PARAM_INT,
                'Maximum number of messages',
                VALUE_DEFAULT,
                0
            ),
        ]);
    }

    public static function search_messages() {
        $params = self::validate_call(self::search_messages_parameters(), func_get_args());

        $search = self::validate_query_parameter($params['query']);

        $messages = $search->get($params['offset'], $params['limit']);

        return self::search_messages_response($search->user, $messages);
    }

    public static function search_messages_response(user $user, array $messages) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_mail');

        $result = [];

        foreach ($messages as $message) {
            $course = $message->course;
            $context = $course->get_context();
            $sender = $message->sender();
            $recipients = [];
            foreach ($message->recipients() as $recipient) {
                $role = $message->role($recipient);
                if ($role == message::ROLE_BCC && $user->id != $recipient->id && $user->id != $sender->id) {
                    continue;
                }
                $recipients[] = [
                    'type' => message::role_names()[$message->role($recipient)],
                    'id' => $recipient->id,
                    'firstname' => $recipient->firstname,
                    'lastname' => $recipient->lastname,
                    'fullname' => $recipient->fullname(),
                    'pictureurl' => $recipient->picture_url(),
                    'profileurl' => $recipient->profile_url($course),
                    'sortorder' => $recipient->sortorder(),
                ];
            }
            $labels = [];
            foreach ($message->get_labels($user) as $label) {
                $labels[] = [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ];
            }
            $result[] = [
                'id' => $message->id,
                'subject' => $message->subject,
                'numattachments' => $message->attachments,
                'draft' => $message->draft,
                'time' => $message->time,
                'shorttime' => $renderer->formatted_time($message->time),
                'fulltime' => $renderer->formatted_time($message->time, true),
                'unread' => $message->unread($user),
                'starred' => $message->starred($user),
                'deleted' => $message->deleted($user) != message::NOT_DELETED,
                'course' => [
                    'id' => $course->id,
                    'shortname' => external_format_string($course->shortname, $context),
                    'fullname' => external_format_string($course->fullname, $context),
                    'visible' => $course->visible,
                    'groupmode' => $course->groupmode,
                ],
                'sender' => [
                    'id' => $sender->id,
                    'firstname' => $sender->firstname,
                    'lastname' => $sender->lastname,
                    'fullname' => $sender->fullname(),
                    'pictureurl' => $sender->picture_url(),
                    'profileurl' => $sender->profile_url($course),
                    'sortorder' => $sender->sortorder(),
                ],
                'recipients' => $recipients,
                'labels' => $labels,
            ];
        }

        return $result;
    }

    public static function search_messages_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the message'),
                'subject' => new \external_value(PARAM_RAW, 'Subject of the message'),
                'numattachments' => new \external_value(PARAM_INT, 'Number of attachments'),
                'draft' => new \external_value(PARAM_BOOL, 'Draft status'),
                'time' => new \external_value(PARAM_INT, 'Time of the message'),
                'shorttime' => new \external_value(PARAM_RAW, 'Formatted short time'),
                'fulltime' => new \external_value(PARAM_RAW, 'Formatted full time'),
                'unread' => new \external_value(PARAM_BOOL, 'Unread status'),
                'starred' => new \external_value(PARAM_BOOL, 'Starred status'),
                'deleted' => new \external_value(PARAM_BOOL, 'Deleted status'),
                'course' => new \external_single_structure([
                    'id' => new \external_value(PARAM_INT, 'Id of the course'),
                    'shortname' => new \external_value(PARAM_RAW, 'Short name of the course'),
                    'fullname' => new \external_value(PARAM_RAW, 'Full name of the course'),
                    'visible' => new \external_value(PARAM_BOOL, 'Course visibility'),
                    'groupmode' => new \external_value(PARAM_INT, 'Group mode: 0 (no), 1 (separate) or 2 (visible)'),
                ], '', VALUE_OPTIONAL),
                'sender' => new \external_single_structure([
                    'id' => new \external_value(PARAM_INT, 'Id of the user'),
                    'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                    'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                    'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                    'pictureurl' => new \external_value(PARAM_URL, 'User image URL', VALUE_OPTIONAL),
                    'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                    'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
                ]),
                'recipients' => new \external_multiple_structure(
                    new \external_single_structure([
                        'type' => new \external_value(PARAM_ALPHA, 'Role of the user: "to", "cc" or "bcc"'),
                        'id' => new \external_value(PARAM_INT, 'Id of the user'),
                        'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                        'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                        'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                        'pictureurl' => new \external_value(PARAM_URL, 'User image URL'),
                        'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                        'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
                    ])
                ),
                'labels' => new \external_multiple_structure(
                    new \external_single_structure([
                        'id' => new \external_value(PARAM_INT, 'Id of the label'),
                        'name' => new \external_value(PARAM_RAW, 'Name of the label'),
                        'color' => new \external_value(PARAM_ALPHA, 'Color of the label'),
                    ])
                ),
            ])
        );
    }

    public static function get_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
        ]);
    }

    public static function get_message() {
        $params = self::validate_call(self::get_message_parameters(), func_get_args());

        $user = user::current();

        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        return self::get_message_response($user, $message);
    }

    public static function get_message_response(user $user, message $message) {
        global $OUTPUT, $PAGE;
        $renderer = $PAGE->get_renderer('local_mail');

        $PAGE->initialise_theme_and_output();
        $OUTPUT->header(); // Hack alert: Forcing bootstrap_renderer to initiate moodle page.
        $PAGE->start_collecting_javascript_requirements();

        $course = $message->course;
        $context = $course->get_context();
        $sender = $message->sender();

        $result = [
            'id' => $message->id,
            'subject' => $message->subject,
            'content' => $renderer->formatted_message_content($message),
            'format' => FORMAT_HTML,
            'numattachments' => $message->attachments,
            'draft' => $message->draft,
            'time' => $message->time,
            'shorttime' => $renderer->formatted_time($message->time),
            'fulltime' => $renderer->formatted_time($message->time, true),
            'unread' => $message->unread($user),
            'starred' => $message->starred($user),
            'deleted' => (bool) $message->deleted($user),
            'course' => [
                'id' => $course->id,
                'shortname' => external_format_string($course->shortname, $context),
                'fullname' => external_format_string($course->fullname, $context),
                'visible' => $course->visible,
                'groupmode' => $course->groupmode,
            ],
            'sender' => [
                'id' => $sender->id,
                'firstname' => $sender->firstname,
                'lastname' => $sender->lastname,
                'fullname' => $sender->fullname(),
                'pictureurl' => $sender->picture_url(),
                'profileurl' => $sender->profile_url($course),
                'sortorder' => $sender->sortorder(),
            ],
            'recipients' => [],
            'attachments' => [],
            'references' => [],
            'labels' => [],
        ];

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_mail', 'message', $message->id, 'filename', false);
        foreach ($files as $file) {
            $result['attachments'][] = [
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename(),
                'filesize' => (int) $file->get_filesize(),
                'mimetype' => $file->get_mimetype(),
                'fileurl' => $renderer->file_url($file),
                'iconurl' => $renderer->file_icon_url($file),
            ];
        }

        $recipients = $message->recipients();

        $search = new user_search($user, $course);
        $search->include = array_column($recipients, 'id');
        $validrecipients = $search->get();

        foreach ($recipients as $recipient) {
            $role = $message->role($recipient);
            if ($role == message::ROLE_BCC && $user->id != $recipient->id && $user->id != $sender->id) {
                continue;
            }
            $result['recipients'][] = [
                'type' => message::role_names()[$role],
                'id' => $recipient->id,
                'firstname' => $recipient->firstname,
                'lastname' => $recipient->lastname,
                'fullname' => $recipient->fullname(),
                'pictureurl' => $recipient->picture_url(),
                'profileurl' => $recipient->profile_url($course),
                'sortorder' => $recipient->sortorder(),
                'isvalid' => isset($validrecipients[$recipient->id]),
            ];
        }

        foreach ($message->get_references() as $ref) {
            $attachments = [];
            $files = $fs->get_area_files($context->id, 'local_mail', 'message', $ref->id, 'filename', false);

            foreach ($files as $file) {
                $attachments[] = [
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
                    'filesize' => (int) $file->get_filesize(),
                    'mimetype' => $file->get_mimetype(),
                    'fileurl' => $renderer->file_url($file),
                    'iconurl' => $renderer->file_icon_url($file),
                ];
            }

            $refsender = $ref->sender();

            $result['references'][] = [
                'id' => $ref->id,
                'subject' => $ref->subject,
                'content' => $renderer->formatted_message_content($ref),
                'format' => FORMAT_HTML,
                'time' => $ref->time,
                'shorttime' => $renderer->formatted_time($ref->time),
                'fulltime' => $renderer->formatted_time($ref->time, true),
                'sender' => [
                    'id' => $refsender->id,
                    'firstname' => $refsender->firstname,
                    'lastname' => $refsender->lastname,
                    'fullname' => $refsender->fullname(),
                    'pictureurl' => $refsender->picture_url(),
                    'profileurl' => $refsender->profile_url($course),
                    'sortorder' => $refsender->sortorder(),
                ],
                'attachments' => $attachments,
            ];
        }

        foreach ($message->get_labels($user) as $label) {
            $result['labels'][] = [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ];
        }

        $result['javascript'] = $PAGE->requires->get_end_code();
        $PAGE->end_collecting_javascript_requirements();

        return $result;
    }

    public static function get_message_returns() {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Id of the message'),
            'subject' => new \external_value(PARAM_RAW, 'Subject of the message'),
            'content' => new \external_value(PARAM_RAW, 'Content of the message'),
            'format' => new \external_format_value('Format of the message content'),
            'numattachments' => new \external_value(PARAM_INT, 'Number of attachments'),
            'draft' => new \external_value(PARAM_BOOL, 'Draft status'),
            'time' => new \external_value(PARAM_INT, 'Time of the message'),
            'shorttime' => new \external_value(PARAM_RAW, 'Formatted short time'),
            'fulltime' => new \external_value(PARAM_RAW, 'Formatted full time'),
            'unread' => new \external_value(PARAM_BOOL, 'Unread status'),
            'starred' => new \external_value(PARAM_BOOL, 'Starred status'),
            'deleted' => new \external_value(PARAM_BOOL, 'Deleted status'),
            'course' => new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the course'),
                'shortname' => new \external_value(PARAM_RAW, 'Short name of the course'),
                'fullname' => new \external_value(PARAM_RAW, 'Full name of the course'),
                'visible' => new \external_value(PARAM_BOOL, 'Course visibility'),
                'groupmode' => new \external_value(PARAM_INT, 'Group mode: 0 (no), 1 (separate) or 2 (visible)'),
            ]),
            'sender' => new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the user'),
                'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                'pictureurl' => new \external_value(PARAM_URL, 'User image URL'),
                'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
            ]),
            'recipients' => new \external_multiple_structure(
                new \external_single_structure([
                    'type' => new \external_value(PARAM_ALPHA, 'Role of the user: "to", "cc" or "bcc"'),
                    'id' => new \external_value(PARAM_INT, 'Id of the user'),
                    'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                    'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                    'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                    'pictureurl' => new \external_value(PARAM_URL, 'User image URL'),
                    'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                    'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
                    'isvalid' => new \external_value(PARAM_BOOL, 'This user can receive messages.'),
                ])
            ),
            'attachments' => new \external_multiple_structure(
                new \external_single_structure([
                    'filepath' => new \external_value(PARAM_PATH, 'File directory'),
                    'filename' => new \external_value(PARAM_FILE, 'File name'),
                    'mimetype' => new \external_value(PARAM_RAW, 'Mime type'),
                    'filesize' => new \external_value(PARAM_INT, 'File size'),
                    'fileurl'  => new \external_value(PARAM_URL, 'Download URL'),
                    'iconurl'  => new \external_value(PARAM_URL, 'Icon URL'),
                ])
            ),
            'references' => new \external_multiple_structure(
                new \external_single_structure([
                    'id' => new \external_value(PARAM_INT, 'Id of the message'),
                    'subject' => new \external_value(PARAM_RAW, 'Subject of the message'),
                    'content' => new \external_value(PARAM_RAW, 'Content of the message'),
                    'format' => new \external_format_value('Format of the message content'),
                    'time' => new \external_value(PARAM_INT, 'Time of the message'),
                    'shorttime' => new \external_value(PARAM_RAW, 'Formatted short time'),
                    'fulltime' => new \external_value(PARAM_RAW, 'Formatted full time'),
                    'sender' => new \external_single_structure([
                        'id' => new \external_value(PARAM_INT, 'Id of the user'),
                        'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                        'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                        'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                        'pictureurl' => new \external_value(PARAM_URL, 'User image URL'),
                        'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                        'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
                    ]),
                    'attachments' => new \external_multiple_structure(
                        new \external_single_structure([
                            'filepath' => new \external_value(PARAM_PATH, 'File directory'),
                            'filename' => new \external_value(PARAM_FILE, 'File name'),
                            'mimetype' => new \external_value(PARAM_RAW, 'Mime type'),
                            'filesize' => new \external_value(PARAM_INT, 'File size'),
                            'fileurl'  => new \external_value(PARAM_URL, 'Download URL'),
                            'iconurl'  => new \external_value(PARAM_URL, 'Icon URL'),
                        ])
                    ),
                ])
            ),
            'labels' => new \external_multiple_structure(
                new \external_single_structure([
                    'id' => new \external_value(PARAM_INT, 'Id of the label'),
                    'name' => new \external_value(PARAM_RAW, 'Name of the label'),
                    'color' => new \external_value(PARAM_ALPHA, 'Color of the label'),
                ])
            ),
            'javascript' => new \external_value(PARAM_RAW, 'Required Javascript HTML elements'),
        ]);
    }

    public static function view_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
        ]);
    }

    public static function view_message() {
        $params = self::validate_call(self::view_message_parameters(), func_get_args());

        $user = user::current();

        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $message->set_unread($user, false);

        if ($message->draft) {
            event\draft_viewed::create_from_message($message)->trigger();
        } else {
            event\message_viewed::create_from_message($message)->trigger();
        }

        return null;
    }

    public static function view_message_returns() {
        return null;
    }

    public static function set_unread_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
            'unread' => new \external_value(PARAM_BOOL, 'New unread status'),
        ]);
    }

    public static function set_unread() {
        $params = self::validate_call(self::set_unread_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $message->set_unread($user, $params['unread']);

        return null;
    }

    public static function set_unread_returns() {
        return null;
    }

    public static function set_starred_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
            'starred' => new \external_value(PARAM_BOOL, 'New starred status'),
        ]);
    }

    public static function set_starred() {
        $params = self::validate_call(self::set_starred_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $message->set_starred($user, $params['starred']);

        return null;
    }

    public static function set_starred_returns() {
        return null;
    }

    public static function set_deleted_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
            'deleted' => new \external_value(
                PARAM_INT,
                'New deleted status: 0 (not deleted), 1 (deleted), 2 (deleted forever)'
            ),
        ]);
    }

    public static function set_deleted() {
        $params = self::validate_call(self::set_deleted_parameters(), func_get_args());

        if (!in_array($params['deleted'], [message::NOT_DELETED, message::DELETED, message::DELETED_FOREVER])) {
            throw new \invalid_parameter_exception('Invalid deleted status');
        }

        $user = user::current();
        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $message->set_deleted($user, $params['deleted']);

        if ($message->draft && $params['deleted'] == message::DELETED_FOREVER) {
            event\draft_deleted::create_from_message($message)->trigger();
        }

        return null;
    }

    public static function set_deleted_returns() {
        return null;
    }

    public static function empty_trash_parameters() {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'ID of the course', VALUE_DEFAULT, 0),
        ]);
    }

    public static function empty_trash() {
        $params = self::validate_call(self::empty_trash_parameters(), func_get_args());

        $user = user::current();

        $course = null;
        if ($params['courseid']) {
            $course = course::get($params['courseid']);
            if (!$user->can_use_mail($course)) {
                throw new exception('errorcoursenotfound', $course->id);
            }
        }

        $search = new message_search($user);
        $search->course = $course;
        $search->deleted = true;
        $batchsize = 100;

        do {
            $messages = $search->get(0, $batchsize);
            foreach ($messages as $message) {
                $message->set_deleted($user, message::DELETED_FOREVER);
                if ($message->draft) {
                    event\draft_deleted::create_from_message($message)->trigger();
                }
            }
        } while (count($messages) >= $batchsize);

        return null;
    }

    public static function empty_trash_returns() {
        return null;
    }

    public static function create_label_parameters() {
        $colors = implode(', ', label::COLORS);
        return new \external_function_parameters([
            'name' => new \external_value(PARAM_RAW, 'Name of the label'),
            'color' => new \external_value(PARAM_ALPHA, "Color of the label. Valid values: $colors", VALUE_DEFAULT, ''),
            'messageids' => new \external_multiple_structure(
                new \external_value(PARAM_INT),
                'IDs of the messages to which the label will be set',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }

    public static function create_label() {
        $params = self::validate_call(self::create_label_parameters(), func_get_args());

        $user = user::current();

        $normalizedname = label::nromalized_name($params['name']);
        if (strlen($normalizedname) == 0) {
            throw new exception('erroremptylabelname');
        }

        foreach (label::get_by_user($user) as $label) {
            if ($label->name == $normalizedname) {
                throw new exception('errorrepeatedlabelname');
            }
        }

        if ($params['color'] && !in_array($params['color'], label::COLORS)) {
            throw new \invalid_parameter_exception('invalid color: ' . $params['color']);
        }

        $messages = message::get_many($params['messageids']);
        foreach ($messages as $message) {
            if (!$user->can_view_message($message)) {
                throw new exception('errormessagenotfound', $message->id);
            }
        }

        $label = label::create($user, $normalizedname, $params['color']);

        foreach ($messages as $message) {
            $labels = $message->get_labels($user);
            $labels[] = $label;
            $message->set_labels($user, $labels);
        }

        return $label->id;
    }

    public static function create_label_returns() {
        return new \external_value(PARAM_INT, 'ID of the label');
    }

    public static function update_label_parameters() {
        $colors = implode(', ', label::COLORS);
        return new \external_function_parameters([
            'labelid' => new \external_value(PARAM_INT, 'ID of the label'),
            'name' => new \external_value(PARAM_RAW, 'Name of the label'),
            'color' => new \external_value(PARAM_ALPHA, "Color of the label: $colors", VALUE_DEFAULT, ''),
        ]);
    }

    public static function update_label() {
        $params = self::validate_call(self::update_label_parameters(), func_get_args());

        $user = user::current();

        $label = label::get($params['labelid']);
        if ($label->userid != $user->id) {
            throw new exception('errorlabelnotfound', $label->id);
        }

        $normalizedname = label::nromalized_name($params['name']);
        if (strlen($normalizedname) == 0) {
            throw new exception('erroremptylabelname');
        }

        foreach ($label::get_by_user($user) as $userlabel) {
            if ($userlabel->id != $params['labelid'] && $userlabel->name == $normalizedname) {
                throw new exception('errorrepeatedlabelname');
            }
        }

        if ($params['color'] && !in_array($params['color'], label::COLORS)) {
            throw new \invalid_parameter_exception('invalid color: ' . $params['color']);
        }

        $label->update($normalizedname, $params['color']);

        return null;
    }

    public static function update_label_returns() {
        return null;
    }

    public static function delete_label_parameters() {
        return new \external_function_parameters([
            'labelid' => new \external_value(PARAM_INT, 'ID of the label'),
        ]);
    }

    public static function delete_label() {
        $params = self::validate_call(self::delete_label_parameters(), func_get_args());

        $user = user::current();

        $label = label::get($params['labelid']);
        if ($label->userid != $user->id) {
            throw new exception('errorlabelnotfound', $label->id);
        }

        $label->delete();

        return null;
    }

    public static function delete_label_returns() {
        return null;
    }

    public static function set_labels_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'ID of the message'),
            'labelids' => new \external_multiple_structure(
                new \external_value(
                    PARAM_INT,
                    'ID of a label'
                ),
            ),
        ]);
    }

    public static function set_labels() {
        $params = self::validate_call(self::set_labels_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);

        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $labels = label::get_many($params['labelids']);
        foreach ($params['labelids'] as $id) {
            if (!isset($labels[$id]) || $labels[$id]->userid != $user->id) {
                throw new exception('errorlabelnotfound', $id);
            }
        }

        $message->set_labels($user, $labels);

        return null;
    }

    public static function set_labels_returns() {
        return null;
    }

    public static function get_roles_parameters() {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'ID of the course'),
        ]);
    }

    public static function get_roles() {
        $params = self::validate_call(self::get_roles_parameters(), func_get_args());

        $user = user::current();
        $course = course::get($params['courseid']);

        if (!$user->can_use_mail($course)) {
            throw new exception('errorcoursenotfound', $course->id);
        }

        $result = [];
        foreach ($course->get_viewable_roles($user) as $id => $name) {
            $result[] = ['id' => $id, 'name' => $name];
        }
        return $result;
    }

    public static function get_roles_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'ID of the role'),
                'name' => new \external_value(PARAM_RAW, 'Name of the role'),
            ])
        );
    }

    public static function get_groups_parameters() {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'ID of the course'),
        ]);
    }

    public static function get_groups() {
        $params = self::validate_call(self::get_groups_parameters(), func_get_args());

        $user = user::current();
        $course = course::get($params['courseid']);

        if (!$user->can_use_mail($course)) {
            throw new exception('errorcoursenotfound', $course->id);
        }

        $result = [];
        foreach ($course->get_viewable_groups($user) as $id => $name) {
            $result[] = ['id' => $id, 'name' => $name];
        }
        return $result;
    }

    public static function get_groups_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'ID of the group'),
                'name' => new \external_value(PARAM_RAW, 'Name of the group'),
            ])
        );
    }

    public static function search_users_parameters() {
        return new \external_function_parameters([
            'query' => new \external_single_structure([
                'courseid' => new \external_value(
                    PARAM_INT,
                    'Search messages in this course',
                    VALUE_REQUIRED,
                ),
                'roleid' => new \external_value(
                    PARAM_INT,
                    'Search users with this role',
                    VALUE_DEFAULT,
                    0
                ),
                'groupid' => new \external_value(
                    PARAM_INT,
                    'Search users in this group',
                    VALUE_DEFAULT,
                    0
                ),
                'fullname' => new \external_value(
                    PARAM_RAW,
                    'Search users with a full name that contains this text',
                    VALUE_DEFAULT,
                    ''
                ),
                'include' => new \external_multiple_structure(
                    new \external_value(PARAM_INT),
                    'Search users with one of these IDs.',
                    VALUE_DEFAULT,
                    []
                ),
            ]),
            'offset' => new \external_value(
                PARAM_INT,
                'Skip this number of messages',
                VALUE_DEFAULT,
                0
            ),
            'limit' => new \external_value(
                PARAM_INT,
                'Maximum number of messages',
                VALUE_DEFAULT,
                0
            ),
        ]);
    }

    public static function search_users() {
        $params = self::validate_call(self::search_users_parameters(), func_get_args());

        $user = user::current();
        $course = course::get($params['query']['courseid']);
        if (!$user->can_use_mail($course)) {
            throw new exception('errorcoursenotfound', $course->id);
        }

        $search = new user_search($user, $course);

        if ($params['query']['roleid']) {
            $search->roleid = $params['query']['roleid'];
        }

        if ($params['query']['groupid']) {
            $search->groupid = $params['query']['groupid'];
        }

        if (\core_text::strlen($params['query']['fullname'])) {
            $search->fullname = $params['query']['fullname'];
        }

        if ($params['query']['include']) {
            $search->include = $params['query']['include'];
        }

        if (!$user->can_view_group($search->course, $search->groupid)) {
            throw new exception('errorgroupnotfound');
        }

        $users = $search->get($params['offset'], $params['limit']);

        return self::search_users_response($course, $users);
    }

    public static function search_users_response(course $course, array $users) {
        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'fullname' => $user->fullname(),
                'pictureurl' => $user->picture_url(),
                'profileurl' => $user->profile_url($course),
                'sortorder' => $user->sortorder(),
            ];
        }

        return $result;
    }

    public static function search_users_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Id of the user'),
                'firstname' => new \external_value(PARAM_NOTAGS, 'First name of the user'),
                'lastname' => new \external_value(PARAM_NOTAGS, 'Last name of the user'),
                'fullname' => new \external_value(PARAM_NOTAGS, 'Full name of the user'),
                'pictureurl' => new \external_value(PARAM_URL, 'User image URL'),
                'profileurl' => new \external_value(PARAM_URL, 'User profile URL'),
                'sortorder' => new \external_value(PARAM_RAW, 'User sort order'),
            ])
        );
    }

    public static function get_message_form_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'Id of the message'),
        ]);
    }

    public static function get_message_form() {
        global $CFG, $OUTPUT, $PAGE;

        require_once("$CFG->libdir/form/editor.php");
        require_once("$CFG->libdir/form/filemanager.php");

        $params = self::validate_call(self::get_message_form_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);
        if (!$user->can_edit_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }
        $options = message_data::file_options();
        $data = message_data::draft($message);
        if ($data->format != FORMAT_HTML) {
            $data->content = format_text($data->content, $data->format, ['filter' => false, 'para' => false]);
            $data->format = FORMAT_HTML;
        }

        $originaltexteditors = $CFG->texteditors;
        $CFG->texteditors = implode(',', settings::text_editors());

        $PAGE->set_url(new \moodle_url('/local/mail/view.php', ['t' => 'drafts', 'm' => $message->id]));
        $PAGE->set_context(\context_system::instance());
        $options['autosave'] = false;
        $attributes = ['id' => 'local-mail-compose-editor-' . $message->id];
        $editor = new \MoodleQuickForm_editor('content', null, $attributes, $options);
        $editor->setValue(['text' => $data->content, 'format' => $data->format, 'itemid' => $data->draftitemid]);
        $attributes = ['id' => 'local-mail-compose-filemanager-' . $message->id];
        $filemanager = new \MoodleQuickForm_filemanager('attachments', null, $attributes, $options);
        $filemanager->setValue($data->draftitemid);

        $PAGE->initialise_theme_and_output();
        $OUTPUT->header(); // Hack alert: Forcing bootstrap_renderer to initiate moodle page.
        $PAGE->start_collecting_javascript_requirements();
        $editorhtml = $editor->toHtml();
        $filemanagerhtml = $filemanager->toHtml();
        $javascript = $PAGE->requires->get_end_code();
        $PAGE->end_collecting_javascript_requirements();

        $CFG->texteditors = $originaltexteditors;

        return [
            'draftitemid' => $data->draftitemid,
            'editorhtml' => $editorhtml,
            'filemanagerhtml' => $filemanagerhtml,
            'javascript' => $javascript,
        ];
    }

    public static function get_message_form_returns() {
        return new \external_single_structure([
            'draftitemid' => new \external_value(PARAM_INT, 'Id of the file draft item.'),
            'editorhtml' => new \external_value(PARAM_RAW, 'HTML fragment of the editor'),
            'filemanagerhtml' => new \external_value(PARAM_RAW, 'HTML fragment of the file manager'),
            'javascript' => new \external_value(PARAM_RAW, 'Required Javascript HTML elements'),
        ]);
    }

    public static function create_message_parameters() {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'ID of the course'),
        ]);
    }

    public static function create_message() {
        $params = self::validate_call(self::create_message_parameters(), func_get_args());

        $user = user::current();
        $course = course::get($params['courseid']);
        if (!$user->can_use_mail($course)) {
            throw new exception('errorcoursenotfound', $course->id);
        }

        $data = message_data::new($course, $user);
        $message = message::create($data);

        event\draft_created::create_from_message($message)->trigger();

        return $message->id;
    }

    public static function create_message_returns() {
        return new \external_value(PARAM_INT, 'Id of the created message');
    }

    public static function reply_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'Id of the message to reply.'),
            'all' => new \external_value(PARAM_BOOL, 'Reply to all users.'),
        ]);
    }

    public static function reply_message() {
        $params = self::validate_call(self::reply_message_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);
        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $data = message_data::reply($message, $user, $params['all']);
        $message = message::create($data);
        return $message->id;
    }

    public static function reply_message_returns() {
        return new \external_value(PARAM_INT, 'Id of the created message');
    }

    public static function forward_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'Id of the message to reply.'),
        ]);
    }

    public static function forward_message() {
        $params = self::validate_call(self::forward_message_parameters(), func_get_args());

        $user = user::current();
        $message = message::get($params['messageid']);
        if (!$user->can_view_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $data = message_data::forward($message, $user);
        $message = message::create($data);
        return $message->id;
    }

    public static function forward_message_returns() {
        return new \external_value(PARAM_INT, 'Id of the created message');
    }

    public static function update_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'Id of the message'),
            'data' => new \external_single_structure([
                'courseid' => new \external_value(PARAM_INT, 'Id of the course'),
                'to' => new \external_multiple_structure(new \external_value(PARAM_INT), 'Ids of TO recipients.'),
                'cc' => new \external_multiple_structure(new \external_value(PARAM_INT), 'Ids of CC recipients.'),
                'bcc' => new \external_multiple_structure(new \external_value(PARAM_INT), 'Ids of BCC recipients.'),
                'subject' => new \external_value(PARAM_RAW, 'Subject of the message'),
                'content' => new \external_value(PARAM_RAW, 'Content of the message'),
                'format' => new \external_format_value('Format of the message'),
                'draftitemid' => new \external_value(PARAM_INT, 'Id of the file draft item.'),
            ]),
        ]);
    }

    public static function update_message() {
        $params = self::validate_call(self::update_message_parameters(), func_get_args());

        $user = user::current();

        $message = message::get($params['messageid']);
        if (!$user->can_edit_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $course = course::get($params['data']['courseid']);
        if (!$user->can_use_mail($course)) {
            throw new exception('errorcoursenotfound', $course->id);
        }

        $data = message_data::new($course, $user);
        $data->subject = $params['data']['subject'];
        $data->content = $params['data']['content'];
        $data->format = $params['data']['format'];
        $data->draftitemid = $params['data']['draftitemid'];
        foreach (['to', 'cc', 'bcc'] as $rolename) {
            if ($params['data'][$rolename]) {
                $data->$rolename = user::get_many($params['data'][$rolename]);
            }
        }
        $message->update($data);

        event\draft_updated::create_from_message($message)->trigger();

        return null;
    }

    public static function update_message_returns() {
        return null;
    }

    public static function send_message_parameters() {
        return new \external_function_parameters([
            'messageid' => new \external_value(PARAM_INT, 'Id of the message', VALUE_DEFAULT, 0),
        ]);
    }

    public static function send_message() {
        global $PAGE;

        $params = self::validate_call(self::send_message_parameters(), func_get_args());

        $user = user::current();

        $message = message::get($params['messageid']);
        if (!$user->can_edit_message($message)) {
            throw new exception('errormessagenotfound', $message->id);
        }

        $recipients = $message->recipients();

        if (!$recipients) {
            throw new exception('erroremptyrecipients');
        }

        $search = new user_search($user, $message->course);
        $search->include = array_column($recipients, 'id');
        $validrecipients = $search->get();

        foreach ($recipients as $recipient) {
            if (!isset($validrecipients[$recipient->id])) {
                throw new exception('errorinvalidrecipients');
            }
        }

        $maxrecipients = (int) get_config('local_mail', 'maxrecipients') ?: 100;
        if (count($recipients) > $maxrecipients) {
            throw new exception('errortoomanyrecipients', $maxrecipients);
        }

        if (!\core_text::strlen(trim($message->subject))) {
            throw new exception('erroremptysubject');
        }

        $message->send(time());

        event\message_sent::create_from_message($message)->trigger();

        $renderer = $PAGE->get_renderer('local_mail');
        foreach ($message->recipients() as $recipient) {
            $notificationid = message_send($renderer->notification($message, $recipient));
            if ($notificationid && get_user_preferences('local_mail_markasread', false, $recipient->id)) {
                $message->set_unread($recipient, false);
            }
        }

        return null;
    }

    public static function send_message_returns() {
        return null;
    }

    /**
     * Validates user and parameters.
     *
     * @param \external_function_parameters $description Description of function parameters.
     * @param array $args Argument list of function obtanied by calling func_get_args().
     * @return mixed[] Validated parameters.
     */
    private static function validate_call(\external_function_parameters $description, array $args): array {
        self::validate_context(\context_system::instance());
        if (!settings::is_installed()) {
            throw new exception('errorpluginnotinstalled');
        }
        $keys = array_slice(array_keys($description->keys), 0, count($args));
        $values = array_slice($args, 0, count($description->keys));
        $params = array_combine($keys, $values);
        return self::validate_parameters($description, $params);
    }
}
