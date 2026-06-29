@format @format_tiles @format_tiles_colours @javascript

Feature: Check that tile colours are correct

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion | basecolour |
      | Course 1 | C1        | tiles  | 0             | 7           | 1                |  #1670CC   |
      | Course 2 | C2        | tiles  | 0             | 7           | 1                |  #009681   |
    And the following "activities" exist:
      | activity | name         | intro           | course | idnumber | section | visible | completion |
      | page     | Test page 1a | Test page intro | C1     | page1a   | 1       | 1       | 1          |
      | page     | Test page 1b | Test page intro | C1     | page1b   | 1       | 1       | 1          |
      | page     | Test page 2a | Test page intro | C2     | page2a   | 1       | 1       | 1          |
      | page     | Test page 2b | Test page intro | C2     | page2b   | 1       | 1       | 1          |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student1 | C2     | student        |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |
      | tilestyle              | 1        | format_tiles |

  @javascript
  Scenario: Student correctly sees blue base colour in course 1
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And format_tiles subtiles are on for course "Course 1"
    And I wait until the page is ready
    And I wait "3" seconds
    And Tile "2" has colour "22, 112, 204"

  Scenario: Student correctly sees green base colour in course 2
    When I log in as "student1"
    And I am on "Course 2" course homepage
    And format_tiles subtiles are on for course "Course 2"
    And I wait until the page is ready
    And I wait "3" seconds
    And Tile "2" has colour "0, 150, 129"
