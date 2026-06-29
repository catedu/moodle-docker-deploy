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
 * Steps definitions related to Format tiles
 *
 * @package    format_tiles
 * @category   test
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Format tiles related steps definitions.
 *
 * @package    format_tiles
 * @category   test
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_format_tiles extends behat_base {
    /**
     * Set course format option for subtiles on for course.
     *
     * @Given /^format_tiles subtiles are on for course "(?P<coursefullname_string>(?:[^"]|\\")*)"$/
     * @param string $coursefullname
     * @throws dml_exception
     */
    public function format_tiles_sub_tiles_are_on_for_course($coursefullname) {
        $this->sub_tiles_on_off($coursefullname, 1);
    }


    /**
     * Check a tile has expected colour (bg and border top).
     * @Given /^Tile "(?P<tilenumber_int>(?:[\d]|\\")*)" has colour "(?P<colour_string>(?:[^"]|\\")*)"$/
     * @param int $tilenumber
     * @param string $expectedvalue
     * @return void
     */
    public function format_tiles_tile_has_colour(int $tilenumber, string $expectedvalue) {
        $selector = "#tile-$tilenumber";
        $property = 'border-top-color';
        $value = $this->element_get_css_value($selector, $property);

        // Expected value will be in rgb format like "22, 112, 204".
        // We ignore the opacity since if the element happens to be on hover during behat test, opacity will be < 1.
        // Actual value may be like rgb(22, 112, 204) rgba(22, 112, 204, 0.5) and both are ok.
        $pattern = '/rgba?\(' . $expectedvalue . '(\)|, \d\.\d\))$/';
        if (!preg_match($pattern, $value)) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "The property '$property' for the selector '$selector' is '$value' not '$expectedvalue'",
                $this->getSession()
            );
        }

        $selector = "#tile-$tilenumber .tile-bg";
        $property = 'background-color';
        $value = $this->element_get_css_value($selector, $property);
        $expectedvaluebg = "rgba($expectedvalue, 0.05)";
        if ($value != $expectedvaluebg) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "The property '$property' for the selector '$selector' is '$value' not '$expectedvaluebg'",
                $this->getSession()
            );
        }

        $jsscript = "(() => {return document.querySelector('style#format-tiles-dynamic-css') !== null;})();";
        $stylesincluded = $this->getSession()->evaluateScript($jsscript);
        if (!$stylesincluded) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Style tag for dynamic styles not found",
                $this->getSession()
            );
        }
    }

    /**
     * Check a tile has expected icon.
     * @Given /^Tile "(?P<tilenumber_int>(?:[\d]|\\")*)" should have icon "(?P<icon_string>(?:[^"]|\\")*)"$/
     * @param int $tilenumber
     * @param string $icon
     * @return void
     */
    public function format_tiles_tile_has_icon(int $tilenumber, string $icon) {
        $script = "(() => {return document.querySelector('#tileicon_$tilenumber i').classList.contains('fa-$icon');})();";
        $result = $this->getSession()->evaluateScript($script);
        if (!$result) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Icon $icon not found on tile $tilenumber",
                $this->getSession()
            );
        }
    }

    /**
     * Check if JS config <div> appears on page.
     * @Given /^Tiles JS config element exists on page$/
     * @return void
     */
    public function format_tiles_js_config_exists_on_page() {
        $script = "(() => {return document.getElementById('format-tiles-js-config') !== null;})()";
        $result = $this->getSession()->evaluateScript($script);
        if (!$result) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Tiles JS config div not found",
                $this->getSession()
            );
        }
    }

    /**
     * Get a CSS property for an element.
     *
     * @param string $selector e.g. '#tile-1'
     * @param string $property which CSS property e.g. 'border-top-color'
     * @return string $value
     */
    private function element_get_css_value(string $selector, string $property): string {
        $script = "(() => {
            const elem = document.querySelector('$selector');
            return elem !== null ? window.getComputedStyle(elem).getPropertyValue('$property') : null;
        })();";
        $result = $this->getSession()->evaluateScript($script);
        if (gettype($result) == 'string') {
            return $result;
        }
        throw new \Behat\Mink\Exception\ExpectationException(
            "Error getting CSS property '$property' for element '$selector'",
            $this->getSession()
        );
    }

    /**
     * * Set course format option for subtiles off for course.
     *
     * @Given /^format_tiles subtiles are off for course "(?P<coursefullname_string>(?:[^"]|\\")*)"$/
     * @param string $coursefullname
     * @throws dml_exception
     */
    public function format_tiles_sub_tiles_are_off_for_course($coursefullname) {
        $this->sub_tiles_on_off($coursefullname, 0);
    }

    /**
     * Set course format option for subtiles on or off for course.
     * @param string $coursefullname
     * @param int $onoff
     * @throws dml_exception
     */
    private function sub_tiles_on_off($coursefullname, $onoff) {
        global $DB;
        $onoff = $onoff ? 1 : 0;
        $courseid = $DB->get_field('course', 'id', ['fullname' => $coursefullname], MUST_EXIST);
        $courseformat = course_get_format($courseid);
        $courseformat->update_course_format_options(['id' => $courseid, 'courseusesubtiles' => $onoff]);
    }

    // @codingStandardsIgnoreStart.
    /**
     * Set the course format option for the progress indicator for a course as percent or fraction.
     *
     * @Given /^format_tiles progress indicator is showing as "(?P<progresstype_string>(?:[^"]|\\")*)" for course "(?P<coursefullname_string>(?:[^"]|\\")*)"$/
     * @param string $progresstype
     * @param string $coursefullname
     * @throws \Behat\Mink\Exception\ExpectationException
     * @throws dml_exception
     */
    public function progress_indicator_showing_as($progresstype, $coursefullname) {
        // @codingStandardsIgnoreEnd.
        global $DB;
        if (strtolower($progresstype) == 'percent') {
            $numerictype = 2;
        } else if (strtolower($progresstype) == 'numeric') {
            $numerictype = 1;
        } else {
            throw new \Behat\Mink\Exception\ExpectationException("Indicator type must be percent or numeric", $this->getSession());
        }
        $courseid = $DB->get_field('course', 'id', ['fullname' => $coursefullname], MUST_EXIST);
        $courseformat = course_get_format($courseid);
        $courseformat->update_course_format_options(['id' => $courseid, 'courseshowtileprogress' => $numerictype]);
    }

    // @codingStandardsIgnoreStart.
    /**
     * For a given page, check that its progress indicator shows a certain value (i.e. complete or not).
     *
     * @Then /^format_tiles progress for "(?P<modtype_string>(?:[^"]|\\")*)" called "(?P<activitytitle_string>(?:[^"]|\\")*)" in "(?P<coursefullname_string>(?:[^"]|\\")*)" is "(?P<value>\d+)" in the database$/
     * @param string $modtype
     * @param string $activitytitle
     * @param string $coursefullname
     * @param int $value
     * @throws \Behat\Mink\Exception\ExpectationException
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function progress_indicator_for_page_in_is_set_to($modtype, $activitytitle, $coursefullname, $value) {
        // @codingStandardsIgnoreEnd.
        global $DB;
        $user = $this->get_session_user();
        $courseid = $DB->get_field('course', 'id', ['fullname' => $coursefullname], MUST_EXIST);
        $modinfo = get_fast_modinfo($courseid);
        $cminfos = $modinfo->get_instances_of($modtype);
        $cms = [];
        foreach ($cminfos as $cminfo) {
            $cms[$cminfo->name] = $cminfo->id;
        }
        $this->wait_for_pending_js(); // Wait for AJAX request to complete.
        $this->getSession()->wait(1500);
        if (!isset($cms[$activitytitle])) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Activity type '$modtype' title '$activitytitle' not found in $coursefullname."
                        . "Available cms " . json_encode(array_keys($cms)),
                $this->getSession()
            );
        }
        $completionstate = $DB->get_field(
            'course_modules_completion',
            'completionstate',
            ['coursemoduleid' => $cms[$activitytitle], 'userid' => $user->id]
        );
        if (($completionstate == $value) || (!$completionstate && !$value)) {
            return;
        } else if ($completionstate === false) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Completion state should be $value but no record found for cmid $cms[$activitytitle] title $activitytitle",
                $this->getSession()
            );
        } else {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Completion state should be " . $value
                . " but found '" . $completionstate
                . "' for cm type " . $modtype . ' title ' . $activitytitle . ' cmid ' . $cms[$activitytitle],
                $this->getSession()
            );
        }
    }

    /**
     * Check that a named activity is dimmed.
     *
     * @Then /^activity in format tiles is dimmed "(?P<activityname_string>(?:[^"]|\\")*)"$/
     * @param string $activityname
     * @return bool
     */
    public function activity_in_format_tiles_is_dimmed($activityname) {
        $activityname = behat_context_helper::escape($activityname);
        // Var $xpath is to find the li (the ancestor) which contains an element where the text is activity name.
        $xpath = "//text()[contains(.," . $activityname . ")]/ancestor::*[self::li][1]";
        $activitynode = $this->find('xpath', $xpath, false);
        return $activitynode->hasClass('dimmed');
    }

    /**
     * Check that a named activity is not dimmed.
     *
     * @Then /^activity in format tiles is not dimmed "(?P<activityname_string>(?:[^"]|\\")*)"$/
     * @param string $activityname
     * @return bool
     */
    public function activity_in_format_tiles_is_not_dimmed($activityname) {
        return !$this->activity_in_format_tiles_is_dimmed($activityname);
    }

    /**
     * I click a tile (to open it)
     *
     * @Given /^I click on tile "(?P<tilenumber>\d+)"$/
     * @param string $tileumber
     * @throws Exception
     */
    public function i_click_on_tile($tileumber) {
        $tileid = behat_context_helper::escape("sectionlink-" . $tileumber);

        // Click the tile.
        $this->execute("behat_general::i_click_on", ["//a[@id=" . $tileid . "]", "xpath_element"]);
        $this->getSession()->wait(1500); // Important to wait here as page is scrolling and might click wrong thing after.
        $this->wait_for_pending_js(); // Wait for AJAX request to complete.
    }

    /**
     * I toggle expand or collapse section for edit
     *
     * @Given /^I toggle expand or collapse section "(?P<tilenumber>\d+)" for edit$/
     * @param string $tileumber
     * @throws Exception
     */
    public function i_toggle_expand_collapse_section_for_edit($tileumber) {
        $tileid = behat_context_helper::escape("collapssesection" . $tileumber);

        // Click the tile.
        $this->wait_for_pending_js();
        $this->getSession()->wait(1500);  // Just in case we did a collapse all - wait a bit.
        $this->execute("behat_general::i_click_on", ["//a[@id=" . $tileid . "]", "xpath_element"]);
        $this->wait_for_pending_js(); // Wait for AJAX request to complete.
        $this->getSession()->wait(3000); // Important to wait here as section is expanding with transition.
    }

    /**
     * I click a tile (to close it)
     * This is using the button at the top right of the content bearing section.
     *
     * @Given /^I click on close button for tile "(?P<tilenumber>\d+)"$/
     * @param string $tilenumber
     * @throws Exception
     */
    public function i_click_tile_close_button($tilenumber) {
        $tileid = behat_context_helper::escape("closesectionbtn-" . $tilenumber);

        // Click the button.
        $this->wait_for_pending_js();
        $this->execute("behat_general::i_click_on", ["//button[@id=" . $tileid . "]", "xpath_element"]);
        $this->execute('behat_general::wait_until_the_page_is_ready');
        $this->getSession()->wait(2000);
        $this->wait_for_pending_js(); // Wait for AJAX request to complete.
    }
    // @codingStandardsIgnoreStart.
    /**
     * I wait until a certain activity is visible following AJAX load.
     *
     * @Given /^I wait until activity "(?P<activitytitle_string>(?:[^"]|\\")*)" exists in "(?P<format_string>(?:[^"]|\\")*)" format$/
     * @param string $activitytitle
     * @param string $format
     * @throws Exception
     */
    public function wait_until_activity_exists_in_format($activitytitle, $format) {
        // @codingStandardsIgnoreEnd.
        if ($format == 'subtile' || $format == 'subtiles') {
            $liclass = 'subtile';
        } else if ($format == 'non-subtile') {
            $liclass = 'activity';
        } else {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Invalid activity format - must be subtile or non-subtile',
                $this->getSession()
            );
        }
        // We wait until the AJAX request finishes and the activity is visible.
        // xpath is to find the li (the ancestor) which contains an element where the text is activity name.
        $xpath = "//text()[contains(.,'" . $activitytitle . "')]/ancestor::li[contains(@class, '" . $liclass . "')]";
        $this->wait_for_pending_js();
        $this->execute(
            "behat_general::wait_until_exists",
            [$this->escape($xpath), "xpath_element"]
        );
    }

    /**
     * I click a certain activity.
     *
     * @Given /^I click format tiles activity "(?P<activityname_string>(?:[^"]|\\")*)"$/
     * @param string $activityname
     * @throws Exception
     */
    public function click_format_tiles_activity($activityname) {
        // As the open tile overlay is moved when page is ready, add a short pause to ensure that is complete.
        $this->wait_for_pending_js();
        $this->getSession()->wait(100);
        $this->execute("behat_general::i_click_on_in_the", [$this->escape($activityname), 'link', '#page-content', 'css_element']);
    }

    /**
     * I click a tile's progress indicator.
     *
     * @Given /^I click format tiles progress indicator for "(?P<activitytitle_string>(?:[^"]|\\")*)"$/
     * @param string $activitytitle
     * @throws Exception
     */
    public function i_click_progress_indicator_for($activitytitle) {
        $selector = "button[data-action=toggle-manual-completion][data-activityname='{$activitytitle}']";
        $this->execute("behat_general::i_click_on", [$selector, "css_element"]);
        $this->execute('behat_general::wait_until_the_page_is_ready');
        $this->wait_for_pending_js();  // Important to wait for pending JS here so as await AJAX response.
    }

    /**
     * I click a sub-tile's progress indicator.
     *
     * @Given /^I click format tiles subtile progress indicator for "(?P<activitytitle_string>(?:[^"]|\\")*)"$/
     * @param string $activitytitle
     * @throws Exception
     */
    public function i_click_subtile_progress_indicator_for($activitytitle) {
        $selector = "button[data-action=tiles-toggle-manual-completion-subtile][data-activityname='{$activitytitle}']";
        $this->execute("behat_general::i_click_on", [$selector, "css_element"]);
        $this->execute('behat_general::wait_until_the_page_is_ready');
        $this->wait_for_pending_js();  // Important to wait for pending JS here so as await AJAX response.
    }

    /**
     * Progress Indicator for tile shows correct out of values e.g. 1 / 2 complete.
     *
     * @Given /^format_tiles progress indicator for tile "(?P<tilenumber>\d+)" is "(?P<numcomplete>\d+)" out of "(?P<outof>\d+)"$/
     * @param string $tilenumber
     * @param string $numcomplete
     * @param string $outof
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Behat\Mink\Exception\ExpectationException
     */
    public function progress_indicator_tile_shows_outof($tilenumber, $numcomplete, $outof) {
        $xpath = "//div[@id='tileprogress-" . $tilenumber . "']";
        $node = $this->get_selected_node("xpath_element", $xpath);
        if ($node->getAttribute('data-numcomplete') !== $numcomplete) {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Tile ' . $tilenumber . ': Expected number complete ' . $numcomplete
                . ' but found ' . $node->getAttribute('data-numcomplete'),
                $this->getSession()
            );
        }
        if ($node->getAttribute('data-numoutof') !== $outof) {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Tile ' . $tilenumber . ': Expected number out of ' . $numcomplete
                . ' but found ' . $node->getAttribute('data-numoutof'),
                $this->getSession()
            );
        }
    }

    /**
     * Checks if the course section exists.
     *
     * @param int $sectionnumber
     * @return string The xpath of the section.
     */
    protected function tile_exists($sectionnumber) {

        // Just to give more info in case it does not exist.
        $xpath = "//li[@id='section-" . $sectionnumber . "']";
        $exception = new \Behat\Mink\Exception\ElementNotFoundException($this->getSession(), "Tile $sectionnumber ");
        $this->find('xpath', $xpath, $exception);

        return $xpath;
    }

    /**
     * Hides the specified visible tile. You need to be in the course page and on editing mode.
     *
     * @Given /^I hide tile "(?P<section_number>\d+)"$/
     * @param int $sectionnumber
     * @throws coding_exception
     */
    public function i_hide_tile($sectionnumber) {
        // Ensures the section exists.
        $xpath = $this->tile_exists($sectionnumber);
        $this->i_show_hide($sectionnumber, 'hidefromothers', $xpath);
    }

    /**
     * Hides the specified visible tile. You need to be in the course page and on editing mode.
     *
     * @Given /^I show tile "(?P<section_number>\d+)"$/
     * @param int $sectionnumber
     * @throws coding_exception
     */
    public function i_show_tile($sectionnumber) {
        // Ensures the section exists.
        $xpath = $this->tile_exists($sectionnumber);
        $this->i_show_hide($sectionnumber, 'showfromothers', $xpath);
    }

    /**
     * Show or hide a certain tile.
     *
     * @param int $sectionnumber
     * @param string $showhide
     * @param string $xpath
     * @throws coding_exception
     */
    private function i_show_hide($sectionnumber, $showhide, $xpath) {

        // If javascript is on, link is inside a menu.
        if ($this->running_javascript()) {
            $fullxpath = $xpath
                . "/descendant::div[contains(@class, 'section-actions')]/descendant::a[contains(@data-toggle, 'dropdown')]";
            $exception = new \Behat\Mink\Exception\ExpectationException(
                'Tile "' . $sectionnumber . '" edit menu was not found',
                $this->getSession()
            );
            $menu = $this->find('xpath', $fullxpath, $exception);
            $menu->click();
        }

        // Click on show/hide link.
        $strhide = get_string($showhide, 'format_tiles');
        $this->execute(
            'behat_general::i_click_on_in_the',
            [$strhide, "link", $this->escape($xpath), "xpath_element"]
        );

        if ($this->running_javascript()) {
            $this->getSession()->wait(self::get_timeout() * 1000, self::PAGE_READY_JS);
        }
    }

    /**
     * Logs out of the system.
     * Copied from behat_auth to add delay as sometimes not working.
     *
     * @Given /^I log out tiles$/
     */
    public function i_log_out_tiles() {

        // Wait for page to be loaded.
        $this->wait_for_pending_js();
        $this->getSession()->wait(1000); // Additional wait.

        // Click on logout link in footer, as it's much faster.
        $this->execute('behat_general::i_click_on_in_the', [get_string('logout'), 'link', '#page-footer', "css_element"]);
    }

    // @codingStandardsIgnoreStart.
    /**
     * Checks if the tile photo is set to a certain value
     *
     * @Given /^course "(?P<course_name>(?:[^"]|\\")*)" tile "(?P<section_number>\d+)" should show photo "(?P<photo_name>(?:[^"]|\\")*)"$/
     * @throws \Behat\Mink\Exception\ElementNotFoundException Thrown by behat_base::find
     * @throws \Behat\Mink\Exception\ExpectationException
     * @param string $coursename
     * @param int $sectionnumber
     * @param string $photoname
     * @return bool
     */
    public function tile_should_show_photo($coursename, $sectionnumber, $photoname) {
        // @codingStandardsIgnoreEnd.
        global $CFG, $DB;
        $courseid = $DB->get_field('course', 'id', ['fullname' => $coursename], IGNORE_MISSING);
        if (!$courseid) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Cannot find course ID for course $coursename",
                $this->getSession()
            );
        }
        $context = context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Cannot find course context for course $coursename, ID $courseid",
                $this->getSession()
            );
        }
        $sectionid = $DB->get_field(
            'course_sections',
            'id',
            ['course' => $courseid, 'section' => $sectionnumber],
            MUST_EXIST
        );

        $tilephoto = new \format_tiles\local\tile_photo($context, $sectionid);
        if (!$tilephoto->get_file()) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "File not found in files table for course $coursename | course ID $courseid "
                . "context ID $context->id | tile $sectionnumber | photo $photoname | element ID $sectionid",
                $this->getSession()
            );
        }

        $imageurl = $CFG->wwwroot . "/pluginfile.php/" . $context->id
            . '/format_tiles/tilephoto/' . $sectionid . '/tilephoto/' . $photoname;

        $tilestyle = get_config('format_tiles', 'tilestyle');
        $xpath = in_array($tilestyle, ["1", "2"])
            ? "//li[@id='tile-" . $sectionnumber . "']"
            : "//li[@id='tile-" . $sectionnumber . "']//div[contains(@class, 'photo-overlay')]";
        $node = $this->get_selected_node("xpath_element", $xpath);
        $nodestyle = $node->getAttribute('style');

        // File name will have _xxx added to end before extension, where xxx is random string.
        // E.g. apple_xeb.jpg.
        $imageextension = '.' . pathinfo($imageurl, PATHINFO_EXTENSION);
        $imagebaseurl = substr($imageurl, 0, stripos($imageurl, $imageextension));
        if (!$nodestyle || strpos($nodestyle, $imagebaseurl) === false || strpos($nodestyle, $imageextension) === false) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Tile $sectionnumber :Photo not displaying as background tile $sectionnumber course $coursename"
                . " could not find image base URL '$imagebaseurl' or extension '$imageextension'"
                . "in style string '$nodestyle' for tile style '$tilestyle'",
                $this->getSession()
            );
        }
        return true;
    }

    // @codingStandardsIgnoreStart.
    /**
     * Checks if the tile photo is set to a certain value
     *
     * @Given /^course "(?P<course_name>(?:[^"]|\\")*)" tile "(?P<section_number>\d+)" should show no photo$/
     * @throws \Behat\Mink\Exception\ElementNotFoundException Thrown by behat_base::find
     * @throws \Behat\Mink\Exception\ExpectationException
     * @param string $coursename
     * @param int $sectionnumber
     * @return bool
     */
    public function tile_should_show_no_photo($coursename, $sectionnumber) {
        // @codingStandardsIgnoreEnd.
        global $DB;
        $courseid = $DB->get_field('course', 'id', ['fullname' => $coursename], MUST_EXIST);
        $sectionid = $DB->get_field(
            'course_sections',
            'id',
            ['course' => $courseid, 'section' => $sectionnumber],
            MUST_EXIST
        );
        $photo = \format_tiles\local\format_option::get(
            $courseid,
            format_tiles\local\format_option::OPTION_SECTION_PHOTO,
            $sectionid
        );
        if ($photo) {
            throw new \Behat\Mink\Exception\ExpectationException(
                "Photo unexpectedly found for course $coursename tile $sectionnumber photo $photo",
                $this->getSession()
            );
        }
        return true;
    }

    // @codingStandardsIgnoreStart.
    /**
     * Check the "This will delete Tile x" message.
     * Moodle 4.1 and earlier has a different message, so we have this function to avoid separate plugin versions.
     * E.g. "This will delete Tile 5 and all the activities it contains".
     * @Then /^I should see section confirm delete message for "(?P<text_string>(?:[^"]|\\")*)"$/
     * @param string $sectionname
     * @return bool
     * @throws coding_exception
     */
    public function should_see_section_confirm_delete($sectionname) {
        // @codingStandardsIgnoreEnd.
        $moodlerelease = \format_tiles\local\util::get_moodle_release();
        $expectedstring = $moodlerelease < 4.2
            ? get_string('confirmdeletesection', 'moodle', $sectionname)
            : get_string('sectiondelete_info', 'courseformat', (object)['name' => $sectionname]);

        $this->execute('behat_general::assert_page_contains_text', [$expectedstring]);
        return true;
    }

    /**
     * Sets the completion tracking field to manual depending on Moodle version.
     * (To avoid having to have multiple Tiles plugin versions).
     *
     * @Given /^I set activity completion tracking form field to manual$/
     * @return void
     */
    public function i_set_completion_tracking_to_manual() {
        // Moodle 42 version: And I set the field "Completion tracking" to "Students can manually mark the activity as completed".
        // Moodle 43 version: And I set the field "Students must manually mark the activity as done" to "1".
        $moodlerelease = \format_tiles\local\util::get_moodle_release();
        $field = $moodlerelease <= 4.2
            ? "Completion tracking"
            : "Students must manually mark the activity as done";
        $value = $moodlerelease <= 4.2
            ? "Students can manually mark the activity as completed"
            : "1";
        $behatforms = behat_context_helper::get('behat_forms');
        $behatforms->i_set_the_field_to($field, $value);
    }
}
