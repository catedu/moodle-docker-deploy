@format @format_tiles @format_tiles_icon_picker @javascript
Feature: Teacher can allocate icons to tiles

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname     | shortname | format | coursedisplay | numsections | enablecompletion | defaulttileicon |
      | Business Law | BL        | tiles  | 0             | 6           | 1                |  asterisk       |
    And the following "activities" exist:
      | activity | name         | intro                  | course | idnumber | section | visible |
      | quiz     | Test quiz V  | Test quiz description  | BL     | quiz1    | 1       | 1       |
      | page     | Test page V  | Test page description  | BL     | page1    | 1       | 1       |
      | forum    | Test forum V | Test forum description | BL     | forum1   | 1       | 1       |
      | url      | Test URL V   | Test url description   | BL     | url1     | 1       | 1       |
      | label    | Test label V | Test label description | BL     | label1   | 1       | 1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | BL     | student        |
      | teacher1 | BL     | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |

  @javascript
  Scenario: Teacher can use icon picker to pick icons, and can rename section afterwards
    When I log in as "teacher1"
    And I am on "Business Law" course homepage with editing mode on
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on "#tileicon_1" "css_element"
    And I wait until the page is ready
    And I wait "3" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I click on "button.pickericon[title=\"Map\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready
    And I click on "#tileicon_2" "css_element"
    And I wait until the page is ready
    And I click on "button.pickericon[title=\"Refresh\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready
    And I click on "#tileicon_3" "css_element"
    And I wait until the page is ready
    And I click on "button.pickericon[title=\"Star (shaded)\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready
    And I click on "#tileicon_4" "css_element"
    And I wait until the page is ready
    And I click on "button.pickericon[title=\"Tasks\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready
    And I click on "#tileicon_5" "css_element"
    And I wait until the page is ready
    And I click on "button.pickericon[title=\"British pound\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready

#    // one title edit just to check we can after the above (this is also done elsewhere)
    When I click on "Edit section name" "link" in the "li#section-1" "css_element"
    And I set the field "New name for section Tile 1" to "Setting up in business"
    And I press the enter key
    Then I should not see "Tile 1" in the "region-main" "region"
    And I should see "Setting up in business" in the "li#section-1" "css_element"
    And I am on "Business Law" course homepage
    And I should not see "Tile 1" in the "region-main" "region"

    When I click on "Edit section name" "link" in the "li#section-2" "css_element"
    And I set the field "New name for section Tile 2" to "Directors' Duties"
    And I press the enter key
    Then I should not see "Tile 2" in the "region-main" "region"
    And I should see "Directors' Duties" in the "li#section-2" "css_element"
    And I am on "Business Law" course homepage

    And I turn editing mode off
    And I wait until the page is ready
    And I wait "2" seconds
    And Tile "1" should have icon "map-o"
    And Tile "2" should have icon "refresh"
    And Tile "3" should have icon "star"
    And Tile "4" should have icon "tasks"
    And Tile "5" should have icon "gbp"
    And Tile "6" should have icon "asterisk"

    And I should see "Setting up in business" in the "li#tile-1" "css_element"
    And I should see "Directors' Duties" in the "li#tile-2" "css_element"

    And I click on tile "1"
    And I wait "1" seconds
    And I click on close button for tile "1"
    And I log out tiles
