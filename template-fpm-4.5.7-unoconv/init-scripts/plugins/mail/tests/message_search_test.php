<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

/**
 * @covers \local_mail\message_search
 */
final class message_search_test extends test\testcase {
    public function test_count(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            $expected = count(self::search_result($messages, $search));
            self::assertEquals($expected, $search->count(), $search);
        }
    }

    public function test_count_per_course(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            $expected = [];
            foreach (self::search_result($messages, $search) as $message) {
                $expected[$message->course->id] = ($expected[$message->course->id] ?? 0) + 1;
            }
            self::assertEquals($expected, $search->count_per_course(), $search);
        }
    }

    public function test_count_per_label(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            $expected = [];
            foreach (self::search_result($messages, $search) as $message) {
                foreach ($message->get_labels($search->user) as $label) {
                    if (!$search->label || $search->label->id == $label->id) {
                        $expected[$label->id][$message->course->id] = ($expected[$label->id][$message->course->id] ?? 0) + 1;
                    }
                }
            }
            self::assertEquals($expected, $search->count_per_label(), $search);
        }
    }

    public function test_get(): void {
        [$users, $messages] = self::generate_random_data(true);

        foreach ($this->messages_search_cases($users, $messages) as $search) {
            $expected = self::search_result($messages, $search);
            $result = $search->get(0, 0);
            self::assert_array_of_objects($expected, $result, $search);

            // Offset and limit.
            $expected = array_slice($expected, 5, 20, true);
            $result = $search->get(5, 20);
            self::assert_array_of_objects($expected, $result, $search);
        }

        // Invalid startid.
        try {
            $search = new message_search($users[0]);
            $search->startid = 123;
            $search->get();
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }

        // Invalid stopid.
        try {
            $search = new message_search($users[0]);
            $search->stopid = 123;
            $search->get();
            self::fail();
        } catch (exception $e) {
            self::assertEquals('errormessagenotfound', $e->errorcode);
            self::assertEquals(123, $e->a);
        }
    }


    /**
     * Returns thee generated messages filtered by search parameters.
     *
     * @param message[] $messages Array of messages.
     * @param message_search $search Search parameters.
     * @return message[] Found messages, ordered from newer to older and indexed by ID.
     */
    protected static function search_result(array $messages, message_search $search): array {
        $courseids = $search->course ? [$search->course->id] : array_keys(course::get_by_user($search->user));

        $result = [];

        foreach (array_reverse($messages) as $message) {
            if (
                !in_array($message->course->id, $courseids) ||
                $search->user->id != $message->sender()->id && !$message->has_recipient($search->user) ||
                $search->user->id != $message->sender()->id && $message->draft ||
                $search->label && !$message->has_label($search->label) ||
                $search->draft !== null && $search->draft != $message->draft ||
                $search->roles && !in_array($message->role($search->user), $search->roles) ||
                $search->unread !== null && $message->unread($search->user) != $search->unread ||
                $search->starred !== null && $message->starred($search->user) != $search->starred ||
                !$search->deleted && $message->deleted($search->user) != message::NOT_DELETED ||
                $search->deleted && $message->deleted($search->user) != message::DELETED ||
                $search->withfilesonly && $message->attachments == 0 ||
                $search->maxtime && $message->time > $search->maxtime ||
                $search->startid && !$search->reverse && $message->id >= $search->startid ||
                $search->startid && $search->reverse && $message->id <= $search->startid ||
                $search->stopid && !$search->reverse && $message->id <= $search->stopid ||
                $search->stopid && $search->reverse && $message->id >= $search->stopid
            ) {
                continue;
            }
            if ($search->content != '') {
                $found = false;
                $pattern = message::normalize_text($search->content, FORMAT_PLAIN);
                if (\core_text::strpos(message::normalize_text($message->subject, FORMAT_PLAIN), $pattern) !== false) {
                    $found = true;
                }
                if (\core_text::strpos(message::normalize_text($message->content, FORMAT_PLAIN), $pattern) !== false) {
                    $found = true;
                }
                foreach ([$message->sender(), ...$message->recipients(message::ROLE_TO, message::ROLE_CC)] as $user) {
                    if (\core_text::strpos($user->fullname(), $pattern) !== false) {
                        $found = true;
                    }
                }
                if (!$found) {
                    continue;
                }
            }
            if ($search->sendername != '') {
                $pattern = message::normalize_text($search->sendername, FORMAT_PLAIN);
                if (\core_text::strpos($message->sender()->fullname(), $pattern) === false) {
                    continue;
                }
            }
            if ($search->recipientname != '') {
                $found = false;
                $pattern = message::normalize_text($search->recipientname, FORMAT_PLAIN);
                foreach ($message->recipients(message::ROLE_TO, message::ROLE_CC) as $user) {
                    if (\core_text::strpos($user->fullname(), $pattern) !== false) {
                        $found = true;
                    }
                }
                if (!$found) {
                    continue;
                }
            }

            $result[$message->id] = $message;
        }

        if ($search->reverse) {
            $result = array_reverse($result, true);
        }

        return $result;
    }
}
