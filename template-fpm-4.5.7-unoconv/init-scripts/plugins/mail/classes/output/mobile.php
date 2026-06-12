<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail\output;

use local_mail\course;
use local_mail\settings;
use local_mail\user;

class mobile {
    public static function init() {
        global $CFG;

        $user = user::current();

        if (!settings::is_installed() || !$user || !course::get_by_user($user)) {
            return ['disabled' => true];
        }

        return [
            'javascript' => file_get_contents("$CFG->dirroot/local/mail/classes/output/mobile-init.js"),
        ];
    }

    public static function view(array $args) {
        global $CFG;

        $url = new \moodle_url('/local/mail/view.php', $args);

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => '<core-iframe src="' . $url->out(false) . '"></core-iframe>',
                ],
            ],
            'javascript' => file_get_contents("$CFG->dirroot/local/mail/classes/output/mobile-view.js"),
        ];
    }
}
