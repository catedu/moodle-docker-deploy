@format @format_tiles @format_tiles_block_calendar_month
Feature: Enable an example block in a course (calendar is convenient) and check that blocks drawer is visible to student user in tiles format

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
      | student1 | Student | 1 | student1@example.com | S1 |
    And the following "courses" exist:
      | fullname                 | shortname | category | format |
      | Tiles checking blocks    | C1        | 0        | tiles  |
      | Topics checking blocks   | C2        | 0        | topics  |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | teacher1 | C2 | editingteacher |
      | student1 | C2 | student |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |
      | tilestyle              | 1        | format_tiles |
      | usecourseindex         | 1        | format_tiles |

  @javascript
  Scenario: Add the block to a topics course
    Given I log in as "teacher1"
    And I am on "Topics checking blocks" course homepage with editing mode on
    When I add the "Calendar" block
    Then "Calendar" "block" should exist
    And I turn editing mode off
    And I am on "Topics checking blocks" course homepage
    And I wait "1" seconds
    Then "Calendar" "block" should exist
    And I log out
    And I log in as "student1"
    And I am on "Topics checking blocks" course homepage
    And I wait "1" seconds
    Then "Calendar" "block" should exist

  @javascript
  Scenario: Add the block to a tiles course
    Given I log in as "teacher1"
    And I am on "Tiles checking blocks" course homepage with editing mode on
    When I add the "Calendar" block
    Then "Calendar" "block" should exist
    And I turn editing mode off
    And I am on "Tiles checking blocks" course homepage
    And I wait "1" seconds
    Then "Calendar" "block" should exist
    And I log out
    And I log in as "student1"
    And I am on "Tiles checking blocks" course homepage
    And I wait "1" seconds
    Then "Calendar" "block" should exist
