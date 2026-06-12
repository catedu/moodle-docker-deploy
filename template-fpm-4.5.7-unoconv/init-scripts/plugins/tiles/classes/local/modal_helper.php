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
 * Helper class for dealing with modals class for format_tiles.
 * @package    format_tiles
 * @copyright  2023 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\local;

/**
 * Helper class for dealing with modals class for format_tiles.
 * @package    format_tiles
 * @copyright  2023 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modal_helper {

    /**
     * Which course modules is the site administrator allowing to be displayed in a modal?
     * @return array the permitted modules including resource types e.g. page, pdf, HTML
     * @throws \dml_exception
     */
    public static function allowed_modal_modules(): array {
        $devicetype = \core_useragent::get_device_type();
        if ($devicetype != \core_useragent::DEVICETYPE_TABLET && $devicetype != \core_useragent::DEVICETYPE_MOBILE
            && !(\core_useragent::is_ie())) {
            // JS navigation and modals in Internet Explorer are not supported by this plugin so we disable modals here.
            $resources = get_config('format_tiles', 'modalresources');
            $modules = get_config('format_tiles', 'modalmodules');
            return [
                'resources' => $resources ? explode(",", $resources) : [],
                'modules' => $modules ? explode(",", $modules) : [],
            ];
        } else {
            return ['resources' => [], 'modules' => []];
        }
    }


    /**
     * Get the course module IDs for any resource modules in this course that need a modal.
     * We tried to leverage the fact that cached cminfo already contains the resource file type in the "icon" field.
     * However that appears not always to accurately reflect the mime type of the file.
     * @param int $courseid
     * @param array $mimetypes
     * @return object containing cmids of relevant PDFs and HTML cms.
     */
    public static function get_resource_modal_cmids(int $courseid, array $mimetypes): object {
        global $DB, $CFG;
        $result = (object)['pdf' => [], 'html' => []];

        if (empty($mimetypes)) {
            return $result;
        }
        foreach ($mimetypes as $mimetype) {
            if (!in_array($mimetype, ['application/pdf', 'text/html'])) {
                throw new \Exception("Unexpected MIME type '$mimetype'");
            }
        }

        // To import RESOURCELIB_DISPLAY_XXX etc.
        require_once("$CFG->libdir/resourcelib.php");

        // This is not very efficient, so we cache the results elsewhere.
        $excludeddisplaytypes = [
            RESOURCELIB_DISPLAY_POPUP, RESOURCELIB_DISPLAY_NEW, RESOURCELIB_DISPLAY_DOWNLOAD,
        ];
        list($notinsql, $params) =
            $DB->get_in_or_equal($excludeddisplaytypes, SQL_PARAMS_NAMED, 'param', false);
        $params['courseid'] = $courseid;
        $params['contextmodule'] = CONTEXT_MODULE;

        // First get file cmids of relevant mime type.
        // There is an index on the files table component-filearea-contextid-itemid.
        // For resources with > 1 file attached, we are interested in the last file, if it's the right MIME type.
        // We use the last file (highest sort order) as that's the "main" file and what /mod/resource/view.php does.
        $basesql = "MAX(f.sortorder) AS sortorder
                    FROM {course_modules} cm
                    JOIN {modules} m ON m.id = cm.module and m.name = 'resource'
                    JOIN {resource} r ON cm.instance = r.id
                    JOIN {context} ctx ON ctx.contextlevel = :contextmodule AND ctx.instanceid = cm.id
                    JOIN {files} f ON f.component = 'mod_resource' AND f.filearea = 'content' AND f.contextid = ctx.id
                        AND f.itemid = 0 AND f.filesize > 0 and f.filename != '.'
                    WHERE cm.course = :courseid AND cm.deletioninprogress = 0 AND r.display $notinsql";

        // Get the details of the highest sortorder file on each CM of the relevant mime type, to check against main files.
        list($insql, $insqlparams) = $DB->get_in_or_equal($mimetypes, SQL_PARAMS_NAMED);
        $params = array_merge($params, $insqlparams);

        // Get the details of the highest sortorder ("main") file on each CM, as that's the only one that could be relevant.
        $mainfilecms = $DB->get_records_sql("SELECT cm.id AS cmid, $basesql GROUP BY cm.id", $params);
        if (empty($mainfilecms)) {
            return $result;
        }
        $lastmimetypefilecms = $DB->get_recordset_sql(
            "SELECT cm.id AS cmid, f.mimetype, $basesql AND f.mimetype $insql GROUP BY cm.id, f.mimetype", $params
        );
        // Now check if the highest sortorder ("main") file on each CM is of the right MIME type.
        if ($lastmimetypefilecms->valid()) {
            foreach ($lastmimetypefilecms as $lastmimetypefilecm) {
                $ismimetypefile = isset($mainfilecms[$lastmimetypefilecm->cmid])
                    && $mainfilecms[$lastmimetypefilecm->cmid]->sortorder == $lastmimetypefilecm->sortorder;
                if ($ismimetypefile) {
                    // The "main" file has the right MIME type, so we have a hit for this CM.
                    $mimetypekey = explode('/', $lastmimetypefilecm->mimetype)[1];
                    $result->{$mimetypekey}[] = (int)$lastmimetypefilecm->cmid;
                }
            }
        }
        $lastmimetypefilecms->close();
        foreach ([$result->pdf, $result->html] as $res) {
            $res = array_map(function($cmid) {
                return (int)$cmid;
            }, $res);
            sort($res);
        }
        return $result;
    }

    /**
     * Amongst other things this is to avoid re-implementing multiple files from the course index.
     * To know which resources to launch in modals, we can get the cmids of all resources which will launch as modals.
     * @param int $courseid
     * @param bool $excludeunavailable should we check availability of each cm in list and exclude unavailable?
     * @return object set of arrays of course module IDs to launch in modals.
     */
    public static function get_modal_allowed_cm_ids(int $courseid, bool $excludeunavailable): object {
        global $CFG;
        $cmids = (object)['page' => [], 'url' => [], 'pdf' => [], 'html' => []];

        // First check what modals site admin is allowing.
        $allowedmodals = self::allowed_modal_modules();
        $allowedmodals = array_merge($allowedmodals['modules'] ?? [], $allowedmodals['resources'] ?? []);
        if (empty($allowedmodals)) {
            return $cmids;
        }
        require_once("$CFG->libdir/resourcelib.php");
        $modinfo = get_fast_modinfo($courseid);

        // The cached values are for the course and does not take user visibility into account.
        // But they may save us some time.
        $cache = \cache::make('format_tiles', 'modalcmids');
        $excludeddisplaytypes = [RESOURCELIB_DISPLAY_POPUP, RESOURCELIB_DISPLAY_NEW];

        // First "page" course modules.
        if (in_array('page', $allowedmodals)) {
            $cachekey = $courseid . "_page";
            $cachedvalue = $cache->get($cachekey);
            if ($cachedvalue === false) {
                $pagecms = $modinfo->get_instances_of('page');
                foreach ($pagecms as $pagecm) {
                    // Issue #226 - it is unusual but possible for page to have 'open in pop up' set if admin as allowed.
                    $needsmodal = !$pagecm->onclick &&
                        !in_array($pagecm->get_custom_data()['display'] ?? null, $excludeddisplaytypes);
                    if ($needsmodal) {
                        $cmids->page[] = (int)$pagecm->id;
                    }
                }
                sort($cmids->page);
                $cache->set($cachekey, $cmids->page);
            } else {
                $cmids->page = $cachedvalue;
            }
        }

        // Then URL course modules.
        if (in_array('url', $allowedmodals)) {
            $cachekey = $courseid . "_url";
            $cachedvalue = $cache->get($cachekey);
            if ($cachedvalue === false) {
                $urlcms = $modinfo->get_instances_of('url');
                foreach ($urlcms as $urlcm) {
                    $needsmodal = !$urlcm->onclick &&
                        !in_array($urlcm->get_custom_data()['display'] ?? null, $excludeddisplaytypes);
                    if ($needsmodal) {
                        $cmids->url[] = (int)$urlcm->id;
                    }
                }
                sort($cmids->url);
                $cache->set($cachekey, $cmids->url);
            } else {
                $cmids->url = $cachedvalue;
            }
        }

        // Then resource course modules (PDF and HTML files).
        $mimemapping = ['pdf' => 'application/pdf', 'html' => 'text/html'];
        $allowedresourcemimetypes = [];
        foreach ($mimemapping as $key => $value) {
            if (in_array($key, $allowedmodals)) {
                $allowedresourcemimetypes[] = $value;
            }
        }

        $cachekeypdf = $courseid . "_pdf";
        $cachekeyhtml = $courseid . "_html";

        $cachedvaluepdf = $cache->get($cachekeypdf);
        $cachedvaluehtml = $cache->get($cachekeyhtml);

        if ($cachedvaluepdf === false || $cachedvaluehtml === false) {
            $resourcecmids = self::get_resource_modal_cmids($courseid, $allowedresourcemimetypes);
            $cache->set($cachekeypdf, $resourcecmids->pdf);
            $cache->set($cachekeyhtml, $resourcecmids->html);
            $cmids->pdf = $resourcecmids->pdf;
            $cmids->html = $resourcecmids->html;
        } else {
            $cmids->pdf = $cachedvaluepdf;
            $cmids->html = $cachedvaluehtml;
        }
        if (!$excludeunavailable) {
            // We may want to skip the availability check for efficiency, where it doesn't matter.
            // In that case, we are done here.
            return $cmids;
        }

        // Now we check each cmid for user visibility.
        $result = (object)['page' => [], 'url' => [], 'pdf' => [], 'html' => []];
        $modtypes = array_keys((array)$cmids);
        foreach ($modtypes as $modtype) {
            $rawcmids = $cmids->$modtype;
            if (!empty($rawcmids)) {
                $modinfo = $modinfo ?: get_fast_modinfo($courseid);
                foreach ($rawcmids as $rawcmid) {
                    try {
                        $cm = $modinfo->get_cm($rawcmid);
                    } catch (\Exception $e) {
                        // This is unexpected, but we don't want an exception in the footer so continue.
                        debugging("Could not find course mod $rawcmid " . $e->getMessage(), DEBUG_DEVELOPER);
                        continue;
                    }

                    if ($cm && !$cm->onclick && $cm->uservisible) {
                        $result->{$modtype}[] = (int)$cm->id; // Must be ints for JS to interpret correctly.
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get all the CM IDs allowed modals as a flat list of integers regardless of modal type.
     * @param int $courseid
     * @param bool $excludeunavailable should we check availability of each cm in list and exclude unavailable?
     * @return array
     */
    public static function get_modal_allowed_cm_ids_integer_list(int $courseid, bool $excludeunavailable): array {
        $cmidsbymodule = self::get_modal_allowed_cm_ids($courseid, $excludeunavailable);
        $modtypes = array_keys((array)$cmidsbymodule);
        $result = [];
        foreach ($modtypes as $modtype) {
            $result = array_merge($result, $cmidsbymodule->$modtype);
        }
        sort($result);
        return $result;
    }

    /**
     * Does a particular course module use a modal.
     * This does not check availability of the cm to the user.
     * @param int $courseid
     * @param int $cmid
     * @return bool
     */
    public static function cm_has_modal(int $courseid, int $cmid): bool {
        return (bool)self::cm_modal_type($courseid, $cmid);
    }

    /**
     * If a course module use a modal, what type? E.g. 'pdf', 'url'.
     * This does not check availability of the cm to the user.
     * @param int $courseid
     * @param int $cmid
     * @return string|null type of modal.
     */
    public static function cm_modal_type(int $courseid, int $cmid): ?string {
        $cmidsbymodule = self::get_modal_allowed_cm_ids($courseid, false);
        $modtypes = array_keys((array)$cmidsbymodule);
        foreach ($modtypes as $modtype) {
            if (!empty($cmidsbymodule->$modtype) && in_array($cmid, $cmidsbymodule->$modtype)) {
                return $modtype;
            }
        }
        return null;
    }

    /**
     * Is this module one which uses the cache to store modal cm data?
     * @param string $modname
     * @return bool
     */
    public static function mod_uses_cm_modal_cache(string $modname): bool {
        return in_array($modname, ['resource', 'page', 'url']);
    }

    /**
     * Clear the cache of resource modal IDs for a given course.
     * @param int $courseid
     * @param string $modulename optional module name e.g. resource, page, url.
     * @return bool
     */
    public static function clear_cache_modal_cmids(int $courseid, string $modulename = ''): bool {
        // See also \cache_helper::purge_by_event('format_tiles/modaladminsettingchanged') in settings.php.
        $cache = \cache::make('format_tiles', 'modalcmids');
        switch ($modulename) {
            case 'resource':
                $cache->delete($courseid . '_pdf');
                $cache->delete($courseid . '_html');
                return true;
            case 'url':
            case 'page':
                $cache->delete($courseid . '_' . $modulename);
                return true;
            case '':
                // In this case clear all caches for course.
                foreach (['_page', '_url', '_pdf', '_html'] as $cachekey) {
                    $cache->delete($courseid . $cachekey);
                }
                return true;
            default:
                // In this case do nothing.  E.g. if 'label' is passed we will reach here.
                // This method may be called when a course module we have no data for is updated.
                return false;
        }
    }
}
