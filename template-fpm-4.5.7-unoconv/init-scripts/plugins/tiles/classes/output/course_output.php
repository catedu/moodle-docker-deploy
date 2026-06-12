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
 * Tiles course format, main course output class to prepare data for mustache templates
 *
 * @package format_tiles
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles\output;

use format_tiles\local\format_option;
use format_tiles\local\tile_photo;
use format_tiles\local\filters;
use format_tiles\local\util;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot .'/course/format/lib.php');

/**
 * Tiles course format, main course output class to prepare data for mustache templates
 * @copyright 2018 David Watson
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_output implements \renderable, \templatable {

    /**
     * Course object for this class
     * @var \stdClass
     */
    protected $course;
    /**
     * Whether this class is called from fragment API
     * @var bool
     */
    protected $fromajax;
    /**
     * The section number of the section we want to display
     * @var int
     */
    protected $sectionnum;
    /**
     * The course renderer object
     * @var \renderer_base
     */
    protected $courserenderer;

    /**
     * Course Module IDs for which modal windows should be used.
     * @var array of CM IDs
     */
    protected array $modalscmids;

    /**
     * User's device type e.g. DEVICE_TYPE_MOBILE ('mobile')
     * @var string
     */
    protected $devicetype;

    /**
     * The course format.
     * @var string
     */
    protected $format;

    /**
     * @var \course_modinfo|null
     */
    protected $modinfo;

    /**
     * @var bool
     */
    protected $isediting;

    /**
     * @var bool
     */
    protected $canviewhidden;

    /**
     * @var \context_course
     */
    protected $coursecontext;

    /**
     * @var \completion_info
     */
    protected $completioninfo;

    /**
     * @var bool
     */
    protected $completionenabled;

    /**
     * @var mixed
     */
    public $courseformatoptions;

    /**
     * Sometimes to avoid having multiple versions of this plugin we need to know current moodle release e.g. 4.3
     * @var float
     */
    protected $moodlerelease;

    /**
     * Whether we are using javascript animated navigation.
     * @var bool
     */
    protected $usingjsnav;

    /**
     * Identifier of the standard tile style (see settings.php).
     */
    const TILE_STYLE_STANDARD = 1;

    /**
     * Identifier of the tile style with title at bottom (see settings.php).
     */
    const TILE_STYLE_BOTTOM_TITLE = 2;

    /**
     * course_output constructor.
     * @param \stdClass $course the course object.
     * @param bool $fromajax Whether we are rendering for AJAX request.
     * @param int|null $sectionnum the id of the current section.
     * @param \renderer_base|null $courserenderer the course renderer.
     */
    public function __construct($course, $fromajax = false, $sectionnum = null, \renderer_base|null $courserenderer = null) {
        global $PAGE;
        $this->course = $course;
        $this->fromajax = $fromajax;
        $this->sectionnum = $sectionnum;
        if (!$fromajax) {
            $this->courserenderer = $courserenderer;
        }
        $this->devicetype = \core_useragent::get_device_type();
        $this->modalscmids = \format_tiles\local\modal_helper::get_modal_allowed_cm_ids_integer_list(
            $this->course->id, false
        );
        $this->format = course_get_format($this->course);
        $this->modinfo = get_fast_modinfo($this->course);

        // User could be editing if this is called from /course/section.php.
        $this->isediting = $PAGE->user_is_editing();
        $this->coursecontext = \context_course::instance($this->course->id);
        $this->canviewhidden = has_capability('moodle/course:viewhiddensections', $this->coursecontext);
        if ($this->course->enablecompletion && !isguestuser()) {
            $this->completioninfo = new \completion_info($this->course);
        }
        $this->completionenabled = $course->enablecompletion && !isguestuser();
        $this->courseformatoptions = $this->get_course_format_options($this->fromajax);

        $this->moodlerelease = util::get_moodle_release();

        $this->usingjsnav = \format_tiles\local\util::using_js_nav();
    }

    /**
     * Export the course data for the mustache template.
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;

        if (!$this->courserenderer) {
            $this->courserenderer = $output;
        }
        if ($this->fromajax) {
            try {
                // Issue #153 avoid multiple glossary auto link JS onclick events.
                $PAGE->requires->should_create_one_time_item_now('filter_glossary_autolinker');

            } catch (\Exception) {
                debugging('Could not set glossary autolink created', DEBUG_DEVELOPER);
            }
        }
        $data = $this->get_basic_data();
        // We have assembled the "common data" needed for both single and multiple section pages.
        // Now we can go off and get the specific data for the single or multiple page as required.

        // Only show section zero if we need it.
        $onmultisectionpage = $this->sectionnum === null;
        $processsectionzero = $onmultisectionpage ||
            (get_config('format_tiles', 'showseczerocoursewide') && !$this->isediting);
        if ($processsectionzero) {
            // Only show section 0 on multi section page, or single sec page with admin setting to show course wide.
            $data = $this->append_section_zero_data($data, $output);
        } else {
            $data['section_zero_show'] = 0;
        }
        if (!$onmultisectionpage) {
            return $this->append_single_section_page_data($output, $data);
        } else {
            // We are outputting multi section page.
            return $this->append_multi_section_page_data($data);
        }
    }

    /**
     * Get the basic data required to render (required whatever we are doing).
     * @return array data
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function get_basic_data() {
        global $SESSION, $USER;
        $data = ['editorwarnings' => []];
        $data['canedit'] = has_capability('moodle/course:update', $this->coursecontext);
        $data['canviewhidden'] = $this->canviewhidden;
        $data['courseid'] = $this->course->id;
        $data['completionenabled'] = $this->completionenabled;
        $data['istrackeduser'] = $this->completionenabled && $this->completioninfo->is_tracked_user($USER->id);
        $data['from_ajax'] = $this->fromajax;
        $data['ismobile'] = $this->devicetype == \core_useragent::DEVICETYPE_MOBILE;
        // If this session flag is set, user is being shown JS versions of pages.
        // Allow them to cancel the session var if they have no JS.
        $data['showJScancelLink'] = isset($SESSION->format_tiles_jssuccessfullyused) ? 1 : 0;
        $data['editing'] = $this->isediting;
        $data['sesskey'] = sesskey();
        $data['showinitialpageloadingicon'] = !$this->isediting
            && \format_tiles\local\dynamic_styles::page_needs_loading_icon($this->course->id);
        $data['jsnavadminallowed'] = get_config('format_tiles', 'usejavascriptnav');
        $data['usingjsnav'] = $this->usingjsnav ? 1 : 0;
        $data['jsnavuserenabled'] = !get_user_preferences('format_tiles_stopjsnav');
        $data['abovetilescontrols'] = $this->get_above_tiles_controls($data['jsnavadminallowed'], $data['usingjsnav']);
        $data['hasabovetilescontrols'] = !empty($data['abovetilescontrols']);

        $data['useSubtiles'] = get_config('format_tiles', 'allowsubtilesview') && $this->courseformatoptions['courseusesubtiles'];
        $data['outerextraclasses'] = get_config('format_tiles', 'subtileiconcolourbackground')
            ? 'format-tiles-colour-subtile-icon-bg' : '';

        foreach ($this->courseformatoptions as $k => $v) {
            $data[$k] = $v;
        }
        // RTL support for nav arrows direction (Arabic/ Hebrew).
        $data['is-rtl'] = right_to_left();

        if ($data['canedit'] && format_option::needs_migration_incomplete_warning($this->course->id)) {
            $message = get_string('coursephotomigrationincomplete', 'format_tiles');
            $message .= \html_writer::link(
                new \moodle_url('/course/format/tiles/editor/migratecoursedata.php', ['courseid' => $this->course->id]),
                '<i class="icon fa fa-cog me-1"></i>' . get_string('fixproblems', 'format_tiles'),
                ['class' => 'ms-1']
            );
            $data['editorwarnings'][] = [
                'text' => $message,
                'icon' => 'exclamation-triangle', 'class' => 'warning',
            ];
        }
        $data['singlesectionnum'] = $this->sectionnum;
        return $data;
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $section
     * @return string
     */
    protected function temp_format_summary_text($section) {
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $this->coursecontext->id, 'course', 'section', $section->id);

        $options = new \stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $section
     * @return string|null
     * @throws \coding_exception
     */
    protected function temp_section_availability_message($section): ?string {
        $widgetclass = $this->format->get_output_classname('content\\section\\availability');
        $widget = new $widgetclass($this->format, $section);
        $result = $this->courserenderer->render($widget);
        return trim(strip_tags($result)) ? $result : null;
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param \cm_info $mod
     * @return bool|string
     * @throws \coding_exception
     */
    protected function temp_course_section_cm_availability($mod) {
        if ($this->courseformatoptions['courseusesubtiles']) {
            // Subtiles show a badge on the tile with a tool tip including full info.
            // This needs not to be truncated, whereas core $availabilityclass below will truncate it.
            // If there are multiple restrictions the tooltip on the subtile then shows them all.
            $ci = new \core_availability\info_module($mod);
            $fullinfo = $ci->get_full_information();
            $description = \core_availability\info::format_info($fullinfo, $this->course);
            if ($this->moodlerelease == 4.1) {
                // In Moodle 4.1 activities with multiple restrictions are shown with a show more link.
                // As that approach now deprecated, this is a quick/sub-optimal fix to remove "show more" link and un-hide items.
                $description = str_replace('d-none', '', $description);
                return str_replace('d-block showmore', 'd-none', $description);
            } else {
                return $description;
            }
        }
        $availabilityclass = $this->format->get_output_classname('content\\cm\\availability');
        $availability = new $availabilityclass(
            $this->format,
            $mod->get_section_info(),
            $mod,
        );
        return $this->courserenderer->render($availability);
    }

    /**
     * Append the data we need to render section zero.
     * @param [] $data
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function append_section_zero_data($data, $output) {
        $seczero = $this->modinfo->get_section_info(0);
        $coursemods = $this->section_course_mods($seczero, $output);
        $sectioninfo = $this->modinfo->get_section_info(0);
        $data['section_zero']['summary'] = self::temp_format_summary_text($seczero);
        $data['section_zero']['content']['course_modules'] = $coursemods->mods;
        $data['section_zero']['jsfooter'] = $coursemods->jsfooter;
        $data['section_zero']['secid'] = $sectioninfo->id;
        $data['section_zero']['title'] = $sectioninfo->name && trim($sectioninfo->name)
            ? format_string($sectioninfo->name, true, ['context' => $this->coursecontext]) : '';
        $data['section_zero']['is_section_zero'] = true;
        $data['section_zero']['tileid'] = 0;
        $data['section_zero']['visible'] = true;

        // Do not show if entirely empty.
        $data['section_zero_show'] = $seczero->summary || !empty($data['section_zero']['content']['course_modules']);
        $data['section_zero']['is_collapsible'] = get_config('format_tiles', 'seczerocollapsible');

        if ($this->courseformatoptions['courseusesubtiles'] && $this->courseformatoptions['usesubtilesseczero']) {
            $data['section_zero']['useSubtiles'] = 1;
        } else {
            $data['section_zero']['useSubtiles'] = 0;
        }
        return $data;
    }

    /**
     * Get the course format options (how depends on where we are calling from).
     * @param bool $fromajax is this request from AJAX.
     * @return array
     */
    protected function get_course_format_options($fromajax) {
        // Custom course settings not in course object if called from AJAX, so make sure we get them.
        $options = [
            'defaulttileicon', 'basecolour', 'courseusesubtiles', 'courseshowtileprogress',
            'displayfilterbar', 'usesubtilesseczero', 'courseusebarforheadings',
        ];
        $data = [];
        if (!$fromajax) {
            foreach ($options as $option) {
                if (isset($this->course->$option)) {
                    $data[$option] = $this->course->$option;
                }
            }
        } else {
            $data = $this->format->get_format_options();
        }
        if (!get_config('format_tiles', 'allowsubtilesview')) {
            $data['courseusesubtiles'] = 0;
        }
        return $data;
    }

    /**
     * Take the "common data" supplied as the $data argument, and build on it
     * with data which is specific to single section pages, then return
     * the amalgamated data
     * @param \renderer_base $output the renderer for this format
     * @param array $data the common data
     * @return array the amalgamated data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function append_single_section_page_data($output, $data) {
        // If we have nothing to output, don't.
        $data['is_single_section'] = true;
        if (!($thissection = $this->modinfo->get_section_info($this->sectionnum))) {
            // This section doesn't exist.
            debugging('Unknown course section ' . $this->sectionnum, DEBUG_DEVELOPER);
            return $data;
        }
        if (!$thissection->uservisible) {
            // Can't view this section - in that case the template will just render 'Not available' and nothing else.
            $data['hidden_section'] = true;
            return $data;
        }

        // Data for the requested section page.
        $data['title'] = format_string(get_section_name($this->course, $thissection->section));
        if (get_config('format_tiles', 'enablelinebreakfilter')) {
            // No need to line break here as we have plenty of room, so remove the char by passing true.
            $data['title'] = $this->apply_linebreak_filter($data['title'], true);
        }
        $data['summary'] = self::temp_format_summary_text($thissection);
        $data['tileid'] = $thissection->section;
        $data['secid'] = $thissection->id;
        $data['tileicon'] = format_option::get($this->course->id, format_option::OPTION_SECTION_ICON, $thissection->id);
        $data['tilenumber'] = $data['tileicon'] ? util::get_tile_number_from_icon_name($data['tileicon']) : null;
        $data['current'] = $this->format->is_section_current($thissection);

        // If photo tile backgrounds are allowed by site admin, prepare the image for this section.
        if (get_config('format_tiles', 'allowphototiles')) {
            $tilephoto = new tile_photo($this->coursecontext, $thissection->id);
            $tilephotourl = $tilephoto->get_image_url();
            if ($tilephotourl) {
                $data['phototileinlinestyle'] = 'style = "background-image: url(' . $tilephotourl . ');"';
                $data['hastilephoto'] = $tilephotourl ? 1 : 0;
                $data['phototileurl'] = $tilephotourl;
                $data['phototileediturl'] = new \moodle_url(
                    '/course/format/tiles/editor/editimage.php',
                    ['sectionid' => $thissection->id]
                );
            }
        }

        // Include completion help icon HTML.
        if ($this->completioninfo) {
            $data['completion_help'] = true;
        }

        // The list of activities on the page (HTML).
        $coursemods = $this->section_course_mods($thissection, $output);
        $data['course_modules'] = $coursemods->mods;
        $data['jsfooter'] = $coursemods->jsfooter;

        // If lots of content in this section, we include nav arrows again at bottom of page.
        // But otherwise not as looks odd when no content.
        $longsectionlength = 10000;
        if (strlen('single_sec_content') > $longsectionlength) {
            $data['single_sec_content_is_long'] = true;
        }
        $isdelegatedsection = $this->moodlerelease >= 4.5 && ($thissection->is_delegated() ?? false);
        if (!$data['usingjsnav']) {
            if ($isdelegatedsection) {
                $parentcm = $thissection->get_component_instance();
                $parentsection = $parentcm->get_parent_section();
                $data['parent_tile'] = [
                    'id' => $parentsection->id,
                    'title' => $this->format->get_section_name($parentsection),
                ];
            } else {
                $previousnext = $this->get_previous_next_section_ids($thissection->section);
                $data['previous_tile_id'] = $previousnext['previous'];
                $data['next_tile_id'] = $previousnext['next'];
            }
        }

        $data['visible'] = $thissection->visible;
        // If user can view hidden items, include the explanation as to why an item is hidden.
        if ($this->canviewhidden) {
            $data['availabilitymessage'] = self::temp_section_availability_message($thissection);
        }
        if ($isdelegatedsection) {
            $data['isdelegatedsection'] = true;
            $data['contentcollapsed'] = true;
        }
        return $data;
    }

    /**
     * Take the "common data" supplied as the $data argument, and build on it
     * with data which is specific to multiple section pages, then return
     * the amalgamated data
     * @param array $data the common data
     * @return array the amalgamated data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function append_multi_section_page_data($data) {
        $data['is_multi_section'] = true;
        $data['tiles'] = [];

        // If using completion tracking, get the data.
        if ($this->completionenabled) {
            $data['overall_progress']['num_complete'] = 0;
            $data['overall_progress']['num_out_of'] = 0;
        }
        $data['hasNoSections'] = true;

        // Before we start the section loop. get key vars for photo tiles ready.
        $allowedphototiles = get_config('format_tiles', 'allowphototiles');
        $tilestyle = get_config('format_tiles', 'tilestyle') ?? self::TILE_STYLE_STANDARD;
        $data['isstyle-' . $tilestyle] = true;
        $data['tilestyle'] = $tilestyle;

        if ($allowedphototiles) {
            $data['allowphototiles'] = 1;
            $data['showprogressphototiles'] = get_config('format_tiles', 'showprogresssphototiles');
            $phototileids = format_option::get_element_ids_having_options(
                $this->course->id, format_option::OPTION_SECTION_PHOTO
            );
            $phototileextraclasses = 'phototile';
        } else {
            $phototileids = [];
            $phototileextraclasses = '';
        }

        $maxallowedsections = $this->format->get_max_sections();
        $sectioncountwarningissued = false;

        $previoustiletitle = '';
        $countincludedsections = 0;
        $uselinebreakfilter = get_config('format_tiles', 'enablelinebreakfilter');
        $secsall = $this->modinfo->get_section_info_all();
        if (count($secsall) <= 1) {
            // We only have section zero.
            $data['is_multi_section'] = false;
        }
        $usingoutcomesfilter = in_array(
            $this->courseformatoptions['displayfilterbar'],
            [format_option::FILTER_OUTCOMES_ONLY, format_option::FILTER_OUTCOMES_AND_NUMBERS]
        );

        foreach ($secsall as $sectionnum => $section) {
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display,
            // OR it is hidden but the course has a setting to display hidden sections as unavilable.

            // If we have sections with numbers greater than the max allowed, do not show them unless teacher.
            // (Showing more to editors allows editor to fix them).
            if ($countincludedsections >= $maxallowedsections) {
                if (!$data['canedit']) {
                    // Do not show them to students at all.
                    break;
                } else {
                    if (!$sectioncountwarningissued) {
                        $a = new \stdClass();
                        $a->max = $maxallowedsections;
                        $a->tilename = $previoustiletitle;
                        \core\notification::error(get_string('coursetoomanysections', 'format_tiles', $a));
                        $sectioncountwarningissued = true;
                    }
                    if ($countincludedsections > $maxallowedsections * 2) {
                        // Even if the user is editing, if we have a *very* large number of sections, we only show 2 x that number.
                        $data['showsectioncountwarning'] = true;
                        break;
                    }
                }
            }

            $isphototile = $allowedphototiles && in_array($section->id, $phototileids);
            $showsection = $section->uservisible ||
                ($section->visible && !$section->available && !empty($section->availableinfo));
            $isdelegated = $this->moodlerelease >= 4.5 && ($section->is_delegated() ?? false);
            if ($sectionnum != 0 && $showsection && !$isdelegated) {
                $rawtitle = $this->truncate_title(get_section_name($this->course, $sectionnum));
                if ($uselinebreakfilter) {
                    $title = $this->apply_linebreak_filter($rawtitle);
                } else {
                    $title = format_string($rawtitle);
                }
                $ariatitle = get_string('tilearialabel', 'format_tiles',
                    trim($title)
                        ? $title
                        : get_string('tilearialabel', 'format_tiles', $this->format->get_default_section_name($section))
                );
                if ($allowedphototiles && $tilestyle == self::TILE_STYLE_BOTTOM_TITLE && $isphototile) {
                    // Replace the last space with &nbsp; to avoid having one word on the last line of the tile title.
                    $title = preg_replace('/\s(\S*)$/', '&nbsp;$1', $title);
                }

                $longtitlelength = 65;

                $newtile = [
                    'tileid' => $section->section,
                    'secid' => $section->id,
                    'title' => $title,
                    'tilearialabel' => $ariatitle,
                    'tileicon' => format_option::get($this->course->id, format_option::OPTION_SECTION_ICON, $section->id),
                    'current' => course_get_format($this->course)->is_section_current($section),
                    'hidden' => !$section->visible,
                    'visible' => $section->visible,
                    'restrictionlock' => !($section->available),
                    'userclickable' => $section->available || $section->uservisible,
                    'titleclass' => strlen($title) >= $longtitlelength ? ' longtitle' : '',
                    'progress' => false,
                    'isactive' => $this->course->marker == $section->section,
                    'extraclasses' => "tilestyle-$tilestyle ",
                    'isdelegated' => $isdelegated,
                ];

                $newtile['tilenumber'] = $newtile['tileicon'] ? util::get_tile_number_from_icon_name($newtile['tileicon']) : null;

                // If photo tile backgrounds are allowed by site admin, prepare them for this tile.
                if ($isphototile) {
                    $tilephoto = new tile_photo($this->coursecontext, $section->id);
                    $tilephotourl = $tilephoto->get_image_url();

                    $newtile['extraclasses'] .= " $phototileextraclasses";
                    $newtile['phototileinlinestyle'] = 'style = "background-image: url(' . $tilephotourl . ');"';
                    $newtile['hastilephoto'] = $tilephotourl ? 1 : 0;
                    $newtile['phototileurl'] = $tilephotourl;
                    $newtile['phototileediturl'] = new \moodle_url(
                        '/course/format/tiles/editor/editimage.php',
                        ['sectionid' => $section->id]
                    );
                }

                // Include completion tracking data for each tile (if used).
                if ($section->visible && $this->completionenabled) {
                    if (isset($this->modinfo->sections[$sectionnum])) {
                        $completionthistile = $this->section_progress($sectionnum);
                        // Keep track of overall progress so we can show this too - add this tile's completion to the totals.
                        $data['overall_progress']['num_out_of'] += $completionthistile['outof'];
                        $data['overall_progress']['num_complete'] += $completionthistile['completed'];

                        // We only add the tile values to the individual tile if courseshowtileprogress is true.
                        // (Otherwise we only retain overall completion as above, not for each tile).
                        if ($this->courseformatoptions['courseshowtileprogress']) {
                            $showaspercent = $this->courseformatoptions['courseshowtileprogress'] == 2;
                            $newtile['progress'] = $this->completion_indicator(
                                $completionthistile['completed'],
                                $completionthistile['outof'],
                                $showaspercent,
                                false
                            );
                        }
                    }
                }
                if ($section->availability || !$section->visible) {
                    $availabilityclass = $this->format->get_output_classname('content\\section\\availability');
                    $availability = new $availabilityclass($this->format, $section);
                    // If item is restricted, user needs to know why.
                    if ($availability->has_availability($this->courserenderer)) {
                        $newtile['hasavailability'] = true;
                        $newtile['availabilitymessage'] = $this->courserenderer->render($availability);
                    } else if (!$section->visible) {
                        $newtile['availabilitymessage'] = get_string('hiddenfromstudents');
                    }
                }
                if ($usingoutcomesfilter) {
                    $newtile['tileoutcomeid'] = $section->tileoutcomeid;
                }
                // See below about when "hide add cm control" is true.
                $newtile['hideaddcmcontrol'] = false;
                $newtile['single_sec_add_cm_control_html'] = $this->courserenderer->course_section_add_cm_control(
                    $this->course, $section->section, 0
                );

                $newtile['is_expanded'] = false;

                // Finally add tile we constructed to the array.
                $data['tiles'][] = $newtile;
                $previoustiletitle = $title;
            } else if ($sectionnum == 0) {
                // Add in section zero completion data to overall completion count.
                if ($section->visible && $this->completionenabled) {
                    if (isset($this->modinfo->sections[$sectionnum])) {
                        $completionthistile = $this->section_progress($sectionnum);
                        // Keep track of overall progress so we can show this too - add this tile's completion to the totals.
                        $data['overall_progress']['num_out_of'] += $completionthistile['outof'];
                        $data['overall_progress']['num_complete'] += $completionthistile['completed'];
                    }
                }
            }
            // Check if it's a subsection and do not count if so as not a true section.
            if ($sectionnum > 0 && !$section->is_delegated()) {
                $countincludedsections++;
            }
        }

        // Now the filter buttons (if used).
        if ($this->courseformatoptions['displayfilterbar']) {
            $usingnumbersfilter = in_array(
                $this->courseformatoptions['displayfilterbar'],
                [format_option::FILTER_NUMBERS_ONLY, format_option::FILTER_OUTCOMES_AND_NUMBERS]
            );
            if ($usingnumbersfilter) {
                $data['filternumberedbuttons'] = filters::get_filter_numbered_buttons_data($data['tiles']);
            }
            if ($usingoutcomesfilter) {
                $outcomes = filters::get_course_outcomes($this->course->id);
                $firstid = empty($data['filternumberedbuttons']) ? 1 : count($data['filternumberedbuttons']) + 1;
                $data['filteroutcomebuttons'] = filters::get_filter_outcome_buttons_data($data['tiles'], $outcomes, $firstid);
            }
        }
        $data['has_filter_buttons'] = !empty($data['filternumberedbuttons']) || !empty($data['filteroutcomebuttons']);

        $data['section_zero_add_cm_control_html'] = $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
        if ($this->completionenabled && $data['overall_progress']['num_out_of'] > 0) {
            if (get_config('format_tiles', 'showoverallprogress')) {
                $data['overall_progress_indicator'] = $this->completion_indicator(
                    $data['overall_progress']['num_complete'],
                    $data['overall_progress']['num_out_of'],
                    true,
                    true
                );
                $data['overall_progress_indicator']['tileid'] = 0;
            }
        }
        return $data;
    }

    /**
     * Count the number of course modules with completion tracking activated
     * in this section, and the number which the student has completed
     * Exclude labels if we are using sub tiles, as these are not checkable
     * Also exclude items the user cannot see e.g. restricted
     * @param int $sectionnum the section number we want.
     * @return array with the completion data x items complete out of y
     */
    public function section_progress(int $sectionnum): array {
        $completed = 0;
        $outof = 0;
        $sectioncmids = array_key_exists($sectionnum, $this->modinfo->sections)
            ? $this->modinfo->sections[$sectionnum] : [];
        if (!empty($sectioncmids)) {
            $coursecms = $this->modinfo->cms;
            $includesubsectiondata = get_config('format_tiles', 'progressincludesubsections');
            foreach ($sectioncmids as $cmid) {
                $thismod = $coursecms[$cmid];
                $issubsection = $thismod->modname === 'subsection';
                if ($thismod->uservisible && !$thismod->deletioninprogress) {
                    if (!$issubsection && $this->completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                        $outof++;
                        $completiondata = $this->completioninfo->get_data($thismod, true);
                        if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                        ) {
                            $completed++;
                        }
                    } else if ($issubsection && $includesubsectiondata) {
                        // Add completion data for the subsection to the parent section totals.
                        $delegatedsectioninfo = $thismod->get_delegated_section_info();
                        if ($delegatedsectioninfo !== null && ($delegatedsectioninfo->sectionnum ?? null)) {
                            $delegatedsectiondata = $this->section_progress($delegatedsectioninfo->sectionnum);
                            $completed += $delegatedsectiondata['completed'];
                            $outof += $delegatedsectiondata['outof'];
                        }
                    }
                }
            }
        }
        return ['completed' => $completed, 'outof' => $outof];
    }

    /**
     * Take a title (e.g. from a section) and truncate it if too big for sub tile
     * @param string $title to truncated
     * @return string truncated
     */
    protected function truncate_title($title) {
        $maxtitlelength = 75;
        if (strlen($title) >= $maxtitlelength) {
            $lastspace = strripos(substr($title, 0, $maxtitlelength), ' ');
            $title = substr($title, 0, $lastspace) . ' ...';
        }
        return trim($title);
    }

    /**
     * Watch for the word joiner character '&#8288;' in very long tile titles.
     * When encountered on a tile title, this char is changed to '- ' to allow the text to wrap.
     * This is useful on tiles with long words in the title (e.g. German language).
     * @param string $text
     * @param bool $remove if we want just to remove the flag (no need to line break), pass true.
     * @return string
     */
    protected function apply_linebreak_filter(string $text, $remove = false) {
        $zerowidthspace = '&#8288;';
        $maxwidthfortilechars = 15;
        if (!$remove && strlen($text) > $maxwidthfortilechars) {
            // If the title is long, we want to line break with a -, so replace the zero width space with hyphen space.
            return format_string(str_replace($zerowidthspace, '- ', $text));
        } else {
            // If the title is short, we don't need to line break so delete the flag.
            return format_string(str_replace($zerowidthspace, '', $text));
        }
    }

    /**
     * Gets the data (context) to be used with the activityinstance template
     * @param \section_info $section the section object we want content for
     * @param \renderer_base $output
     * @see \cm_info for full detail of $mod instance variables
     * @see \core_completion\manager::get_activities() which covers similar ground
     * @see \core_course_renderer::course_section_cm_completion() which covers similar ground
     * In the snap theme, course_renderer::course_section_cm_list_item() covers similar ground
     * @return object
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function section_course_mods($section, $output): object {
        $result = (object)['mods' => [], 'jsfooter' => ''];
        if (!isset($section->section)) {
            debugging("section->section is not set", DEBUG_DEVELOPER);
        }
        if (!isset($this->modinfo->sections[$section->section]) || !$cmids = $this->modinfo->sections[$section->section]) {
            // There are no CMs for the section (i.e. section is empty) so we silently return.
            return $result;
        }
        if (empty($cmids)) {
            // There are no CMs for the section (i.e. section is empty) so we silently return.
            return $result;
        }

        $previouswaslabel = false;
        foreach ($cmids as $index => $cmid) {
            $mod = $this->modinfo->get_cm($cmid);
            if ($mod->deletioninprogress) {
                continue;
            }
            $moduledata = $this->course_module_data(
                $mod,
                $section,
                $previouswaslabel,
                $index == 0,
                $output
            );

            if (!empty($moduledata)) {
                $result->mods[] = $moduledata;
                $previouswaslabel = $mod->has_custom_cmlist_item();
            }
        }
        return $result;
    }

    /**
     * Assemble and return the data to render a single course module.
     * @param \cm_info $mod
     * @param \section_info $section
     * @param bool $previouswaslabel
     * @param bool $isfirst
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function course_module_data($mod, $section, $previouswaslabel, $isfirst, $output): array {
        global $CFG, $DB;
        $displayoptions = [];
        $obj = new \core_courseformat\output\local\content\section\cmitem($this->format, $section, $mod, $displayoptions);
        $moduleobject = (array)$obj->export_for_template($output);
        if ($this->canviewhidden) {
            $moduleobject['uservisible'] = true;
            $moduleobject['clickable'] = true;
        } else if (!$mod->uservisible && $mod->is_visible_on_course_page() && $mod->availableinfo && $mod->visible) {
            // Activity is not available, not hidden from course page and has availability info.
            // So it is actually visible on the course page (with availability info and without a link).
            $moduleobject['uservisible'] = true;
            $moduleobject['clickable'] = false;
        } else {
            $moduleobject['uservisible'] = $mod->is_visible_on_course_page();
            $moduleobject['clickable'] = $mod->uservisible;
        }

        // We check that the stealth function exists in case we are running in Totara or earlier Moodle, where it doesn't.
        $isstealth = method_exists($mod, 'is_stealth') && $mod->is_stealth();
        if (!$moduleobject['uservisible'] || $mod->deletioninprogress || (!$this->canviewhidden && $isstealth)) {
            return [];
        }
        // If the module isn't available, or we are a teacher (can view hidden activities) get availability info.
        if (!$mod->available || $this->canviewhidden) {
            $moduleobject['availabilitymessage'] = self::temp_course_section_cm_availability($mod);
        }
        $moduleobject['available'] = $mod->available;
        $moduleobject['cmid'] = $mod->id;
        $moduleobject['modcontextid'] = $mod->context->id;
        $moduleobject['activityname'] = $mod->get_formatted_name();
        $moduleobject['modname'] = $mod->modname;
        $moduleobject['url'] = $mod->url;
        $moduleobject['visible'] = $mod->visible;
        $moduleobject['content'] = $mod->get_formatted_content(['overflowdiv' => true, 'noclean' => true]);
        if (!$this->courseformatoptions['courseusesubtiles'] && $mod->indent) {
            $moduleobject['indentlevel'] = $mod->indent;
        }

        // We set this here, with the value from the last loop, before updating it in the next block.
        // So that we can use it again on the next loop.
        $moduleobject['previouswaslabel'] = $previouswaslabel;
        $treataslabel = $mod->has_custom_cmlist_item();
        if ($treataslabel) {
            $moduleobject['is_label'] = true;
            $moduleobject['long_label'] = strlen($mod->content) > 300 ? 1 : 0;
            if (!$isfirst && !$previouswaslabel && $this->courseformatoptions['courseusesubtiles']) {
                $moduleobject['hasSpacersBefore'] = 1;
            }
        }

        if (isset($mod->instance)) {
            $moduleobject['modinstance'] = $mod->instance;
        }
        $moduleobject['modresourceicon'] = $mod->modname == 'resource'
            ? util::get_mod_resource_type($mod->icon) : null;

        if (!$treataslabel) {
            $iconclass = '';
            $modiconurl = $mod->get_icon_url($output);
            if (!\format_tiles\local\util::has_monologo_icon('mod', $mod->modname)) {
                // To use the mod's non-monologo icon we need no filtering.
                $iconclass = 'nofilter';
            }

            // Turnitin logo is too small and blurry on subtiles and wrong colour.
            if ($mod->modname === 'turnitintooltwo' && $this->courseformatoptions['courseusesubtiles']) {
                if (file_exists("$CFG->dirroot/mod/turnitintooltwo/pix/tii-icon.png")) {
                    $modiconurl = $output->image_url('tii-icon', 'turnitintooltwo');
                    $iconclass = '';
                }
            }

            $moduleobject['icon'] = [
                'url' => $modiconurl,
                'label' => $moduleobject['activityname'],
                'iconclass' => $iconclass,
            ];
            $moduleobject['tileicon'] = false; // Template is shared with top level tile, so avoiding inheriting parent icon.
            $moduleobject['purpose'] = plugin_supports('mod', $mod->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER);
        }

        // Specific handling for modal items (e.g. PDFs) as allowed by site admin.
        if (\format_tiles\local\modal_helper::mod_uses_cm_modal_cache($mod->modname)) {
            $moduleobject['modalType'] = in_array($mod->id, $this->modalscmids)
                ? \format_tiles\local\modal_helper::cm_modal_type($this->course->id, $mod->id)
                : null;
        }

        // Issue 67 handling for LTI set to open in new window.
        // Where onclick is truthy, suggests core JS will open in new window so don't treat as tiles modal.
        if ($mod->onclick) {
            $moduleobject['onclick'] = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);
        }

        $moduleobject['showdescription'] =
            isset($mod->showdescription) && !$treataslabel ? $mod->showdescription : 0;
        if ($moduleobject['showdescription']) {
            // The reason we need 'noclean' arg here is that otherwise youtube etc iframes will be stripped out.
            $moduleobject['description'] = $mod->get_formatted_content(['overflowdiv' => false, 'noclean' => true]);
            if ($moduleobject['modalType'] ?? null == 'pdf' && $this->courseformatoptions['courseusesubtiles']) {
                // In this case we may want to add the PDF description to the markup so that modal can grab it from JS.
                $moduleobject['modalDescriptionHTML'] =
                    trim(strip_tags($moduleobject['description'])) ? $moduleobject['description'] : '';
            }
        }
        $moduleobject['extraclasses'] = $mod->extraclasses;
        $moduleobject['afterlink'] = $mod->afterlink;
        if ($isstealth) {
            $moduleobject['extraclasses'] .= ' stealth';
            $moduleobject['stealth'] = 1;
        } else if (
            (!$mod->visible && !$mod->visibleold)
            || !$mod->available
            || !$section->visible
        ) {
            $moduleobject['extraclasses'] .= ' dimmed';
        }
        $moduleobject['completion'] = $mod->completion;
        if ($mod->completion == COMPLETION_TRACKING_MANUAL) {
            $moduleobject['extraclasses'] .= " completion-enabled completion-manual";
        } else if ($mod->completion == COMPLETION_TRACKING_AUTOMATIC) {
            if ($mod->completionview ?? null == COMPLETION_VIEW_REQUIRED) {
                // Auto completion with a view required.
                $moduleobject['extraclasses'] .= " completion-enabled completion-view";
            } else if ($mod->completion == COMPLETION_TRACKING_AUTOMATIC) {
                // Auto completion with no view required (e.g. grade required).
                $moduleobject['extraclasses'] .= " completion-enabled completion-auto";
            }
        }

        if ($mod->modname == 'folder') {
            $moduleobject['url'] = new \moodle_url('/mod/folder/view.php', ['id' => $mod->id]);
        }
        if ($mod->modname == 'url'&& \format_tiles\local\video_cm::is_video_cm($this->course->id, $mod->id)) {
            $externalurl = $DB->get_field('url', 'externalurl', ['id' => $mod->instance]);
            $modifiedvideourl = \format_tiles\local\video_cm::check_modify_embedded_url($externalurl);
            if ($modifiedvideourl) {
                // Even though it's really a URL activity, display it as "video" activity with video icon.
                $videostring = get_string('displaytitle_mod_mp4', 'format_tiles');
                if ($this->courseformatoptions['courseusesubtiles']) {
                    $moduleobject['extraclasses'] .= ' video';
                    $moduleobject['modnameDisplay'] = $videostring;
                }
                $moduleobject['icon'] = [
                    'url' => $output->image_url("play", 'format_tiles'),
                    'label' => $videostring,
                ];
            }
        }

        $moduleobject['issubsection'] = $mod->modname == 'subsection';

        if (
            ($mod->modname === 'url' || $mod->modname === 'resource')
            && $this->devicetype != \core_useragent::DEVICETYPE_TABLET
            && $this->devicetype != \core_useragent::DEVICETYPE_MOBILE
        ) {
            // If the non JS link is used, it redirects from /mod/xxx/view.php to external or pluginURL.
            $moduleobject['url'] .= '&redirect=1';
        }

        // Now completion information for the individual course module.
        $completion = $mod->completion && $this->completioninfo && $this->completioninfo->is_enabled($mod)
            && $mod->available && $mod->modname !== 'subsection';
        if ($completion) {
            // Add completion icon to the course module if appropriate.
            $moduleobject['hascompletion'] = true;
            $completiondata = $this->completioninfo->get_data($mod, true);
            $moduleobject['completionstate'] = $completiondata->completionstate;
            $moduleobject['iscomplete'] = $completiondata->completionstate
                && $completiondata->completionstate !== COMPLETION_COMPLETE_FAIL;
            $moduleobject['completionstateInverse'] = $completiondata->completionstate == 1 ? 0 : 1;
            if ($mod->completion == COMPLETION_TRACKING_MANUAL) {
                $moduleobject['completionIsManual'] = 1;
                switch ($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $moduleobject['completionstring'] = get_string('togglecompletionincomplete', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE:
                        $moduleobject['completionstring'] = get_string('togglecompletioncomplete', 'format_tiles');
                        break;
                }
            } else { // Automatic.
                switch ($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $moduleobject['completionstring'] = get_string('complete-n-auto', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE:
                        $moduleobject['completionstring'] = get_string('complete-y-auto', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE_PASS:
                        $moduleobject['completionstring'] = get_string(
                            'completion-pass', 'core_completion', $moduleobject['activityname']
                        );
                        break;
                    case COMPLETION_COMPLETE_FAIL:
                        $moduleobject['completionstring'] = get_string(
                            'completion-fail', 'core_completion', $moduleobject['activityname']
                        );
                        $moduleobject['isfail'] = 1;
                        break;
                }
            }
        }
        return $moduleobject;
    }

    /**
     * For the legacy navigation arrows, establish the section ID of the next and previous sections.
     * @param int $currentsectionnum the section number of the section we are in.
     * @return array previous and next section numbers.
     */
    protected function get_previous_next_section_ids(int $currentsectionnum): array {
        $visiblesectionids = [];
        $currentsectionarrayindex = -1;
        foreach ($this->modinfo->get_section_info_all() as $section) {
            if ($section->section == 0 || ($this->moodlerelease >= 4.5 && $section->is_delegated())) {
                continue;
            }
            if ($section->uservisible) {
                $visiblesectionids[] = $section->id;
                if ($section->section <= $currentsectionnum) {
                    $currentsectionarrayindex++;
                }
            }
        }

        // If $currentsectionarrayindex is zero, this means we are on the first available section so there is no "previous".
        $previous = $currentsectionarrayindex <= 0 ? 0 : $visiblesectionids[$currentsectionarrayindex - 1];

        // If there is no item at the next index, there is no "next" (so set next to zero).
        $next = $visiblesectionids[$currentsectionarrayindex + 1] ?? 0;

        return ['previous' => $previous, 'next' => $next];
    }

    /**
     * Prepare the data required to render a progress indicator (.e. 2/3 items complete)
     * to be shown on the tile or as an overall course progress indicator
     * @param int $numcomplete how many items are complete
     * @param int $numoutof how many items are available for completion
     * @param boolean $aspercent should we show the indicator as a percentage or numeric
     * @param boolean $isoverall whether this is an overall course completion indicator
     * @return array data for output template
     */
    public function completion_indicator($numcomplete, $numoutof, $aspercent, $isoverall) {
        $percentcomplete = $numoutof == 0 ? 0 : round(($numcomplete / $numoutof) * 100, 0);
        $progressdata = [
            'numComplete' => $numcomplete,
            'numOutOf' => $numoutof,
            'percent' => $percentcomplete,
            'isComplete' => $numcomplete > 0 && $numcomplete == $numoutof ? 1 : 0,
            'isOverall' => $isoverall,
        ];
        if ($numoutof && $numcomplete < $numoutof) {
            $progressdata['progresstitle'] = get_string(
                'progresstitle',
                'format_tiles',
                (object)$progressdata
            );
        }
        if ($aspercent) {
            // Percent in circle.
            $progressdata['showAsPercent'] = true;
            $circumference = 106.8;
            $progressdata['percentCircumf'] = $circumference;
            $progressdata['percentOffset'] = round(((100 - $percentcomplete) / 100) * $circumference, 0);
        }
        $progressdata['isSingleDigit'] = $percentcomplete < 10; // Position single digit in centre of circle.
        return $progressdata;
    }


    /**
     * Get an array of the controls to show above the tiles e.g. high contrast mode.
     * @param bool $jsnavadminallowed
     * @param bool $usingjsnav
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception|\moodle_exception
     */
    public function get_above_tiles_controls(bool $jsnavadminallowed, bool $usingjsnav): array {
        $controls = [];
        $courseurl = '/course/view.php';
        $courseurlparams = ['id' => $this->course->id, 'sesskey' => sesskey()];
        if ($jsnavadminallowed) {
            $controls[] = [
                'url' => new \moodle_url($courseurl, array_merge($courseurlparams, ['format-tiles-action' => 'toggleanimatednav'])),
                'label' => get_string('jsactivate', 'format_tiles'),
                'checked' => $usingjsnav,
            ];
        }
        if (get_config('format_tiles', 'highcontrastmodeallow')) {
            $controls[] = [
                'url' => new \moodle_url(
                    $courseurl, array_merge($courseurlparams, ['format-tiles-action' => 'togglehighcontrast'])
                ),
                'label' => get_string('highcontrastmode', 'format_tiles'),
                'checked' => \format_tiles\local\util::using_high_contrast(),
            ];
        }
        return $controls;
    }
}
