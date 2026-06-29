<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/clilib.php');

const EMOJIS = ['😀', '😛', '😱', '👍'];
const CONSONANTS = ['b', 'c', 'ç', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'x', 'y', 'z'];
const VOWELS = ['a', 'e', 'i', 'o', 'u'];
const MAX_WORDS = 10000;
const MAX_SENTENCES = 10000;

const EMOJI_FREQ = 0.05;
const COMMA_FREQ = 0.1;
const QUESTION_FREQ = 0.2;
const DASH_FREQ = 0.1;
const SYLLABES_PER_WORD_EX = 2;
const SYLLABES_PER_WORD_SD = 0.5;
const WORD_PER_SENTENCE_EX = 8;
const WORD_PER_SENTENCE_SD = 3;
const SENTENCES_PER_PARAGRAPH_EX = 5;
const SENTENCES_PER_PARAGRAPH_SD = 3;
const PARAGRAPHS_PER_MESSAGE_EX = 3;
const PARAGRAPHS_PER_MESSAGE_SD = 1;

const MESSAGES_PER_USER_PER_COURSE = 25;
const LABELS_PER_USER_EX = 3;
const LABELS_PER_USER_SD = 2;
const REPLY_FREQ = 0.7;
const FORWARD_FREQ = 0.1;
const DRAFT_FREQ = 0.1;
const TO_RECIPIENTS_EX = 1;
const TO_RECIPIENTS_SD = 2;
const CC_RECIPIENTS_EX = 0;
const CC_RECIPIENTS_SD = 2;
const BCC_RECIPIENTS_EX = -10;
const BCC_RECIPIENTS_SD = 10;
const ATTACHMENTS_EX = -1;
const ATTACHMENTS_SD = 1;
const REPLY_ALL_FREQ = 0.5;
const UNREAD_FREQ_EXP = 4;
const STARRED_FREQ = 0.2;
const DELETED_FREQ = 0.1;
const DELETED_CONTENT_FREQ = 0.05;
const MESSAGE_LABEL_EX = 0;
const MESSAGE_LABEL_SD = 1;

set_debugging(DEBUG_DEVELOPER, true);

function main() {
    global $CFG, $DB;

    raise_memory_limit(MEMORY_HUGE);

    // Run script as an admin user, to be able to use file draft areas.
    if ($CFG->branch >= 402) {
        \core\cron::setup_user();
    } else {
        cron_setup_user();
    }

    $countperuser = MESSAGES_PER_USER_PER_COURSE;
    $countperuser = (int) cli_input("Messages per user per course? [$countperuser]", $countperuser);
    if ($countperuser <= 0) {
        cli_error('Invalid number of messages.');
    }
    cli_writeln('');

    $admin = null;
    $adminname = trim(cli_input("Name of a user that will receive all mail as BCC [none]", ''));
    if ($adminname) {
        $admin = \core_user::get_user_by_username($adminname);
        if (!$admin) {
            cli_error('User not found.');
        }
        $admin = new user($admin);
    }
    cli_writeln('');

    $confirm = cli_input('ALL EXISTING MAIL DATA WILL BE DELETED! Type "OK" to continue.');
    if ($confirm != 'OK') {
        cli_error('Canceled.');
    }
    cli_writeln('');

    $starttime = time();

    $fs = get_file_storage();
    $courses = [];
    foreach (get_courses('all', 'c.sortorder') as $record) {
        if ($record->id != SITEID) {
            $courses[$record->id] = new course($record);
        }
    }

    delete_messages($courses);
    generate_user_labels();
    foreach ($courses as $course) {
        generate_course_messages($fs, $course, $admin, $countperuser);
    }

    $seconds = (int) (time() - $starttime);
    cli_writeln("\n\nFinished in $seconds seconds.");
}

function delete_messages(array $courses) {
    global $DB;

    foreach ($courses as $course) {
        print_progress("Deleting course mail", count($courses));

        message::delete_course_data($course->get_context());
    }
}

function add_random_attachments(\file_storage $fs, message_data $data) {
    global $USER;

    $context = \context_user::instance($USER->id);

    $filenames = [];

    $count = random_count(0, ATTACHMENTS_EX, ATTACHMENTS_SD);

    for ($i = 0; $i < $count; $i++) {
        $filename = '';
        while (!$filename || in_array($filename, $filenames)) {
            $filename = random_word() . '.html';
        }
        $filenames[] = $filename;
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $data->draftitemid,
            'filepath' => '/',
            'filename' => $filename,
            'timecreated' => (int) $data->time,
            'timemodified' => (int) $data->time,
            'userid' => $data->sender->id,
            'mimetype' => 'text/html',
        ];
        $fs->create_file_from_string($filerecord, random_content());
    }
}

