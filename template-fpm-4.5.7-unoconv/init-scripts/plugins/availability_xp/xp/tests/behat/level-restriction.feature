@availability @availability_xp
Feature: Testing that activity access can be based on levels

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | t1       | Teacher   | One      |
      | s1       | Student   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | t1       | c1     | editingteacher |
      | s1       | c1     | student      |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "activities" exist:
      | activity | course | name   | idnumber  |
      | page     | c1     | Page 1 | PAGE1     |
      | page     | c1     | Page 2 | PAGE2     |
      | page     | c1     | Page 3 | PAGE3     |
      | page     | c1     | Page 4 | PAGE4     |
    And the following "availability_xp > restrictions" exist:
      | activity | level | mode |
      | PAGE1    | 2     | eq   |
      | PAGE2    | 2     | gte  |
      | PAGE3    | 5     | eq   |
      | PAGE4    | 5     | gte  |
    And I am on the "c1" "block_xp > rules" page logged in as t1
    And I delete all XP event rules

  @javascript
  Scenario: Students access restriction based on levels
    Given I am on the "c1" "course" page logged in as "s1"
    And I should not see "Page 1"
    And I should not see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | -     |
    And I follow "Edit" for "Student One" in the XP report
    And I set the field "Total" in the "Edit Student One" "dialogue" to "120"
    And I click on "Save changes" "button" in the "Edit Student One" "dialogue"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | 2     |

    When I am on the "c1" "course" page logged in as "s1"
    Then I should see "Page 1"
    And I should see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Edit" for "Student One" in the XP report
    And I set the field "Total" in the "Edit Student One" "dialogue" to "1000"
    And I click on "Save changes" "button" in the "Edit Student One" "dialogue"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | 5     |

    And I am on the "c1" "course" page logged in as "s1"
    And I should not see "Page 1"
    And I should see "Page 2"
    And I should see "Page 3"
    And I should see "Page 4"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Edit" for "Student One" in the XP report
    And I set the field "Total" in the "Edit Student One" "dialogue" to "5000"
    And I click on "Save changes" "button" in the "Edit Student One" "dialogue"

    And I am on the "c1" "course" page logged in as "s1"
    And I should not see "Page 1"
    And I should see "Page 2"
    And I should not see "Page 3"
    And I should see "Page 4"
