<?php
/*
 * SPDX-FileCopyrightText: 2012-2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2014-2020 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2016-2025 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use local_mail\course;
use local_mail\exception;
use local_mail\external;
use local_mail\message;
use local_mail\output\strings;
use local_mail\settings;
use local_mail\user;

function local_mail_pluginfile(
    $course,
    $cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    global $SITE;

    require_login($SITE, false);

    $user = user::current();

    if (!settings::is_installed() || !$user || $filearea != 'message') {
        return false;
    }

    // Check message.
    $messageid = (int) array_shift($args);
    try {
        $message = message::get($messageid);
    } catch (exception $e) {
        return false;
    }
    if (!$user->can_view_files($message)) {
        return false;
    }

    // Get file.
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_mail/$filearea/$messageid/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        return false;
    }

    if (PHPUNIT_TEST) {
        return $file;
    }

    // @codeCoverageIgnoreStart
    send_stored_file($file, null, 0, true, $options);
    // @codeCoverageIgnoreEnd
}

/**
 * Renders the navigation bar popover.
 *
 * @param renderer_base $renderer
 * @return string The HTML
 */
function local_mail_render_navbar_output(\renderer_base $renderer) {
    global $COURSE, $PAGE;

    $user = user::current();

    if (!settings::is_installed() || WS_SERVER || AJAX_SCRIPT || !$user || !course::get_by_user($user)) {
        return '';
    }

    $url = new moodle_url('/local/mail/view.php', ['t' => 'inbox']);
    $ismailpage = $PAGE->url->compare($url, URL_MATCH_BASE);

    // Fallback link to avoid layout changes during page load.
    $mailicon = html_writer::tag('i', '', [
        'class' => 'fa fa-fw fa-envelope-o icon m-0',
        'style' => "font-size: 16px",
    ]);
    if ($ismailpage) {
        $spinnericon = html_writer::tag('i', '', [
            'class' => 'fa fa-fw fa-spinner fa-pulse text-primary',
            'style' => "font-size: 16px",
        ]);
        $spinner = html_writer::div($spinnericon, 'position-absolute', [
            'style' => 'top: 50%; right: 0; transform: translateY(-18px)',
        ]);
    } else {
        $spinner = '';
    }
    $link = html_writer::tag('a', $mailicon . $spinner, [
        'href' => $url,
        'class' => 'nav-link btn h-100 d-flex align-items-center px-2 py-0',
        'title' => strings::get('pluginname'),
    ]);
    $output = html_writer::div($link, 'popover-region', ['id' => 'local-mail-navbar']);

    if (!$ismailpage) {
        // Pass all data via a script tag to avoid web service requests.
        $courses = external::get_courses_raw();
        $courseid = 0;
        if (array_search($COURSE->id, array_column($courses, 'id')) !== false) {
            $courseid = (int) $COURSE->id;
        }
        $data = [
            'userid' => $user->id,
            'courseid' => $courseid,
            'settings' => (array) settings::get(),
            'strings' => strings::get_many([
                'allcourses',
                'bcc',
                'cc',
                'changecourse',
                'compose',
                'course',
                'drafts',
                'inbox',
                'nocoursematchestext',
                'pluginname',
                'preferences',
                'sendmail',
                'sentplural',
                'starredplural',
                'to',
                'trash',
            ]),
            'courses' => $courses,
            'labels' => external::get_labels_raw(),
        ];
        $output .= html_writer::script('window.local_mail_navbar_data = ' . json_encode($data));
        $renderer = $PAGE->get_renderer('local_mail');
        $output .= $renderer->svelte_script('src/navigation.ts');
    }

    return $output;
}
