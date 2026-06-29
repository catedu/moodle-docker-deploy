@format @format_tiles @format_tiles_title_symbols @javascript
Feature: When tile and activity titles are shown, symbols like "&" are displayed correctly

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
      | activity       | page                           |
      | course         | C1                             |
      | idnumber       | P1                             |
      | section        | 1                              |
      | completion     | 0                              |
      | name           | Page with & symbol             |
      | intro          | Page description with & symbol |
    And the following "activity" exists:
      | activity       | label                       |
      | course         | C1                          |
      | idnumber       | L1                          |
      | section        | 1                           |
      | completion     | 0                           |
      | intro          | Label content with & symbol |

  @javascript
  Scenario: Activity headings are shown correctly with "&" symbol for subtiles
    When I log in as "student1"
    And format_tiles subtiles are on for course "Course 1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I wait "1" seconds
    And I should see "Page with & symbol"
    And I should see "Label content with & symbol"
    And I should not see "Page with &amp; symbol"
    And I should not see "Label content with &amp; symbol"

  @javascript
  Scenario: Activity headings are shown correctly with "&" symbol for non-subtiles
    When I log in as "student1"
    And format_tiles subtiles are off for course "Course 1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I wait "1" seconds
    And I should see "Page with & symbol"
    And I should see "Label content with & symbol"
    And I should not see "Page with &amp; symbol"
    And I should not see "Label content with &amp; symbol"

  @javascript
  Scenario: Page modal content shown correctly with "&" symbol
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "Page with & symbol"
    And I wait until the page is ready
    And "Page with & symbol" "dialogue" should be visible
    And "Page description with & symbol" "text" should be visible
    And I should not see "Page description with &amp; symbol"

  @javascript
  Scenario: Tile names are shown correctly with "&" symbol in editing mode
    When I log in as "teacher1"
    And I am on "C1" course homepage with editing mode on
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on "Edit section name" "link" in the "li#section-2" "css_element"
    And I set the field "New name for section Tile 2" to "Tile name with & symbol"
    And I press enter
    And I am on "C1" course homepage with editing mode on
    And I wait until the page is ready
    And I should see "Tile name with & symbol"
    And I should not see "Tile name with &amp; symbol"
    And I am on "C1" course homepage with editing mode off
    And I wait until the page is ready
    And I should see "Tile name with & symbol"
    And I should not see "Tile name with &amp; symbol"
    And I log out tiles

    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Tile name with & symbol"
    And I should not see "Tile name with &amp; symbol"
