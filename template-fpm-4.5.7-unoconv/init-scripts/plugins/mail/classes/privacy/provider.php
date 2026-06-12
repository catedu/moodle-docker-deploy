<?php
/*
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_mail\label;
use local_mail\message;
use local_mail\user;
use local_mail\output\strings;

/**
 * Implementation of the privacy subsystem plugin provider for local mail.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\user_preference_provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_mail_labels', [
            'userid' => 'privacy:metadata:local_mail_labels:userid',
            'name' => 'privacy:metadata:local_mail_labels:name',
            'color' => 'privacy:metadata:local_mail_labels:color',
        ], 'privacy:metadata:local_mail_labels');

        $collection->add_database_table('local_mail_messages', [
            'courseid' => 'privacy:metadata:local_mail_messages:courseid',
            'subject' => 'privacy:metadata:local_mail_messages:subject',
            'content' => 'privacy:metadata:local_mail_messages:content',
            'format' => 'privacy:metadata:local_mail_messages:format',
            'attachments' => 'privacy:metadata:local_mail_messages:attachments',
            'draft' => 'privacy:metadata:local_mail_messages:draft',
            'time' => 'privacy:metadata:local_mail_messages:time',
            'normalizedsubject' => 'privacy:metadata:local_mail_messages:normalizedsubject',
            'normalizedcontent' => 'privacy:metadata:local_mail_messages:normalizedcontent',
        ], 'privacy:metadata:local_mail_messages');

        $collection->add_database_table('local_mail_message_labels', [
            'messageid' => 'privacy:metadata:local_mail_message_labels:messageid',
            'courseid' => 'privacy:metadata:local_mail_message_labels:courseid',
            'draft' => 'privacy:metadata:local_mail_message_labels:draft',
            'time' => 'privacy:metadata:local_mail_message_labels:time',
            'labelid' => 'privacy:metadata:local_mail_message_labes:labelid',
            'role' => 'privacy:metadata:local_mail_message_labels:role',
            'unread' => 'privacy:metadata:local_mail_message_labels:unread',
            'starred' => 'privacy:metadata:local_mail_message_labels:starred',
            'deleted' => 'privacy:metadata:local_mail_message_labels:deleted',
        ], 'privacy:metadata:local_mail_message_labels');

        $collection->add_database_table('local_mail_message_refs', [
            'messageid' => 'privacy:metadata:local_mail_message_refs:messageid',
            'reference' => 'privacy:metadata:local_mail_message_refs:reference',
        ], 'privacy:metadata:local_mail_message_refs');

        $collection->add_database_table('local_mail_message_users', [
            'messageid' => 'privacy:metadata:local_mail_message_users:messageid',
            'courseid' => 'privacy:metadata:local_mail_message_users:courseid',
            'draft' => 'privacy:metadata:local_mail_message_users:draft',
            'time' => 'privacy:metadata:local_mail_message_users:time',
            'userid' => 'privacy:metadata:local_mail_message_user:userid',
            'role' => 'privacy:metadata:local_mail_message_users:role',
            'unread' => 'privacy:metadata:local_mail_message_users:unread',
            'starred' => 'privacy:metadata:local_mail_message_users:starred',
            'deleted' => 'privacy:metadata:local_mail_message_users:deleted',
        ], 'privacy:metadata:local_mail_message_users');

        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:core_files');

        $collection->add_user_preference('local_mail_mailsperpage', 'privacy:metadata:preference:local_mail_mailsperpage');
        $collection->add_user_preference('local_mail_markasread', 'privacy:metadata:preference:local_mail_markasread');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();

        if ($DB->record_exists('local_mail_labels', ['userid' => $userid])) {
            $contextlist->add_user_context($userid);
        }

        $sql = 'SELECT DISTINCT ctx.id'
            . ' FROM {local_mail_message_users} mu'
            . ' JOIN {context} ctx ON ctx.instanceid = mu.courseid'
            . ' WHERE ctx.contextlevel = :contextlevel'
            . ' AND mu.userid = :userid'
            . ' AND (mu.draft = 0 OR mu.role = :rolefrom1)'
            . ' AND (mu.deleted IN (:notdeleted, :deleted) OR mu.role = :rolefrom2 AND mu.deleted = :deletedforever)';
        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
            'rolefrom1' => message::ROLE_FROM,
            'rolefrom2' => message::ROLE_FROM,
            'notdeleted' => message::NOT_DELETED,
            'deleted' => message::DELETED,
            'deletedforever' => message::DELETED_FOREVER,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $sql = 'SELECT DISTINCT mu.userid'
            . ' FROM {local_mail_message_users} mu'
            . ' WHERE mu.courseid = :courseid'
            . ' AND (mu.draft = 0 OR mu.role = :rolefrom1)'
            . ' AND (mu.deleted IN (:notdeleted, :deleted) OR mu.role = :rolefrom2 AND mu.deleted = :deletedforever)';
        $params = [
            'courseid' => $context->instanceid,
            'rolefrom1' => message::ROLE_FROM,
            'rolefrom2' => message::ROLE_FROM,
            'notdeleted' => message::NOT_DELETED,
            'deleted' => message::DELETED,
            'deletedforever' => message::DELETED_FOREVER,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = new user($contextlist->get_user());

        // Labels.
        $context = \context_user::instance($user->id);
        foreach (label::get_by_user($user) as $label) {
            $subcontext = [strings::get('pluginname'), strings::get('labels'), $label->id];
            $data = [
                'name' => $label->name,
                'color' => get_string('color' . $label->color, 'local_mail'),
            ];
            writer::with_context($context)->export_data($subcontext, (object) $data);
        }

        // Messages.
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE || $context->instanceid == SITEID) {
                continue;
            }
            $writer = writer::with_context($context);
            foreach (self::iterate_messages($user, $context->instanceid) as $message) {
                $subcontext = [strings::get('pluginname'), strings::get('messages'), $message->id];
                $content = $writer->rewrite_pluginfile_urls($subcontext, 'local_mail', 'message', $message->id, $message->content);
                $content = format_text($content, $message->format, ['context' => $context, 'filter' => true, 'para' => false]);
                $sender = $message->sender();
                $data = [
                    'subject' => $message->subject,
                    'content' => $content,
                    'draft' => transform::yesno($message->draft),
                    'time' => transform::datetime($message->time),
                    'from' => $sender->fullname(),
                ];
                foreach ($message->recipients() as $recipient) {
                    $role = $message->role($recipient);
                    $rolename = message::role_names()[$role];
                    if ($role == message::ROLE_BCC && $user->id != $recipient->id && $user->id != $sender->id) {
                        continue;
                    }
                    $data[$rolename][] = $recipient->fullname();
                }
                foreach ($message->get_references() as $reference) {
                    $data['references'][] = $reference->id;
                }
                $data['unread'] = transform::yesno($message->unread($user));
                $data['starred'] = transform::yesno($message->starred($user));
                $data['deleted'] = transform::yesno($message->deleted($user));
                foreach ($message->get_labels($user) as $label) {
                    $data['labels'][] = $label->name;
                }
                $writer->export_data($subcontext, (object) $data);
                $writer->export_area_files($subcontext, 'local_mail', 'message', $message->id);
            }
        }
    }

    public static function export_user_preferences(int $userid): void {
        $perpage = get_user_preferences('local_mail_mailsperpage', null, $userid);
        if ($perpage !== null) {
            writer::export_user_preference(
                'local_mail',
                'local_mail_mailsperpage',
                $perpage,
                strings::get('privacy:metadata:preference:local_mail_mailsperpage'),
            );
        }

        $markasread = get_user_preferences('local_mail_markasread', null, $userid);
        if ($markasread !== null) {
            writer::export_user_preference(
                'local_mail',
                'local_mail_markasread',
                transform::yesno($markasread),
                strings::get('privacy:metadata:preference:local_mail_markasread'),
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        if ($context->contextlevel == CONTEXT_COURSE) {
            message::delete_course_data($context->get_course_context());
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $user = new user($contextlist->get_user());
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER) {
                foreach (label::get_by_user($user) as $label) {
                    $label->delete();
                }
            } else if ($context->contextlevel == CONTEXT_COURSE) {
                foreach (self::iterate_messages($user, $context->instanceid) as $message) {
                    if ($message->role($user) == message::ROLE_FROM) {
                        $message->set_deleted($user, $message::DELETED_CONTENT);
                    } else {
                        $message->set_deleted($user, $message::DELETED_FOREVER);
                    }
                }
            }
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        foreach ($userlist->get_users() as $user) {
            $contextid = $userlist->get_context()->id;
            self::delete_data_for_user(new approved_contextlist($user, 'local_mail', [$contextid]));
        }
    }

    private static function iterate_messages(user $user, int $courseid): \Traversable {
        global $DB;

        $sql = 'SELECT messageid'
        . ' FROM {local_mail_message_users}'
        . ' WHERE userid = :userid'
        . ' AND courseid = :courseid'
        . ' AND (draft = 0 OR role = :rolefrom1)'
        . ' AND (deleted IN (:notdeleted, :deleted) OR role = :rolefrom2 AND deleted = :deletedforever)';
        $params = [
            'userid' => $user->id,
            'courseid' => $courseid,
            'rolefrom1' => message::ROLE_FROM,
            'rolefrom2' => message::ROLE_FROM,
            'notdeleted' => message::NOT_DELETED,
            'deleted' => message::DELETED,
            'deletedforever' => message::DELETED_FOREVER,
        ];

        $rs = $DB->get_recordset_sql($sql, $params);

        try {
            while ($rs->valid()) {
                $ids = [];
                while ($rs->valid() && count($ids) < 100) {
                    $ids[] = $rs->key();
                    $rs->next();
                }
                yield from message::get_many($ids);
            }
        } finally {
            $rs->close();
        }
    }
}