function add_random_recipients(message_data $data, array $users): void {
    $counts = new \stdClass();
    $maxcount = count($users) - 1;
    $counts->to = min($maxcount, random_count(1, TO_RECIPIENTS_EX, TO_RECIPIENTS_SD));
    $maxcount -= $counts->to;
    $counts->cc = min($maxcount, random_count(0, CC_RECIPIENTS_EX, CC_RECIPIENTS_SD));
    $maxcount -= $counts->cc;
    $counts->bcc = min($maxcount, random_count(0, BCC_RECIPIENTS_EX, BCC_RECIPIENTS_SD));

    $isparticipant = [$data->sender->id => true];
    foreach ($counts as $rolename => $count) {
        foreach ($data->$rolename as $recipient) {
            $isparticipant[$recipient->id] = true;
        }
    }

    foreach ($counts as $rolename => $count) {
        while ($count > 0) {
            $user = random_item($users);
            if (empty($isparticipant[$user->id])) {
                $data->{$rolename}[] = $user;
                $isparticipant[$user->id] = true;
                $count--;
            }
        }
    }
}

function generate_course_messages(\file_storage $fs, course $course, ?user $admin, int $countperuser): void {
    global $DB;

    $users = user::get_many(array_keys(get_enrolled_users($course->get_context())));
    if (count($users) < 2) {
        return;
    }

    $count = $countperuser * count($users);
    $endtime = time();
    $starttime = $endtime - 365 * 86400;
    $sentmessages = [];

    for ($i = 0; $i < $count; $i++) {
        print_progress("Generating messages for course " . $course->shortname, $count);
        $transaction = $DB->start_delegated_transaction();
        $time = (int) (($endtime - $starttime) * $i / $count + $starttime);
        if ($i > 0 && random_bool(REPLY_FREQ)) {
            $data = generate_random_reply($fs, random_item($sentmessages), $time);
        } else if ($i > 0 && random_bool(FORWARD_FREQ / (1 - REPLY_FREQ))) {
            $data = generate_random_forward($fs, random_item($sentmessages), $users, $time);
        } else {
            $data = generate_random_message($fs, $course, $users, $time);
        }
        if ($admin && $data->sender->id != $admin->id) {
            $data->bcc[] = $admin;
        }
        $message = message::create($data);
        if ($i == 0 || !random_bool(DRAFT_FREQ)) {
            $message->send($time);
            $sentmessages[] = $message;
            // Only reply and forward recent messages.
            $countperweek = (int) ($count / 52);
            if (count($sentmessages) > $countperweek * 2) {
                $sentmessages = array_slice($sentmessages, $countperweek);
            }
        }
        set_random_unread($message, $starttime, $endtime);
        set_random_starred($message);
        set_random_labels($message);
        set_random_deleted($message);
        $transaction->allow_commit();
    }
}

function generate_random_forward(\file_storage $fs, message $message, array $users, int $time): message_data {
    $sender = random_item($message->recipients(message::ROLE_TO, message::ROLE_CC));
    $data = message_data::forward($message, $sender);
    $data->time = $time;

    add_random_recipients($data, $users);

    return $data;
}

function generate_random_message(\file_storage $fs, course $course, array $users, int $time): message_data {
    $sender = random_item($users);
    $data = message_data::new($course, $sender);
    $data->subject = random_sentence();
    $data->content = random_content();
    $data->time = $time;

    add_random_attachments($fs, $data);
    add_random_recipients($data, $users);

    return $data;
}

function generate_random_reply(\file_storage $fs, message $message, int $time): message_data {
    $sender = random_item($message->recipients(message::ROLE_TO, message::ROLE_CC));
    $all = random_bool(REPLY_ALL_FREQ);
    $data = message_data::reply($message, $sender, $all);
    $data->content = random_content();
    $data->time = $time;

    add_random_attachments($fs, $data);

    return $data;
}

function generate_user_labels() {
    global $CFG, $DB;

    $records = $DB->get_records_select('user', 'deleted = 0 AND id <> ?', [$CFG->siteguest], '', 'id');
    $users = user::get_many(array_keys($records));

    foreach ($users as $user) {
        print_progress('Generating user labels', count($users));

        foreach (label::get_by_user($user) as $label) {
            $label->delete();
        }
        $n = random_count(0, LABELS_PER_USER_EX, LABELS_PER_USER_SD);
        for ($i = 0; $i < $n; $i++) {
            $name = random_word(true);
            $color = random_item(label::COLORS);
            label::create($user, $name, $color);
        }
    }
}

