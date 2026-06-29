<?php
/*
 * SPDX-FileCopyrightText: 2016 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2017 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

class backup_local_mail_plugin extends backup_local_plugin {
    protected function define_course_plugin_structure() {
        if (!get_config('local_mail', 'enablebackup')) {
            return;
        }

        if (!$this->get_setting_value('users') || $this->get_setting_value('anonymize')) {
            return;
        }

        $plugin = $this->get_plugin_element(null);

        // Elements.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $messages = new backup_nested_element('messages');
        $elements = ['courseid', 'subject', 'content', 'format', 'attachments', 'draft', 'time'];
        $message = new backup_nested_element('message', ['id'], $elements);
        $refs = new backup_nested_element('refs');
        $ref = new backup_nested_element('ref', ['id'], ['reference']);
        $users = new backup_nested_element('users');
        $user = new backup_nested_element('user', ['id'], ['userid', 'role', 'unread', 'starred', 'deleted']);
        $labels = new backup_nested_element('labels');
        $label = new backup_nested_element('label', ['id'], ['userid', 'name', 'color']);

        // Tree.
        $plugin->add_child($pluginwrapper);
        $pluginwrapper->add_child($messages);
        $messages->add_child($message);
        $message->add_child($refs);
        $refs->add_child($ref);
        $message->add_child($users);
        $users->add_child($user);
        $message->add_child($labels);
        $labels->add_child($label);

        // Messages source.
        $message->set_source_table('local_mail_messages', ['courseid' => backup::VAR_COURSEID], 'id');

        // Users source.
        // Roles are stored by name, for compatibility with older versions of the plugin.
        $ref->set_source_table('local_mail_message_refs', ['messageid' => '../../id'], 'id');
        $rolesql = 'CASE';
        foreach (\local_mail\message::role_names() as $role => $name) {
            $rolesql .= " WHEN role = $role THEN '$name'";
        }
        $rolesql .= ' END';
        $sql = "SELECT id, userid, $rolesql AS role, unread, starred, deleted
                FROM {local_mail_message_users}
                WHERE messageid = ?
                ORDER BY id";

        // Labels source.
        $user->set_source_sql($sql, ['messageid' => '../../id']);
        $sql = 'SELECT ml.id, l.userid, l.name, l.color
                FROM {local_mail_message_labels} ml
                JOIN {local_mail_labels} l ON l.id = ml.labelid
                WHERE ml.messageid = ?
                ORDER BY ml.id';
        $label->set_source_sql($sql, ['messageid' => '../../id']);

        // ID annotations.
        $user->annotate_ids('user', 'userid');
        $label->annotate_ids('user', 'userid');

        // File annotations.
        $message->annotate_files('local_mail', 'message', 'id');

        return $plugin;
    }
}
