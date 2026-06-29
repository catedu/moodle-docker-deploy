@mod @mod_board
Feature: Testing overview integration in board activity
  In order to summarize the board activity
  As a user
  I need to be able to see the board activity overview

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
      | student1 | Student   | 1        | student1@asd.com |
      | student2 | Student   | 2        | student2@asd.com |
      | student3 | Student   | 3        | student3@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "activity" exists:
      | activity       | board           |
      | course         | C1              |
      | name           | Test board name |
      | groupmode      | 0               |
      | singleusermode | 0               |
      | addrating      | 3               |

  @javascript
  Scenario: The board activity overview report should generate log events
    Given the site is running Moodle version 5.0 or higher
    And I am on the "Course 1" "course > activities > board" page logged in as "teacher1"
    When I am on the "Course 1" "course" page logged in as "teacher1"
    And I navigate to "Reports" in current page administration
    And I click on "Logs" "link"
    And I click on "Get these logs" "button"
    Then I should see "Course activities overview page viewed"
    And I should see "viewed the instance list for the module 'board'"

  @javascript
  Scenario: The board activity index redirect to the activities overview
    Given the site is running Moodle version 5.0 or higher
    When I am on the "C1" "course > activities > board" page logged in as "admin"
    Then I should see "Name" in the "board_overview_collapsible" "region"
    And I should see "Actions" in the "board_overview_collapsible" "region"
    And I should see "Test board name"
