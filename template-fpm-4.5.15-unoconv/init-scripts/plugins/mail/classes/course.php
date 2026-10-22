<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

class course {
    /** @var int Course ID. */
    public int $id;

    /** @var string Short name. */
    public string $shortname;

    /** @var string Full name. */
    public string $fullname;

    /** @var bool Visible. */
    public bool $visible;

    /** @var int Group mode. */
    public int $groupmode;

    /**
     * Constructs a course instance from a database record.
     *
     * @param \stdClass $record A database record from table course.
     */
    public function __construct(\stdClass $record) {
        $this->id = (int) $record->id;
        $this->shortname = $record->shortname;
        $this->fullname = $record->fullname;
        $this->visible = $record->visible;
        $this->groupmode = (int) $record->groupmode;
    }

    /**
     * Cache of user courses.
     *
     * @return \cache
     */
    public static function cache(): \cache {
        return \cache::make('local_mail', 'courses');
    }

    /**
     * Gets a course from the database.
     *
     * @param int $id ID of the course to get.
     * @return self
     */
    public static function get(int $id): self {
        $courses = self::get_many([$id]);

        return $courses[$id];
    }

    /**
     * Gets courses in which the given user can use mail.
     *
     * @param user $user User.
     * @return self[] Array of courses indexed by ID.
     */
    public static function get_by_user(user $user): array {
        $cache = self::cache();

        if ($user->id == $cache->get('userid')) {
            return self::get_many($cache->get('courseids'));
        }

        $courses = [];
        foreach (enrol_get_users_courses($user->id, true) as $record) {
            $context = \context_course::instance($record->id);
            if (has_capability('local/mail:usemail', $context, $user->id, false)) {
                $courses[$record->id] = new self($record);
            }
        }

        $cache->purge();
        $cache->set_many($courses);
        $cache->set('courseids', array_keys($courses));
        $cache->set('userid', $user->id);

        return $courses;
    }

    /**
     * Gets multiple courses from the database.
     *
     * @param int[] $ids IDs of the courses to get.
     * @return self[] Array of courses indexed by ID.
     */
    public static function get_many(array $ids): array {
        global $DB;

        $courses = self::cache()->get_many($ids);
        $missingids = array_filter($ids, fn($id) => !$courses[$id]);

        if ($missingids) {
            [$sqlid, $params] = $DB->get_in_or_equal($missingids, SQL_PARAMS_NAMED, 'courseid');
            $select = "id $sqlid AND id <> :siteid";
            $params['siteid'] = SITEID;
            $fields = 'id, shortname, fullname, visible, groupmode';
            $records = $DB->get_records_select('course', $select, $params, '', $fields);
            foreach ($missingids as $id) {
                if (isset($records[$id])) {
                    $courses[$id] = new self($records[$id]);
                } else {
                    throw new exception('errorcoursenotfound', $id);
                }
            }
        }

        return $courses;
    }

    /**
     * Gets the context of the course.
     *
     * @return \context_course
     */
    public function get_context(): \context_course {
        return \context_course::instance($this->id);
    }

    /**
     * Returns the course groups visible by the user.
     *
     * @param user $user User.
     * @return string[] Array of group names, including "All groups", indexed by ID.
     */
    public function get_viewable_groups(user $user): array {
        if ($this->groupmode == NOGROUPS) {
            return [];
        }

        $result = [];

        $accessallgroups = has_capability('moodle/site:accessallgroups', $this->get_context(), $user->id);

        if ($this->groupmode == VISIBLEGROUPS || $accessallgroups) {
            $result[0] = get_string('allgroups', 'local_mail');
            $userid = 0;
        } else {
            $userid = $user->id;
        }

        $groups = groups_get_all_groups($this->id, $userid);

        foreach ($groups as $group) {
            $result[$group->id] = $group->name;
        }

        return $result;
    }

    /**
     * Returns the course roles with mail capability visible by the given user.
     *
     * @param user $user User.
     * @return string[] Array of role names, indexed by ID.
     */
    public function get_viewable_roles(user $user): array {
        $result = [];
        [$needed, $forbidden] = get_roles_with_cap_in_context($this->get_context(), 'local/mail:usemail');
        foreach (get_viewable_roles($this->get_context(), $user->id) as $roleid => $rolename) {
            if (isset($needed[$roleid]) && !isset($forbidden[$roleid])) {
                $result[$roleid] = $rolename;
            }
        }
        return $result;
    }

    /**
     * URL of the course.
     *
     * @return string
     */
    public function url(): string {
        $url = new \moodle_url('/course/view.php', ['id' => $this->id]);
        return $url->out(false);
    }
}
