<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

class message_search {
    /** @var user Search messages sent or received by this user. */
    public user $user;

    /** @var ?course If not null, search messages in this course. */
    public ?course $course = null;

    /** @var ?label If not null, search messages with this label. */
    public ?label $label = null;

    /** @var ?bool If not null, search messages with this draft status. */
    public ?bool $draft = null;

    /** @var int[] If not empty, search messages in which the user has one of these roles. */
    public array $roles = [];

    /** @var ?bool If not null, search messages with this unread status. */
    public ?bool $unread = null;

    /** @var ?bool If not null, search messages with this starred status. */
    public ?bool $starred = null;

    /** @var bool If true, search deleted messages. */
    public bool $deleted = false;

    /** @var string If not empty, search messages with this text in the subject, content, sender or receivers. */
    public string $content = '';

    /** @var string If not empty, search messages with this sender. */
    public string $sendername = '';

    /** @var string If not empty, search messages with this recipient. */
    public string $recipientname = '';

    /** @var bool If true, search nessages with attachments. */
    public bool $withfilesonly = false;

    /** @var int If not zero, search messages older than this date. */
    public int $maxtime = 0;

    /** @var ?int If not null, start serching from the position of this message (excluded). */
    public ?int $startid = null;

    /** @var ?int If not null, stop serching at the position of this message (excluded). */
    public ?int $stopid = null;

    /** @var bool Search messages from older to newer instead of from newer to older. */
    public bool $reverse = false;

    /**
     * Constructs the criteria for searching messages.
     *
     * @param user $user Search messages sent or received by this user.
     */
    public function __construct(user $user) {
        $this->user = $user;
    }

    /**
     * Convert search parameters to a string.
     *
     * Used for debugging.
     */
    public function __toString(): string {
        $params = [];
        $params['user'] = $this->user->id;
        if ($this->course) {
            $params['course'] = $this->course->id;
        }
        if ($this->label) {
            $params['label'] = $this->label->id;
        }
        if ($this->draft !== null) {
            $params['draft'] = $this->draft;
        }
        if ($this->roles) {
            $params['roles'] = $this->roles;
        }
        if ($this->unread !== null) {
            $params['unread'] = $this->unread;
        }
        if ($this->starred !== null) {
            $params['starred'] = $this->starred;
        }
        if ($this->deleted) {
            $params['deleted'] = true;
        }
        if ($this->content != '') {
            $params['content'] = $this->content;
        }
        if ($this->sendername != '') {
            $params['sendername'] = $this->sendername;
        }
        if ($this->recipientname != '') {
            $params['recipientname'] = $this->recipientname;
        }
        if ($this->withfilesonly) {
            $params['withfilesonly'] = true;
        }
        if ($this->maxtime) {
            $params['maxtime'] = $this->maxtime;
        }
        if ($this->startid) {
            $params['startid'] = $this->startid;
        }
        if ($this->stopid) {
            $params['stop'] = $this->stopid;
        }
        if ($this->reverse) {
            $params['reverse'] = true;
        }
        $str = '';
        foreach ($params as $name => $value) {
            $str .= $str ? "\n$name: " : "$name: ";
            if (is_array($value)) {
                $str .= implode(', ', $value);
            } else if (is_bool($value)) {
                $str .= $value ? 'true' : 'false';
            } else {
                $str .= $value;
            }
        }
        return $str;
    }

    /**
     * Counts the number of messages that match the search parameters.
     *
     * @return int
     */
    public function count(): int {
        global $DB;

        [$fromsql, $wheresql, $ordersql, $params] = $this->get_base_sql();

        $sql = "SELECT COUNT(*) $fromsql $wheresql";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Counts the number of messages per course that match the search parameters.
     *
     * @return int[] Array of number of messages, indexed by course ID.
     */
    public function count_per_course(): array {
        global $DB;

        [$fromsql, $wheresql, $ordersql, $params] = $this->get_base_sql();

        $sql = "SELECT i.courseid, COUNT(*) AS num $fromsql $wheresql GROUP BY i.courseid";

        $result = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            $result[$record->courseid] = (int) $record->num;
        }

        return $result;
    }

