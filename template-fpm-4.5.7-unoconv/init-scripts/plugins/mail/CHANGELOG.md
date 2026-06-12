# Changelog

## [2.15] - 2025-05-08

### Added

- Privacy provider implementation.

### Changed

- Refactored cache usage to improve performance and reduce memory usage.
- Links to profiles of deleted users are no longer displayed.

### Fixed

- Error when sending messages to a large number of users from the participants page.
- BCC recipients not displayed in the message list.
- Styling issue with deleted users in the message form in Moodle 5.0.
- Error caused by database references to an invalid user with ID 0.

## [2.14] - 2025-04-14

### Added

- Compatibility with Moodle 5.0.

### Fixed

- CORS error in the development server.

## [2.13] - 2025-03-13

### Fixed

- Message not found error when a site administrator opens a message in a hidden course.
- Unsupported text editors are now ignored.
- The user preference for the text editor is now taken into account.

### Changed

- The names of deleted users are now hidden for privacy reasons.

## [2.12] - 2025-01-22

### Changed

- Language strings for Basque, Catalan, Galician, and Spanish are now downloaded from AMOS.

## [2.11] - 2024-10-06

### Fixed

- Spelling error in string 'Not starred'.
- Alignment of icons inside buttons.
- Deprecation warnings in Moodle 4.5.

## [2.10] - 2024-08-06

### Fixed

- The group dropdown was filtered by the default grouping of the course.

## [2.9] - 2024-03-18

### Fixed

- Autosave was reverting changes in subject and recipients.

## [2.8] - 2024-03-15

### Fixed

- Upgrade to versions 2.6/2.7 failed in MySQL.
- The course filter was not kept to "All courses" when creating a message.
- Drafts were marked as changed just after opening them.

## [2.7] - 2024-03-14

### Changed

- Take into account the capability to access all groups when searching users.
- Require only that recipients are enrolled in the course when sending messages.
- Disable and lock web notification output by default.
- Hide disabled and locked notification outputs in the preferences dialog.

### Fixed

- Selected course was not updated when the course of a draft was changed.
- Compose button not working from site pages.
- Error modal hiding immediately after showing up.

## [2.6] - 2024-03-08

### Added

- New setting to configure the autosave interval in seconds.

### Fixed

- The selected course is no longer changed when creating a new messge.
- It was possible to change the course of a reply.
- Tiny editor autosave was enabled although it is redundant.

## [2.5] - 2024-03-04

### Fixed

- Disable interactions while loading new page to prevent double clicks.

## [2.4] - 2024-03-03

### Added

- New button in the message form to save the draft and go back to the list of messages.

### Changed

- The external function `create_label` can assign the created label to a specified list of messages.

### Fixed

- Excessive number of web service calls to autosave drafts.
- Web service requests are now performed sequentially to prevent potential race conditions.
- Superfluous padding in role and group selectors. 

## [2.3] - 2024-02-12

### Added

- A spinner is displayed while waiting for server responses.
- The number of unread messages of each course is now displayed in the course selector.

### Changed

- Numbers are now displayed with thousands separators.
- The text size in the Moodle app now follows the rest of the app.
- The contrast betweem emabled and disabled buttons has been increased.
- The language string about invalid recipients is now more explicit.
- The number of total messages is no longer displayed in small screens.
- The menu entry in the Moodle app is no longer restricted to the "more" tab.

### Fixed

- The toolbar was not always displayed at the bottom in the Moodle app.
- The course selector sometimes exceeded the screen boundaries in the Moodle app.
- The size of form controls and buttons was not always consistent in the Moodle App.
- Language strings for cache definitions were missing.

## [2.2] - 2024-02-03

### Fixed

- Displaying messages sent or received by deleted users.

## [2.1] - 2024-02-02

### Fixed

- Content of references.

## [2.0] - 2024-01-29

### Added

- New responsive user interface.
- Support for the Moodle app.
- Auto-save of message dratfs.
- Instant search results displayed while user is typing in the search box.
- Pop-up notifications when sending, deleting and restoring messages.
- Preference: Enable or disable email and mobile push notifications.
- Setting: Maximum number of recipients per message.
- Setting: Maximum number of results displayed in the user search.
- Setting: Hide starred, sent, drafts or trash trays.
- Setting: Display course trays or display only course trays with unread messages.
- Setting: Use full name for course trays.
- Setting: Show selector to filter trays and messages by course.
- Setting: Hide or use full name for course badges.
- Setting: Limit the length of course badges.
- Setting: Enable or disabled instant search.
- Setting: Maximum number of recent messages included in instant search.
- Setting: Display a link to the curret course at the top of the page.
- New test data generator script (for developers).

### Changed

- E-mail notifications now include all the content of the message.
- Forwarded messages are embedded in the new message instead of being included as a reference.
- New way of filtering messages by course (course trays are still available but disabled by default).
- Redesigned web service functions that covers all the functionality of the plugin.

### Fixed

- Creating and restoring course backups with mail data.
- Messages from courses not visible by the user are no longer displayed.
