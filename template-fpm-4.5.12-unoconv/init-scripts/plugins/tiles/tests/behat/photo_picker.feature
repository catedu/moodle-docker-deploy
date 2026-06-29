@format @format_tiles @format_tiles_photo_picker @javascript @_file_upload
Feature: Teacher can allocate photos to tiles

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname     | shortname | format | coursedisplay | numsections | enablecompletion |
      | Business Law | BL        | tiles  | 0             | 10          | 1                |
      | Course 2     | C2        | tiles  | 0             | 10          | 1                |
    And the following "activities" exist:
      | activity | name         | intro                  | course | idnumber | section | visible |
      | quiz     | Test quiz V  | Test quiz description  | BL     | quiz1    | 1       | 1       |
      | page     | Test page V  | Test page description  | BL     | page1    | 1       | 1       |
      | forum    | Test forum V | Test forum description | BL     | forum1   | 1       | 1       |
      | url      | Test URL V   | Test url description   | BL     | url1     | 1       | 1       |
      | label    | Test label V | Test label description | BL     | label1   | 1       | 1       |
      | url      | Test URL V   | Test url description   | C2     | url1     | 1       | 1       |
      | label    | Test label V | Test label description | C2     | label1   | 1       | 1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | BL     | student        |
      | student1 | C2     | student        |
      | teacher1 | BL     | editingteacher |
      | teacher1 | C2     | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |
      | allowphototiles        | 1        | format_tiles |

  # TODO this is monolithic and needs refactoring into smaller scenarios.

  @javascript
  Scenario: Teacher can use photo picker to pick photos (and icons), and student can view
    When I log in as "teacher1"
    And I am on "Business Law" course homepage with editing mode on
    And I wait until the page is ready
    And I click on "#tileicon_1" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I follow "Upload new photo"
    And I upload "course/format/tiles/tests/fixtures/images/placeholder_1.jpg" file to "Upload new photo" filemanager
    And I press "Save changes"
    And I wait until the page is ready
    And I should see "Image saved for 'Tile 1'"

    And I am on "Business Law" course homepage with editing mode on
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on "#tileicon_2" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on "button.pickericon[title=\"Refresh\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready
    And I wait "2" seconds

    And I click on "#tileicon_3" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I follow "Upload new photo"
    And I upload "course/format/tiles/tests/fixtures/images/placeholder_3.jpg" file to "Upload new photo" filemanager
    And I press "Save changes"
    And I wait until the page is ready
    And I should see "Image saved for 'Tile 3'"

    And I wait "1" seconds
    And I click on "#tileicon_7" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I follow "Photo library"
    And I wait until the page is ready
#    // We use title^= (starts with) because the image as saved will have had 3 random chars added e.g. placeholder_1_dwo.jpg
    And I click on ".photo[title^=\"placeholder_1_\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready

    And I turn editing mode off
    And I wait until the page is ready
    And I wait "2" seconds
    And course "Business Law" tile "1" should show photo "placeholder_1.jpg"
    And course "Business Law" tile "2" should show no photo
    And course "Business Law" tile "3" should show photo "placeholder_3.jpg"
    And course "Business Law" tile "4" should show no photo
    And course "Business Law" tile "5" should show no photo
    And course "Business Law" tile "6" should show no photo
    And course "Business Law" tile "7" should show photo "placeholder_1.jpg"
    And course "Business Law" tile "8" should show no photo

    And I am on "Course 2" course homepage with editing mode on
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on "#tileicon_3" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I follow "Photo library"
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on ".photo[title^=\"placeholder_1_\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready

    And I wait "1" seconds
    And I click on "#tileicon_6" "css_element"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Pick a new icon or background photo" "dialogue" should be visible
    And I follow "Photo library"
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on ".photo[title^=\"placeholder_3_\"]" "css_element" in the "#icon_picker_modal" "css_element"
    And I wait until the page is ready

    And I turn editing mode off
    And I wait until the page is ready
    And I wait "2" seconds
    And course "Course 2" tile "1" should show no photo
    And course "Course 2" tile "2" should show no photo
    And course "Course 2" tile "3" should show photo "placeholder_1.jpg"
    And course "Course 2" tile "4" should show no photo
    And course "Course 2" tile "5" should show no photo
    And course "Course 2" tile "6" should show photo "placeholder_3.jpg"

    And I log out tiles

    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | BL     | student        |
    And I log in as "student1"
    And I am on "Business Law" course homepage
    And I wait until the page is ready
    And I wait "2" seconds
    And course "Business Law" tile "1" should show photo "placeholder_1.jpg"
    And course "Business Law" tile "3" should show photo "placeholder_3.jpg"
    And course "Business Law" tile "7" should show photo "placeholder_1.jpg"

    And I am on "Course 2" course homepage
    And I wait until the page is ready
    And I wait "2" seconds
    And course "Course 2" tile "3" should show photo "placeholder_1.jpg"
    And course "Course 2" tile "6" should show photo "placeholder_3.jpg"

    And I log out tiles