    /**
     * Counts the number of messages per label that match the search parameters.
     *
     * @return int[][] Array of number of messages, indexed by label ID and course ID.
     */
    public function count_per_label(): array {
        global $DB;

        [$fromsql, $wheresql, $ordersql, $params] = $this->get_base_sql(true);

        $sql = "SELECT MIN(i.id), i.labelid, i.courseid, COUNT(*) AS num $fromsql $wheresql GROUP BY i.labelid, i.courseid";

        $result = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            $result[$record->labelid][$record->courseid] = (int) $record->num;
        }

        return $result;
    }

    /**
     * Gets messages that match the search parameters.
     *
     * @param int $offset Skip this number of messages.
     * @param int $limit Maximum number of messages, 0 means no limit.
     * @return message[] Found messages, ordered from newer to older (unless reversed), and indexed by ID.
     */
    public function get(int $offset = 0, int $limit = 0): array {
        global $DB;

        [$fromsql, $wheresql, $ordersql, $params] = $this->get_base_sql();

        $sql = "SELECT i.messageid $fromsql $wheresql $ordersql";

        $records = $DB->get_records_sql($sql, $params, $offset, $limit);

        $result = message::get_many(array_keys($records));

        return $this->reverse ? array_reverse($result, true) : $result;
    }

    /**
     * Returns the base SQL and parameters of the search.
     *
     * @param bool $countperlabel Return SQL for counting per label.
     *
     * @return array Array with: FROM fragment, WHERE fragment, ORDER fragment and array of parameters.
     */
    private function get_base_sql(bool $countperlabel = false) {
        global $CFG, $DB;

        assert(!$this->label || $this->label->userid == $this->user->id);

        $selects = [];
        $params = [];

        $conditions = [
            'courseid' => $this->course->id ?? array_keys(course::get_by_user(($this->user))),
            'draft' => $this->draft ?? [0, 1],
            'role' => $this->roles ?: [message::ROLE_FROM, message::ROLE_TO, message::ROLE_CC, message::ROLE_BCC],
            'unread' => $this->unread ?? [0, 1],
            'starred' => $this->starred ?? [0, 1],
            'deleted' => $this->deleted ? message::DELETED : message::NOT_DELETED,
        ];

        if ($this->label || $countperlabel) {
            $fromsql = 'FROM {local_mail_message_labels} i';
            $conditions['labelid'] = $this->label->id ?? array_keys(label::get_by_user($this->user));
        } else {
            $fromsql = 'FROM {local_mail_message_users} i';
            $conditions['userid'] = $this->user->id;
        }

        foreach ($conditions as $field => $items) {
            if (is_array($items) && empty($items)) {
                // No courses or labels, return an empty result.
                $selects[] = '1 = 0';
            } else {
                [$condsql, $condparams] = $DB->get_in_or_equal($items, SQL_PARAMS_NAMED, $field);
                $selects[] = 'i.' . $field . ' ' . $condsql;
                $params = array_merge($params, $condparams);
            }
        }

        $selects[] = '(i.draft = 0 OR i.role = :rolefrom)';
        $params['rolefrom'] = message::ROLE_FROM;

        if ($this->content != '' || $this->withfilesonly) {
            $fromsql .= ' JOIN {local_mail_messages} m ON m.id = i.messageid';
        }

        if ($this->content != '') {
            $fullnamefield = $DB->sql_fullname('u.firstname', 'u.lastname');
            $usersql = "SELECT mu.messageid FROM {local_mail_message_users} mu"
                . " JOIN {user} u ON u.id = mu.userid"
                . " WHERE u.id <> :contentguestid AND u.deleted = 0 AND u.confirmed = 1"
                . " AND mu.role IN (:contentrolefrom, :contentroleto, :contentrolecc)"
                . " AND " . $DB->sql_like($fullnamefield, ':contentfullname', false, false);
            $subjectsql = $DB->sql_like('m.normalizedsubject', ':contentsubject', false, false);
            $contentsql = $DB->sql_like('m.normalizedcontent', ':contentcontent', false, false);
            $selects[] = "(($subjectsql) OR ($contentsql) OR i.messageid IN ($usersql))";
            $pattern = '%' . $DB->sql_like_escape(message::normalize_text($this->content, FORMAT_PLAIN)) . '%';
            $params['contentguestid'] = $CFG->siteguest;
            $params['contentfullname'] = $pattern;
            $params['contentrolefrom'] = message::ROLE_FROM;
            $params['contentroleto'] = message::ROLE_TO;
            $params['contentrolecc'] = message::ROLE_CC;
            $params['contentsubject'] = $pattern;
            $params['contentcontent'] = $pattern;
        }

        if ($this->sendername != '') {
            $fullnamefield = $DB->sql_fullname('u.firstname', 'u.lastname');
            $usersql = "SELECT mu.messageid FROM {local_mail_message_users} mu"
                . " JOIN {user} u ON u.id = mu.userid"
                . " WHERE u.id <> :sendernameguestid AND u.deleted = 0 AND u.confirmed = 1"
                . " AND mu.role = :sendernamerolefrom"
                . " AND " . $DB->sql_like($fullnamefield, ':sendernamefullname', false, false);
            $selects[] = "i.messageid IN ($usersql)";
            $pattern = '%' . $DB->sql_like_escape(message::normalize_text($this->sendername, FORMAT_PLAIN)) . '%';
            $params['sendernameguestid'] = $CFG->siteguest;
            $params['sendernamefullname'] = $pattern;
            $params['sendernamerolefrom'] = message::ROLE_FROM;
        }

        if ($this->recipientname != '') {
            $fullnamefield = $DB->sql_fullname('u.firstname', 'u.lastname');
            $usersql = "SELECT mu.messageid FROM {local_mail_message_users} mu"
                . " JOIN {user} u ON u.id = mu.userid"
                . " WHERE u.id <> :recipientnameguestid AND u.deleted = 0 AND u.confirmed = 1"
                . " AND mu.role IN (:recipientnameroleto, :recipientnamerolecc)"
                . " AND " . $DB->sql_like($fullnamefield, ':recipientnamefullname', false, false);
            $selects[] = "i.messageid IN ($usersql)";
            $pattern = '%' . $DB->sql_like_escape(message::normalize_text($this->recipientname, FORMAT_PLAIN)) . '%';
            $params['recipientnameguestid'] = $CFG->siteguest;
            $params['recipientnamefullname'] = $pattern;
            $params['recipientnameroleto'] = message::ROLE_TO;
            $params['recipientnamerolecc'] = message::ROLE_CC;
        }

        if ($this->withfilesonly) {
            $selects[] = 'm.attachments > 0';
        }

        if ($this->maxtime) {
            $selects[] = 'i.time <= :filtertime';
            $params['filtertime'] = $this->maxtime;
        }

        if ($this->startid) {
            $starttime = $DB->get_field('local_mail_messages', 'time', ['id' => $this->startid]);
            if ($starttime === false) {
                throw new exception('errormessagenotfound', $this->startid);
            }
            if ($this->reverse) {
                $selects[] = 'i.time >= :starttime1 AND (i.time > :starttime2 OR i.messageid > :startid)';
            } else {
                $selects[] = 'i.time <= :starttime1 AND (i.time < :starttime2 OR i.messageid < :startid)';
            }
            $params['startid'] = $this->startid;
            $params['starttime1'] = $starttime;
            $params['starttime2'] = $starttime;
        }

        if ($this->stopid) {
            $stoptime = $DB->get_field('local_mail_messages', 'time', ['id' => $this->stopid]);
            if ($stoptime === false) {
                throw new exception('errormessagenotfound', $this->stopid);
            }
            if ($this->reverse) {
                $selects[] = 'i.time <= :stoptime1 AND (i.time < :stoptime2 OR i.messageid < :stopid)';
            } else {
                $selects[] = 'i.time >= :stoptime1 AND (i.time > :stoptime2 OR i.messageid > :stopid)';
            }
            $params['stopid'] = $this->stopid;
            $params['stoptime1'] = $stoptime;
            $params['stoptime2'] = $stoptime;
        }

        $wheresql = 'WHERE ' . implode(' AND ', $selects);

        if ($this->reverse) {
            $ordersql = 'ORDER BY i.time ASC, i.messageid ASC';
        } else {
            $ordersql = 'ORDER BY i.time DESC, i.messageid DESC';
        }

        return [$fromsql, $wheresql, $ordersql, $params];
    }
}
