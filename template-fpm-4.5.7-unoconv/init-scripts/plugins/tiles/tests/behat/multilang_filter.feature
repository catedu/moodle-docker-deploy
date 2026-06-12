@format @format_tiles @format_tiles_multi_lang_filter @javascript
Feature: Multi lang filter works on course headings and content

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion |
      | Course 1 | C1        | tiles  | 0             | 5           | 0                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity       | page                                                                                                                           |
      | course         | C1                                                                                                                             |
      | idnumber       | P1                                                                                                                             |
      | section        | 1                                                                                                                              |
      | completion     | 0                                                                                                                              |
      | name           | <span lang="ES" class="multilang">Página Español</span><span lang="EN" class="multilang">English page</span>                   |
      | intro          | <span lang="ES" class="multilang">Página Contenido Español</span><span lang="EN" class="multilang">English page content</span> |
    And the following "activity" exists:
      | activity       | label                                                                                                              |
      | course         | C1                                                                                                                 |
      | idnumber       | L1                                                                                                                 |
      | section        | 1                                                                                                                  |
      | completion     | 0                                                                                                                  |
      | intro          | <span lang="ES" class="multilang">Contenido Español</span><span lang="EN" class="multilang">English content</span> |
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"

  @javascript
  Scenario: Activity headings are filtered for subtiles
    When I log in as "student1"
    And format_tiles subtiles are on for course "Course 1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I should see "English page"
    And I should not see "Página Español"
    And I should see "English content"
    And I should not see "Contentido Español"

  @javascript
  Scenario: Activity headings are filtered for non-subtiles
    When I log in as "student1"
    And format_tiles subtiles are off for course "Course 1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I should see "English page"
    And I should not see "Página Español"
    And I should see "English content"
    And I should not see "Contentido Español"

  @javascript
  Scenario: Tile names are filtered
    When I log in as "teacher1"
    When I am on "Course 1" course homepage with editing mode on
    And I click on "Edit section name" "link" in the "li#section-1" "css_element"
    And I set the field "New name for section Tile 1" to "<span lang=\"ES\" class=\"multilang\">Mosaico Español</span><span lang=\"EN\" class=\"multilang\">English Tile</span>"
    And I press enter
    And I wait until the page is ready
    And I am on "Course 1" course homepage with editing mode off
    And I should see "English Tile"
    And I should not see "Mosaico Español"

  @javascript
  Scenario: Page modal content is filtered
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "English page"
    And I wait until the page is ready
    And "English page" "dialogue" should be visible
    And "English page content" "text" should be visible
    And "Página Contenido Español" "text" should not be visible
