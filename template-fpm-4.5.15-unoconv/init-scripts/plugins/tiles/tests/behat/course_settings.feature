@format_tiles @format_tiles_course_settings
Feature: Edit course settings page format tiles
  In order to set the course according to my teaching needs
  As a teacher
  I need to edit the course settings

#  Quick check that edit course setting page is still working when Tiles in use.
#  The below is copied from course/tests/behat/edit_settings.feature.

  @javascript
  Scenario: Edit course settings
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | summary | format |
      | Course 1 | C1 | <p>Course summary</p> | tiles |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following config values are set as admin:
      | courselistshortnames | 1 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I navigate to "Settings" in current page administration
    And I set the following fields to these values:
      | Course full name | Edited course fullname |
      | Course short name | Edited course shortname |
      | Course summary | Edited course summary |
    And I press "Save and display"
    And I am on site homepage
    Then I should not see "Course 1"
    And I should not see "C1"
    And I should see "Edited course fullname"
    And I should see "Edited course shortname"
