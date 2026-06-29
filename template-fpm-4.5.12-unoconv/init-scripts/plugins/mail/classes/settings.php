<?php
/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace local_mail;

class settings {
    /** @var bool Backup and restore enabled. */
    public bool $enablebackup = true;

    /** @var int Maximum number of recipients allowed per message. */
    public int $maxrecipients = 100;

    /** @var int Maximum number of results displayed in the user search. */
    public int $usersearchlimit = 100;

    /** @var int Maximum number of attachments allowed per message. */
    public int $maxfiles = 20;

    /** @var int Maximum size of attachments allowed per message. */
    public int $maxbytes;

    /** @var int Interval in seconds between automatic draft saves. */
    public int $autosaveinterval = 5;

    /** @var string[] Global trays displayed in menus: "starred", "sent", "drafts" and/or "trash". */
    public array $globaltrays = ['starred', 'sent', 'drafts', 'trash'];

    /** @var string Course trays displayed in menus: "none", "unread" or "all". */
    public string $coursetrays = 'none';

    /** @var string Type of course name displayed in menus: "shortname" or "fullname". */
    public string $coursetraysname = 'fullname';

    /** @var string Type of course name displayed in messages: "hidden", "shortname" or "fullname". */
    public string $coursebadges = 'fullname';

    /** @var int Course badges are truncated to this approximate length. */
    public int $coursebadgeslength = 20;

    /** @var string Type of course name used in the filter by course: "hidden", "shortname" or "fullname". */
    public string $filterbycourse = 'fullname';

    /** @var bool Incremental search enabled. */
    public bool $incrementalsearch = true;

    /** @var int Maximum number of recent messages included in incremental search. */
    public int $incrementalsearchlimit = 1000;

    /** @var string Type of course name displayed in the course link: "hidden", "shortname" or "fullname". */
    public string $courselink = 'hidden';

    /** @var array Array of message providers (name, displayname, locked, enabled). */
    public array $messageprocessors = [];

    /**
     * Private constructor.
     */
    private function __construct() {
        global $CFG;

        $this->maxbytes = get_max_upload_file_size($CFG->maxbytes ?? 0);
    }

    /**
     * Returns default settings.
     *
     * @return self
     */
    public static function defaults(): self {
        return new self();
    }

    /**
     * Returns the stored settings.
     *
     * @return self
     */
    public static function get(): self {
        $settings = new self();

        $config = get_config('local_mail');

        if (isset($config->enablebackup)) {
            $settings->enablebackup = (bool) $config->enablebackup;
        }
        if (isset($config->maxrecipients)) {
            $settings->maxrecipients = (int) $config->maxrecipients;
        }
        if (isset($config->usersearchlimit)) {
            $settings->usersearchlimit = (int) $config->usersearchlimit;
        }
        if (isset($config->maxfiles)) {
            $settings->maxfiles = (int) $config->maxfiles;
        }
        if (isset($config->maxbytes)) {
            $settings->maxbytes = (int) $config->maxbytes;
        }
        if (isset($config->autosaveinterval)) {
            $settings->autosaveinterval = (int) $config->autosaveinterval;
        }
        if (isset($config->globaltrays)) {
            if ($config->globaltrays) {
                $settings->globaltrays = explode(',', $config->globaltrays);
            } else {
                $settings->globaltrays = [];
            }
        }
        if (isset($config->coursetrays)) {
            $settings->coursetrays = $config->coursetrays;
        }
        if (isset($config->coursetraysname)) {
            $settings->coursetraysname = $config->coursetraysname;
        }
        if (isset($config->coursebadges)) {
            $settings->coursebadges = $config->coursebadges;
        }
        if (isset($config->coursebadgeslength)) {
            $settings->coursebadgeslength = (int) $config->coursebadgeslength;
        }
        if (isset($config->filterbycourse)) {
            $settings->filterbycourse = $config->filterbycourse;
        }
        if (isset($config->incrementalsearch)) {
            $settings->incrementalsearch = (bool) $config->incrementalsearch;
        }
        if (isset($config->incrementalsearchlimit)) {
            $settings->incrementalsearchlimit = (int) $config->incrementalsearchlimit;
        }
        if (isset($config->courselink)) {
            $settings->courselink = $config->courselink;
        }
        if (!get_config('message', 'local_mail_mail_disable')) {
            $enabled = explode(',', get_config('message', 'message_provider_local_mail_mail_enabled'));
            foreach (get_message_processors(true) as $processor) {
                $processorlocked = (bool) get_config('message', "{$processor->name}_provider_local_mail_mail_locked");
                $processorenabled = array_search($processor->name, $enabled) !== false;
                if ($processor->name == 'localmail' || $processorlocked && !$processorenabled) {
                    continue;
                }
                $settings->messageprocessors[] = [
                    'name' => $processor->name,
                    'displayname' => get_string('pluginname', 'message_' . $processor->name),
                    'locked' => $processorlocked,
                    'enabled' => array_search($processor->name, $enabled) !== false,
                ];
            }
        }

        return $settings;
    }

    /**
     * Returns whether the plugin is installed and upgraded.
     *
     * @return bool
     */
    public static function is_installed(): bool {
        global $CFG;

        $plugin = new \stdClass();
        include("$CFG->dirroot/local/mail/version.php");

        $version = get_config('local_mail', 'version');

        return $version == $plugin->version;
    }

    /**
     * Returns the enabled supported text editors for the current user.
     *
     * @return array
     */
    public static function text_editors(): array {
        global $CFG, $USER;

        $preferrededitor = get_user_preferences('htmleditor', '', $USER);
        $enablededitors = explode(',', $CFG->texteditors ?? '');

        if (in_array($preferrededitor, $enablededitors)) {
            array_unshift($enablededitors, $preferrededitor);
        }

        $supportededitors = [];
        foreach ($enablededitors as $editor) {
            if ($editor === 'tinymce') {
                $supportededitors[] = 'tiny';
            } else if (in_array($editor, ['atto', 'tiny', 'textarea'])) {
                $supportededitors[] = $editor;
            }
        }

        return array_values(array_unique($supportededitors));
    }
}
