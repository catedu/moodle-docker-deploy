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

namespace format_tiles\output;

use core_courseformat\output\section_renderer;

/**
 * Basic renderer for tiles format.
 * @package format_tiles
 * @copyright 2022 David Watson
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Override this so that we can use our own local templates.
     * Used at present to ensure tiles specific editor controls are shown.
     * @return void
     */
    public function render_content() {
        $format = course_get_format($this->page->course->id);
        $course = $format->get_course();
        $sectionnumber = $format->get_sectionnum();
        if ($this->page->user_is_editing()) {
            // If user is editing, we render the page the new way.
            // We will use this for non editing as well in a later version, but not yet.
            $template = 'format_tiles/local/content';
            $contentclass = $format->get_output_classname('content');
            $displayoptions = [];
            $contentoutput = new $contentclass(
                $format,
                $sectionnumber,
                null,
                $displayoptions
            );
            $data = $contentoutput->export_for_template($this);
        } else {
            // If user not editing, for now we render the page the old way.
            if (self::display_multiple_section_page((bool)$sectionnumber, false)) {
                $template = 'format_tiles/multi_section_page';
                $templateable = new \format_tiles\output\course_output($course, false, null, $this);
                $data = $templateable->export_for_template($this);
            } else {
                $template = 'format_tiles/single_section_page';
                $templateable = new \format_tiles\output\course_output($course, false, $sectionnumber, $this);
                $data = $templateable->export_for_template($this);
            }
        }
        // We init JS here and not in format.php.
        // This is because in Moodle 4.4+ we may be in this function via section.php and not format.php.
        \format_tiles\local\util::init_js($course, $this->page->context->id, $sectionnumber);

        echo $this->render_from_template($template, $data);
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param \section_info|\stdClass $section The course_section entry from DB
     * @param \stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param \section_info|\stdClass $section The course_section entry from DB
     * @param int|\stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Get the updated rendered version of a section.
     *
     * This method will only be used when the course editor requires to get an updated cm item HTML
     * to perform partial page refresh. It will be used for supporting the course editor webservices.
     *
     * By default, the template used for update a section is the same as when it renders initially,
     * but format plugins are free to override this method to provide extra effects or so.
     *
     * In tiles, we override so that when teacher hides/shows tile with AJAX, re-rendered sec includes photo/icon.
     * @see \format_tiles\output\section_renderer::render_section()
     * @see \format_tiles\output\courseformat\content\section\controlmenu::export_for_template()
     * @param \core_courseformat\base $format the course format
     * @param \section_info $section the section info
     * @return string the rendered element
     */
    public function course_section_updated(\core_courseformat\base $format, \section_info $section): string {
        $sectionclass = $format->get_output_classname('content\\section');
        $output = new $sectionclass($format, $section);
        $renderer = new \format_tiles\output\section_renderer($this->page, $this->target);

        // This ends up calling \format_tiles\output\section_renderer::render_section().
        return $renderer->render($output);
    }

    /**
     * Should we display a multiple section page or not?
     * I.e. do we display all tiles on screen or just one open section?
     * @param bool $displaysection the param to say if we are displaying one sec and if so which.
     * @param bool $isediting are we editing or not.
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function display_multiple_section_page(bool $displaysection, bool $isediting): bool {
        global $SESSION;
        // We display the multi section page if the user is not requesting a specific single section.
        // We also display it if user is requesting a specific section (URL section.php?id=xx) with JS enabled.
        // We know they have JS if $SESSION->format_tiles_jssuccessfullyused is set.
        // In that case we show them the multi section page and use JS to open the section.
        if (optional_param('canceljssession', false, PARAM_BOOL)) {
            // The user is shown a link to cancel the successful JS flag for this session in <noscript> tags if their JS is off.
            unset($SESSION->format_tiles_jssuccessfullyused);
        }

        if (!$displaysection) {
            // If the URL does not request a specific section page (section.php?id=xx) we always show multiple secs.
            return true;
        }

        // Otherwise, even if URL requests single, we may show multiple in certain situations.
        if (\format_tiles\local\util::using_js_nav() && isset($SESSION->format_tiles_jssuccessfullyused)) {
            if (!$isediting && get_config('format_tiles', 'usejsnavforsinglesection')) {
                return true;
            }
        }
        return false;
    }

    /**
     * In Moodle 4.5 we may have sub-sections.
     * We override this here and use existing local code for subtiles pending full refactoring.
     * @param \renderable $widget
     * @return bool|string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_delegatedsection($widget) {
        $parentdata = $widget->export_for_template($this);
        $sectionnum = $parentdata->num;
        $templateable = new \format_tiles\output\course_output(
            $this->page->course, true, $sectionnum
        );
        $data = $templateable->export_for_template($this);
        $template = 'format_tiles/course_modules_subsection';

        // If subtiles are not being used we can use core widget data and template.
        $usecore = !$data['useSubtiles'] || $this->page->user_is_editing();
        if ($usecore) {
            $data = $parentdata;
            $data->contentcollapsed = true;
            $data->isdelegatedsection = true;
            $template = 'format_tiles/local/content/delegatedsection';
        }

        return $this->render_from_template($template, $data);
    }
}
