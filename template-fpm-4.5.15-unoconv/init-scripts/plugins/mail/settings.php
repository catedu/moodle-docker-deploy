<?php
/*
 * SPDX-FileCopyrightText: 2012-2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * SPDX-FileCopyrightText: 2016-2017 Albert Gasset <albertgasset@fsfe.org>
 * SPDX-FileCopyrightText: 2017 Marc Català <reskit@gmail.com>
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use local_mail\settings;
use local_mail\output\strings;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $defaults = settings::defaults();

    $settings = new admin_settingpage('local_mail', strings::get('pluginname'));

    // Backup.
    $settings->add(new admin_setting_heading('local_mail_backup', get_string('backup'), ''));

    $name = 'local_mail/enablebackup';
    $visiblename = strings::get('configenablebackup');
    $description = strings::get('configenablebackupdesc');
    $defaultsetting = $defaults->enablebackup;
    $settings->add(new admin_setting_configcheckbox($name, $visiblename, $description, $defaultsetting));

    // New mail.
    $settings->add(new admin_setting_heading('local_mail_newmail', strings::get('newmail'), ''));

    // Number of recipients.
    $name = 'local_mail/maxrecipients';
    $visiblename = strings::get('configmaxrecipients');
    $description = strings::get('configmaxrecipientsdesc');
    $defaultsetting = $defaults->maxrecipients;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // User search limit.
    $name = 'local_mail/usersearchlimit';
    $visiblename = strings::get('configusersearchlimit');
    $description = strings::get('configusersearchlimitdesc');
    $defaultsetting = $defaults->usersearchlimit;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // Number of attachments.
    $name = 'local_mail/maxfiles';
    $visiblename = strings::get('configmaxattachments');
    $description = strings::get('configmaxattachmentsdesc');
    $defaultsetting = $defaults->maxfiles;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // Attachment size.
    $name = 'local_mail/maxbytes';
    $visiblename = strings::get('configmaxattachmentsize');
    $description = strings::get('configmaxattachmentsizedesc');
    $defaultsetting = $defaults->maxbytes;
    $choices = get_max_upload_sizes($CFG->maxbytes ?? 0, 0, 0, settings::get()->maxbytes);
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    // Autosave interval.
    $name = 'local_mail/autosaveinterval';
    $visiblename = strings::get('configautosaveinterval');
    $description = strings::get('configautosaveintervaldesc');
    $defaultsetting = $defaults->autosaveinterval;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // Trays.
    $settings->add(new admin_setting_heading('local_mail_trays', strings::get('trays'), ''));

    // Global trays.
    $name = 'local_mail/globaltrays';
    $visiblename = strings::get('configglobaltrays');
    $description = strings::get('configglobaltraysdesc');
    $defaultsetting = [];
    foreach ($defaults->globaltrays as $tray) {
        $defaultsetting[$tray] = 1;
    }
    $choices = [
        'starred' => strings::get('starredplural'),
        'sent' => strings::get('sentplural'),
        'drafts' => strings::get('drafts'),
        'trash' => strings::get('trash'),
    ];
    $settings->add(new admin_setting_configmulticheckbox($name, $visiblename, $description, $defaultsetting, $choices));

    // Course trays.
    $name = 'local_mail/coursetrays';
    $visiblename = strings::get('configcoursetrays');
    $description = strings::get('configcoursetraysdesc');
    $defaultsetting = $defaults->coursetrays;
    $choices = [
        'none' => get_string('none'),
        'unread' => strings::get('courseswithunreadmessages'),
        'all' => get_string('allcourses', 'search'),
    ];
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    // Course trays name.
    $name = 'local_mail/coursetraysname';
    $visiblename = strings::get('configcoursetraysname');
    $description = strings::get('configcoursetraysnamedesc');
    $defaultsetting = $defaults->coursetraysname;
    $choices = [
        'shortname' => get_string('shortname'),
        'fullname' => get_string('fullname'),
    ];
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    // Filter by course.
    $name = 'local_mail/filterbycourse';
    $visiblename = strings::get('configfilterbycourse');
    $description = strings::get('configfilterbycoursedesc');
    $defaultsetting = $defaults->filterbycourse;
    $choices = [
        'hidden' => get_string('hide'),
        'shortname' => get_string('shortname'),
        'fullname' => get_string('fullname'),
    ];
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    // Messages.
    $settings->add(new admin_setting_heading('local_mail_messages', strings::get('messages'), ''));

    // Course badge type.
    $name = 'local_mail/coursebadges';
    $visiblename = strings::get('configcoursebadges');
    $description = strings::get('configcoursebadgesdesc');
    $defaultsetting = $defaults->coursebadges;
    $choices = [
        'hidden' => get_string('hide'),
        'shortname' => get_string('shortname'),
        'fullname' => get_string('fullname'),
    ];
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    // Course badge length.
    $name = 'local_mail/coursebadgeslength';
    $visiblename = strings::get('configcoursebadgeslength');
    $description = strings::get('configcoursebadgeslengthdesc');
    $defaultsetting = $defaults->coursebadgeslength;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // Search.
    $settings->add(new admin_setting_heading('local_mail_search', strings::get('search'), ''));

    // Incremental search.
    $name = 'local_mail/incrementalsearch';
    $visiblename = strings::get('configincrementalsearch');
    $description = strings::get('configincrementalsearchdesc');
    $defaultsetting = $defaults->incrementalsearch;
    $settings->add(new admin_setting_configcheckbox($name, $visiblename, $description, $defaultsetting));

    // Incremental search limit.
    $name = 'local_mail/incrementalsearchlimit';
    $visiblename = strings::get('configincrementalsearchlimit');
    $description = strings::get('configincrementalsearchlimitdesc');
    $defaultsetting = $defaults->incrementalsearchlimit;
    $paramtype = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype));

    // Navigation.
    $settings->add(new admin_setting_heading('local_mail_navigation', strings::get('navigation'), ''));

    // Course link.
    $name = 'local_mail/courselink';
    $visiblename = strings::get('configcourselink');
    $description = strings::get('configcourselinkdesc');
    $defaultsetting = $defaults->courselink;
    $choices = [
        'hidden' => get_string('hide'),
        'shortname' => get_string('shortname'),
        'fullname' => get_string('fullname'),
    ];
    $settings->add(new admin_setting_configselect($name, $visiblename, $description, $defaultsetting, $choices));

    $ADMIN->add('localplugins', $settings);
}
