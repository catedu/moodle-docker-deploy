<?php
/*
 * SPDX-FileCopyrightText: 2012-2013 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2014-2015 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

class label {
    /** @var string[] List of valid colors. */
    const COLORS = ['gray', 'blue', 'indigo', 'purple', 'pink', 'red', 'orange', 'yellow', 'green', 'teal', 'cyan'];

    /** @var int Label ID. */
    public int $id;

    /** @var int User ID. */
    public int $userid;

    /** @var string Name. */
    public string $name;

    /** @var string Color. */
    public string $color;

    /**
     * Constructs a label instance from a database record.
     *
     * @param \stdClass $record A database record from table local_mail_labels.
     */
    public function __construct(\stdClass $record) {
        $this->id = (int) $record->id;
        $this->userid = $record->userid;
        $this->name = self::nromalized_name($record->name);
        $this->color = preg_replace('/(light|dark)/', '', $record->color);
    }

    /**
     * Cache of user labels.
     *
     * @return \cache
     */
    public static function cache(): \cache {
        return \cache::make('local_mail', 'labels');
    }

    /**
     * Creates a label.
     *
     * @param user $user User..
     * @param string $name Name of the label.
     * @param string $color Color of the label, optional.
     * @return self Created label.
     */
    public static function create(user $user, string $name, string $color = ''): self {
        global $DB;

        assert(\core_text::strlen(self::nromalized_name($name)) > 0);
        assert($color == '' || in_array($color, self::COLORS));

        $record = new \stdClass();
        $record->userid = $user->id;
        $record->name = self::nromalized_name($name);
        $record->color = $color;
        $record->id = $DB->insert_record('local_mail_labels', $record);

        $label = new self($record);

        self::cache()->purge();

        return $label;
    }

    /**
     * Gets a label from the database.
     *
     * @param int $id ID of the label to get.
     * @return self
     */
    public static function get(int $id): self {
        $labels = self::get_many([$id]);

        return $labels[$id];
    }

    /**
     * Gets all labels of a user from the database.
     *
     * @param user User.
     * @return label[] Array of labels ordered by name and indexed by ID.
     */
    public static function get_by_user(user $user): array {
        global $DB;

        $cache = self::cache();

        if ($user->id == $cache->get('userid')) {
            return self::get_many($cache->get('labelids'));
        }

        $labels = [];
        foreach ($DB->get_records('local_mail_labels', ['userid' => $user->id]) as $record) {
            $labels[$record->id] = new self($record);
        }

        \core_collator::asort_objects_by_property($labels, 'name', \core_collator::SORT_NATURAL);

        self::cache()->purge();
        $cache->set_many($labels);
        $cache->set('labelids', array_keys($labels));
        $cache->set('userid', $user->id);

        return $labels;
    }

    /**
     * Gets multiple labels from the database.
     *
     * @param int[] $id IDs of the labels to get.
     * @return self[] Array of labels indexed by ID.
     */
    public static function get_many(array $ids): array {
        global $DB;

        $labels = self::cache()->get_many($ids);
        $missingids = array_filter($ids, fn($id) => !$labels[$id]);

        if ($missingids) {
            [$sqlid, $params] = $DB->get_in_or_equal($missingids);
            $records = $DB->get_records_select('local_mail_labels', "id $sqlid", $params);
            foreach ($missingids as $id) {
                if (isset($records[$id])) {
                    $labels[$id] = new self($records[$id]);
                } else {
                    throw new exception('errorlabelnotfound', $id);
                }
            }
        }

        return $labels;
    }

    /**
     * Removes leading, trailing and repeated spaces of a label name.
     *
     * @param string $name A label name.
     * @return string The normalized name.
     */
    public static function nromalized_name(string $name): string {
        return preg_replace('/\s+/u', ' ', trim($name));
    }

    /**
     * Deletes the label from the database.
     */
    public function delete(): void {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('local_mail_labels', ['id' => $this->id]);
        $DB->delete_records('local_mail_message_labels', ['labelid' => $this->id]);
        $transaction->allow_commit();

        self::cache()->purge();
    }

    /**
     * Updates the name and color of the label.
     *
     * @param string $name New name of the label.
     * @param string $color New color of the label.
     */
    public function update(string $name, string $color): void {
        global $DB;

        assert(\core_text::strlen(self::nromalized_name($name)) > 0);
        assert($color == '' || in_array($color, self::COLORS));

        $this->name = self::nromalized_name($name);
        $this->color = $color;

        $record = new \stdClass();
        $record->id = $this->id;
        $record->name = $this->name;
        $record->color = $this->color;

        $DB->update_record('local_mail_labels', $record);

        self::cache()->purge();
    }
}
