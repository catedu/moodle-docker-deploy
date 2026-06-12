<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Video course module class for format tiles.
 * @package    format_tiles
 * @copyright  2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\local;

/**
 * Video course module class for format tiles.
 * @package    format_tiles
 * @copyright  2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_cm {

    /**
     * Get a list of the CM IDs for this course which aare to be shown as video activities.
     * @param int $courseid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_video_cmids(int $courseid): array {
        global $DB;
        $cache = \cache::make('format_tiles', 'videocmids');
        $cachedvalue = $cache->get($courseid);
        if ($cachedvalue === false) {
            $result = [];
            $rs = $DB->get_recordset_sql(
                "SELECT cm.id, url.externalurl
                FROM {url} url
                JOIN {course_modules} cm ON cm.instance = url.id
                JOIN {modules} m ON m.id = cm.module and m.name = 'url'
                WHERE url.course = ? AND
                (url.externalurl LIKE '%youtube.%' OR url.externalurl LIKE '%vimeo.%' OR url.externalurl LIKE '%youtu.be/%')",
                [$courseid]
            );
            if ($rs->valid()) {
                foreach ($rs as $record) {
                    if (self::is_video_url($record->externalurl)) {
                        $result[] = (int)$record->id;
                    }
                }
                $rs->close();
            }
            $cache->set($courseid, $result);
            return $result;
        }
        return $cachedvalue;
    }


    /**
     * Is this particular CM a video activity?
     * @param int $courseid
     * @param int $cmid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_video_cm(int $courseid, int $cmid) {
        return in_array($cmid, self::get_video_cmids($courseid));
    }

    /**
     * If the URL is a YouTube or Vimeo URL etc, make some adjustments for embedding.
     * Teacher probably used standard watch URL so fix it if so.
     * @see \format_tiles_testcase::test_video_urls()
     * @param string $url
     * @return string|null string the URL if it was en embed video URL, null if not.
     */
    public static function check_modify_embedded_url(string $url): ?string {

        // Keep pattern replacements here specific as remote end may use params unknown to this code.
        // Sophisticated editors wanting to use other params can enter the embed URL directly and won't need this.

        // First match type - "watch" URL with no other params.
        // E.g. https://www.youtube.com/watch?v=abcdefghijk ==> https://www.youtube.com/embed/abcdefghijk transform.
        $pattern = '/^(http(s)??\:\/\/)?(www\.)?((youtube\.com\/watch\?v=[a-zA-Z0-9\-_]{11}))$/';
        if (preg_match($pattern, $url)) {
            return str_replace('watch?v=', 'embed/', $url);
        }

        // Second match type - "youtu.be" URL with no other params.
        // E.g. https://youtu.be/abcdefghijk ==> https://www.youtube.com/embed/abcdefghijk transform.
        $pattern = '/^(http(s)??\:\/\/)?(www\.)?((youtu\.be\/([a-zA-Z0-9\-_]{11})))$/';
        $matches = null;
        preg_match($pattern, $url, $matches);
        if ($matches && isset($matches[6])) {
            return 'https://www.youtube.com/embed/' . $matches[6];
        }

        // Third match type - "shorts" URL with no other params.
        // E.g. https://www.youtube.com/shorts/abcdefghijk ==> https://www.youtube.com/embed/abcdefghijk transform.
        $pattern = '/^(http(s)??\:\/\/)?(www\.)?((youtube\.com\/shorts\/[a-zA-Z0-9\-_]{11}))$/';
        if (preg_match($pattern, $url)) {
            return str_replace('shorts/', 'embed/', $url);
        }

        // Vimeo.
        // E.g. https://vimeo.com/347119375 ==> https://player.vimeo.com/video/347119375 transform.
        $pattern = '/^(https?:\/\/)?(www.)?vimeo.com\/([a-zA-Z0-9\-_]{6,11})$/';
        $matches = null;
        preg_match($pattern, $url, $matches);
        if ($matches && isset($matches[3])) {
            return "https://player.vimeo.com/video/$matches[3]";
        }
        return null;
    }

    /**
     * Is the URL provided a video URL (i.e. show Video icon for URL activity?).
     * @param string $url
     * @return bool
     */
    public static function is_video_url(string $url): bool {
        $patterns = [
            '/^(http(s)??\:\/\/)?(www\.)?(youtube\.com\/|youtu\.be\/)/',
            '/^(https?:\/\/)?(www.)?vimeo.com\//',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clear the cache of video CM IDs for this course.
     * @param int $courseid
     * @return void
     * @throws \coding_exception
     */
    public static function clear_cached_cmids(int $courseid) {
        $cache = \cache::make('format_tiles', 'videocmids');
        $cache->delete($courseid);
    }
}

