<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/repository/lib.php");

/**
 * Data for creating and updating messages.
 */
class message_data {
    /** @var user Sender. Ignored for updates. */
    public user $sender;

    /** @var ?message Reference. Ignored for updates. */
    public ?message $reference = null;

    /** @var course Course. */
    public course $course;

    /** @var user[] "To" recipients. */
    public array $to = [];

    /** @var user[] "CC" recipients. */
    public array $cc = [];

    /** @var user[] "BCC" recipients. */
    public array $bcc = [];

    /** @var string Subject. */
    public string $subject = '';

    /** @var string Content. */
    public string $content = '';

    /** @var int Format. */
    public int $format;

    /** @var int Draft item ID. */
    public int $draftitemid;

    /** @var int Time. */
    public int $time;

    /**
     * Private constructor.
     */
    private function __construct() {
    }

    /**
     * Constructs data fot an existing draft.
     *
     * @param message $message Message.
     * @return self Initial data for the message draft.
     */
    public static function draft(message $message): self {
        assert($message->draft);

        $data = new self();
        $data->sender = $message->sender();
        $data->course = $message->course;
        foreach ($message->recipients() as $user) {
            if ($message->role($user) == message::ROLE_TO) {
                $data->to[] = $user;
            } else if ($message->role($user) == message::ROLE_CC) {
                $data->cc[] = $user;
            } else if ($message->role($user) == message::ROLE_BCC) {
                $data->bcc[] = $user;
            }
        }
        $data->subject = $message->subject;
        $data->draftitemid = 0;
        $data->content = file_prepare_draft_area(
            $data->draftitemid,
            $message->course->get_context()->id,
            'local_mail',
            'message',
            $message->id,
            self::file_options(),
            $message->content
        );
        $data->format = (int) $message->format;
        $data->time = (int) $message->time;

        return $data;
    }

    /**
     * File options for message attachments.
     *
     * @return mixed[] Array of options.
     */
    public static function file_options(): array {
        global $CFG;

        $context = \context_system::instance();

        $configmaxbytes = get_config('local_mail', 'maxbytes') ?: $CFG->maxbytes;
        $configmaxfiles = get_config('local_mail', 'maxfiles');
        $maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes, 0, $configmaxbytes);
        $maxfiles = is_numeric($configmaxfiles) ? (int) $configmaxfiles : 20;
        return [
            'accepted_types' => '*',
            'maxbytes' => $maxbytes,
            'maxfiles' => $maxfiles,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => false,
            'autosave' => false,
        ];
    }

    /**
     * Constructs data for a forwarded message.
     *
     * @param message $message Message.
     * @param user $sender Sender.
     * @return self Initial data of the forwarded message.
     */
    public static function forward(message $message, user $sender): self {
        assert(!$message->draft);
        assert($sender->id == $message->sender()->id || $message->has_recipient($sender));

        $data = new self();
        $data->sender = $sender;
        $data->course = $message->course;
        $data->time = time();

        // Subject.
        $data->subject = $message->subject;
        $prefix = 'FW:';
        if (\core_text::strpos($data->subject, $prefix) !== 0) {
            $data->subject = $prefix . ' ' . $data->subject;
        }

        // Content.
        $data->draftitemid = 0;
        $originalcontent = file_prepare_draft_area(
            $data->draftitemid,
            $message->course->get_context()->id,
            'local_mail',
            'message',
            $message->id,
            self::file_options(),
            $message->content
        );
        $data->content = '<p><br></p>'
            . '<p>'
            . '--------- ' . output\strings::get('forwardedmessage') . ' ---------<br>'
            . output\strings::get('from') . ': '
            . $message->sender()->fullname() . '<br>'
            . output\strings::get('date') . ': '
            . userdate($message->time, get_string('strftimedatetime', 'langconfig')) . '<br>'
            . output\strings::get('subject') . ': '
            . s($message->subject)
            . '</p>'
            . format_text($originalcontent, $message->format, ['filter' => false, 'para' => false]);
        $data->format = FORMAT_HTML;

        return $data;
    }

    /**
     * Constructs data for a new message.
     *
     * @param course $course Course.
     * @param user $sender Sender.
     * @return self Initial data for the new message.
     */
    public static function new(course $course, user $sender): self {
        $data = new self();
        $data->sender = $sender;
        $data->course = $course;
        $data->format = (int) FORMAT_HTML;
        $data->draftitemid = file_get_unused_draft_itemid();
        $data->time = time();

        return $data;
    }

    /**
     * Constructs data for a message reply.
     *
     * @param message $message Message.
     * @param user $sender Sender.
     * @param bool $all Reply to all.
     * @return self Initial data for the message reply.
     */
    public static function reply(message $message, user $sender, bool $all): self {
        assert(!$message->draft);
        assert($sender->id == $message->sender()->id || $message->has_recipient($sender));

        $data = self::new($message->course, $sender);
        $data->reference = $message;

        // Subject.
        $data->subject = $message->subject;
        $prefix = 'RE:';
        if (\core_text::strpos($data->subject, $prefix) !== 0) {
            $data->subject = $prefix . ' ' . $data->subject;
        }

        // Recipients.
        if ($message->role($sender) == message::ROLE_FROM) {
            // Reply to self.
            $data->to = $message->recipients(message::ROLE_TO);
            if ($all) {
                $data->cc = $message->recipients(message::ROLE_CC);
            }
        } else {
            // Reply to antoher user.
            $data->to = [$message->sender()];
            if ($all) {
                foreach ($message->recipients(message::ROLE_TO, message::ROLE_CC) as $user) {
                    if ($user->id != $sender->id) {
                        $data->cc[] = $user;
                    }
                }
            }
        }

        return $data;
    }
}
