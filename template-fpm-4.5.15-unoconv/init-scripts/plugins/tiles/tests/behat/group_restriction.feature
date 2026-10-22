@format @format_tiles @format_tiles_group_restriction @javascript
Feature: Teacher can restrict course modules to groups

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | studenta | Student   | a        | studenta@example.com |
      | studentb | Student   | b        | studentb@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion |
      | Course 1 | C1        | tiles  | 0             | 5           | 1                |
    And the following "activities" exist:
      | activity | name            | intro                      | course | idnumber | section | visible |
      | page     | Visible page    | Test page description      | C1     | page2    | 1       | 1       |
      | label    | Visible label   | I am an unrestricted label | C1     | label1   | 1       | 1       |
      | label    | Restricted label| I am a restricted label    | C1     | label2   | 1       | 1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | studenta | C1     | student        |
      | studentb | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |
      | enableavailability     | 1        | core         |

    And the following "groups" exist:
      | name    | course | idnumber |
      | Group A | C1     | A        |
      | Group B | C1     | B        |

  @javascript
  Scenario: Teacher can restrict cm by group, and student can only see it if they are in that group
    When format_tiles subtiles are on for course "Course 1"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I wait "1" seconds

    And I am on "Course 1" course homepage with editing mode on
    And I add a "page" activity to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name         | Restricted page |
      | Description  | Test   |
      | Page content | Test   |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I wait until the page is ready
    And I click on "Group" "button" in the "Add restriction..." "dialogue"
    And I wait until the page is ready
    And I set the field with xpath "//select[@name='id']" to "Group A"
    And I wait until the page is ready
    And I click on ".availability-item .availability-eye img" "css_element"
    And I wait until the page is ready
    And I press "Save and return to course"

    And I wait until the page is ready
    #  We used to say "And I follow "Collapse all"" but course index includes that too we have to use element ID now.
    And I follow "collapsesections"
    And I toggle expand or collapse section "1" for edit

    And I open "I am a restricted label" actions menu
    And I click on "Edit settings" "link" in the "I am a restricted label" activity
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I wait until the page is ready
    And I click on "Group" "button" in the "Add restriction..." "dialogue"
    And I wait until the page is ready
    And I set the field with xpath "//select[@name='id']" to "Group A"
    And I wait until the page is ready
    And I click on ".availability-item .availability-eye img" "css_element"
    And I wait until the page is ready
    And I press "Save and return to course"
    And I log out tiles

     # Log back in as student.
    And I log in as "studenta"
    And I am on "Course 1" course homepage

    And I click on tile "1"
    And I wait until the page is ready
    And I wait "1" seconds
    Then I should see "Visible page" in the "region-main" "region"
    And I should not see "Restricted page" in the "region-main" "region"
    And I should see "I am an unrestricted label" in the "region-main" "region"
    And I should not see "I am a restricted label" in the "region-main" "region"

    # Add student to group and log out/in again.

    And I click on close button for tile "1"
    And I log out tiles
    And the following "group members" exist:
      | user     | group |
      | studenta | A     |
    And I log in as "studenta"
    And I am on "Course 1" course homepage
    And I click on tile "1"
    And I wait until the page is ready
    And I wait "1" seconds
#    Now student can see the restricted page too
    Then I should see "Visible page" in the "region-main" "region"
    And I should see "I am an unrestricted label" in the "region-main" "region"
    And I should see "I am a restricted label" in the "region-main" "region"
    And I should see "Restricted page" in the "region-main" "region"
