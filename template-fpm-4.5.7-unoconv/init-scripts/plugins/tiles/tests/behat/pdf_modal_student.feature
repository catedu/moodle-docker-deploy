@format @format_tiles @format_tiles_mod_modal @format_tiles_pdf_modal_student  @javascript @_file_upload
Feature: PDFs can be set to open in modal windows
  In order to improve UX
  As a student
  I need to be able to view these modals

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion |
      | Course 1 | C1-pdf    | tiles  | 0             | 5           | 1                |
    And the following "activities" exist:
      | activity | name           | intro                 | course | idnumber | section | visible | completion | defaultfilename                             | uploaded |
      | page     | Test page name | Test page description | C1-pdf | page1    | 1       | 1       | 0          |                                             | 0        |
      | resource | Test PDF       | File description      | C1-pdf | pdf1     | 1       | 1       | 1          | course/format/tiles/tests/fixtures/test.pdf | 1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1-pdf | student        |
      | teacher1 | C1-pdf | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_tiles |
      | modalresources         | pdf,html | format_tiles |
      | assumedatastoreconsent | 1        | format_tiles |
      | reopenlastsection      | 0        | format_tiles |
      | usejavascriptnav       | 1        | format_tiles |

    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    Then I should see "Test PDF"
    And I log out tiles

    # First with subtiles off as student
  @javascript
  Scenario: Open modal PDF as student with subtiles off
    When format_tiles subtiles are off for course "Course 1"
    And I wait "2" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "Test PDF"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should be visible

    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And "Close" "button" should exist in the "Test PDF" "dialogue"
    And I click on "Close" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should not be visible
    And I click on close button for tile "1"
    And I log out tiles

    # First with subtiles off as teacher
  @javascript
  Scenario: Open modal PDF as student with subtiles off
    When format_tiles subtiles are off for course "Course 1"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "Test PDF"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should be visible

    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And "Close" "button" should exist in the "Test PDF" "dialogue"
    And I click on "Close" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should not be visible
    And I click on close button for tile "1"
    And I log out tiles

      # Now the same again with subtiles on
  @javascript
  Scenario: Open modal PDF as student with subtiles on
    When format_tiles subtiles are on for course "Course 1"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I wait until the page is ready
    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "Test PDF"
    And I wait until the page is ready
    And I wait "1" seconds
    And "Test PDF" "dialogue" should be visible
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And "Close" "button" should exist in the "Test PDF" "dialogue"
    And I click on "Close" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should not be visible
    And I click on close button for tile "1"
    And Tiles JS config element exists on page
    And I log out tiles

  # Now the same again for student with subtiles on
  @javascript
  Scenario: Open modal PDF as student with subtiles on
    When format_tiles subtiles are on for course "Course 1"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

    And I click on tile "1"
    And I wait until the page is ready
    And I click format tiles activity "Test PDF"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should be visible
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And format_tiles progress for "resource" called "Test PDF" in "Course 1" is "0" in the database
    And "Close" "button" should exist in the "Test PDF" "dialogue"
    And I click on "Close" "button" in the "Test PDF" "dialogue"
    And I wait until the page is ready
    And "Test PDF" "dialogue" should not be visible
    And I click on close button for tile "1"
    And I log out tiles