function print_progress(string $message = '', int $total = 0) {
    static $prevmessage = '';
    static $value = 0;
    static $printtime = 0;

    if ($message != $prevmessage) {
        if (strlen($prevmessage)) {
            cli_writeln('');
        }
        $prevmessage = $message;
        $value = 0;
        $printtime = 0;
    }

    $value++;

    if (strlen($message) && ($value == $total || time() - $printtime > 0.5)) {
        $message = "\r$message... ";
        if ($total > 0) {
            $message .= "$value/$total ";
        }
        cli_write($message);
        $printtime = time();
    }
}

function random_bool(float $truefreq): bool {
    return rand() / getrandmax() < $truefreq;
}

function random_content(): string {
    $s = '';
    $n = random_count(1, PARAGRAPHS_PER_MESSAGE_EX, PARAGRAPHS_PER_MESSAGE_SD);
    for ($i = 0; $i < $n; $i++) {
        $s .= "\n" . random_paragraph();
    }
    return $s;
}

function random_count(int $min, float $ex, float $sd): int {
    $x = rand() / getrandmax();
    $y = rand() / getrandmax();
    $r = sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $sd + $ex;
    return max($min, (int) round($r));
}

function random_item(array $items) {
    return $items[array_rand($items)];
}

function random_paragraph(): string {
    $s = '<p>' . random_sentence(true);
    $n = random_count(1, SENTENCES_PER_PARAGRAPH_EX, SENTENCES_PER_PARAGRAPH_SD) - 1;
    for ($i = 0; $i < $n; $i++) {
        $s .= ' ' . random_sentence(true);
    }
    $s .= '</p>';
    return $s;
}

function random_sentence($period = false): string {
    if (random_bool(EMOJI_FREQ)) {
        return random_item(EMOJIS);
    }

    static $sentences = [];
    if (count($sentences) == MAX_SENTENCES) {
        $s = random_item($sentences);
    } else {
        $s = random_word(true);
        $n = random_count(1, WORD_PER_SENTENCE_EX, WORD_PER_SENTENCE_SD) - 1;

        for ($i = 0; $i < $n; $i++) {
            if (random_bool(COMMA_FREQ)) {
                $s .= ',';
            }
            $s .= ' ' . random_word();
        }

        $sentences[] = $s;
    }

    if ($period) {
        if (random_bool(QUESTION_FREQ)) {
            $s .= '?';
        } else {
            $s .= '.';
        }
    }

    return $s;
}

function random_word($capitalize = false): string {
    static $words = [];

    if (count($words) == MAX_WORDS) {
        $s = random_item($words);
    } else {
        $s = '';
        $n = random_count(1, SYLLABES_PER_WORD_EX, SYLLABES_PER_WORD_SD);

        for ($i = 0; $i < $n; $i++) {
            $c = random_item(CONSONANTS);
            $s .= $c . random_item(VOWELS);
            if ($i < $n - 1 && random_bool(DASH_FREQ)) {
                $s .= '-';
            }
        }

        $words[] = $s;
    }

    if ($capitalize) {
        $s = mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }

    return $s;
}

function set_random_deleted(message $message): void {
    if (!$message->draft) {
        if (random_bool(DELETED_FREQ)) {
            $message->set_deleted($message->sender(), message::DELETED);
        }
        foreach ($message->recipients() as $user) {
            if (random_bool(DELETED_FREQ)) {
                $message->set_deleted($user, message::DELETED);
            }
        }
        if (random_bool(DELETED_CONTENT_FREQ)) {
            $message->set_deleted($message->sender(), message::DELETED_CONTENT);
        }
    }
}

function set_random_labels(message $message): void {
    $users = array_merge([$message->sender()], $message->recipients());
    foreach ($users as $user) {
        if (!$message->draft || $user->id == $message->sender()->id) {
            $labels = label::get_by_user($user);
            shuffle($labels);
            $count = random_count(0, MESSAGE_LABEL_EX, MESSAGE_LABEL_SD);
            $message->set_labels($user, array_slice($labels, 0, $count));
        }
    }
}

function set_random_starred(message $message): void {
    $message->set_starred($message->sender(), random_bool(STARRED_FREQ));
    if (!$message->draft) {
        foreach ($message->recipients() as $user) {
            $message->set_starred($user, random_bool(STARRED_FREQ));
        }
    }
}

function set_random_unread(message $message, int $starttime, int $endtime): void {
    if (!$message->draft) {
        $freq = pow(($message->time - $starttime) / ($endtime - $starttime), UNREAD_FREQ_EXP);
        foreach ($message->recipients() as $user) {
            $message->set_unread($user, random_bool($freq));
        }
    }
}

main();
