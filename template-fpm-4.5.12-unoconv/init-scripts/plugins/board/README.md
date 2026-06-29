# Board
Copyright (C) 2023 [Brickfield Education Labs](https://www.brickfield.ie/)

## What is Board?
Encourage lively discussions and idea-sharing with Board, a post-it board activity for students!

This is an anonymous collaborative activity, where students can add their contributions of text, images, files, URLs, and videos, as a collection of visual notes.

Commenting on notes is enabled by default via capabilities, and rating notes can be enabled or disabled in each board instance's settings.

## Usage
Teachers can deploy ready-made, purpose-built board activities from templates in moments, or create custom ones as needed.

Group and single-user modes are also available. Optional completion criteria are provided also.

Students can share their ideas anonymously, and teachers can track these contributions via their board download options.

Students can include the following in their notes:
- Heading.
- Text with multiple formatting options: headings, lists, line breaks, bold and italics.
- Link.
- Image.
- Video (Youtube).
- Comment on any viewable notes.
- Receive notifications about new comments on notes written.
- Rate any viewable notes, if enabled.

## License
2023 Onward [Brickfield Education Labs](https://www.brickfield.ie)

## Version support
This plugin has been developed to work on Moodle releases 4.5, 5.0, 5.1, and 5.2.

## Funding credits
Initial funding for this plugin was provided by the National Institute for Digital Learning at Dublin City University under the SATLE fund from the National Forum. Subsequent funding has been received from Athlone Institute of Technology under the SATLE fund from the National Forum, and also from UCL.

Funding for templates, text formatting, and file attachments was also provided by the National Institute for Digital Learning at Dublin City University under the SATLE fund from the National Forum.

Funding for comment improvements, including notifications, icon, and icon count was provided by [Charité – Universitätsmedizin Berlin](https://www.charite.de).

## Development
This plugin has been developed and is maintained by Brickfield Education Labs.

If you wish to contribute funding to the ongoing development of features and / or
maintenance of the plugin - please contact [support@brickfield.ie](mailto:support@brickfield.ie).

This module uses code derived from ["jquery.editable.amd.js"](https://github.com/victorjonsson/jquery-editable/).
This code written by [Victor Jonsson](http://victorjonsson.se/) is licensed under [GNU GPLv2](http://www.gnu.org/licenses/gpl-2.0.html).

### Icon design
Many thanks to [Stuart Lamour](https://github.com/stuartlamour) for our board icon! Also thanks to [Luca Bosch](https://github.com/lucaboesch) for our updated 4.04 icon!

## Important Links
* [Code repository](https://github.com/brickfield/moodle-mod_board).
* [Plugin directory](https://moodle.org/plugins/mod_board).
* [Board user guide](https://docs.brickfield.ie/mod-board/).

## Installation
1. Unzip and copy the "board" folder into your Moodle's "mod/" folder.
2. Visit the admin page to install the plugin.

Further installation instructions can be found on the "[Installing plugins](http://docs.moodle.org/en/Installing_contributed_modules_or_plugins)" Moodle documentation page.

## Troubleshooting

If you have any support queries regarding the usage of Board, you may contact Brickfield as follows:

* Via the [Board github Issues page here](https://github.com/brickfield/moodle-mod_board/issues).
* Via the [Moodle Plugins Database page here](https://moodle.org/plugins/mod_board).
* Via the Brickfield support desk at 'support @ brickfield . ie'.

## Configurations

The global configurations for the Board module are:

* New column icon – Icon (Favicon v4.7) displayed on the new button for columns.
* New post icon – Icon (Favicon v4.7) displayed on the new button for posts.
* Media selection – Configure how the media selection for posts will be displayed as.
* Post maximum length – The maximum allowed content length. Anything over this length will be trimmed.
* Board refresh timer – Timeout in seconds between automatic board refreshes. If set to 0 or empty then the board will only refresh during board actions (add/update/etc)
* Column colours – The colours used at the top of each column. These are hex colours and should be placed once per line as 3 or 6 characters in length. If any of these values are not equal to a colour then the defaults will be used.
* Allow youtube – If activated, a button to add an embeded Youtube Video is supported.
* Embed width – Width to use for the iframe when embedding the board within the course. This should be a valid CSS value, e.g. px, rem, %, etc...
* Embed height – Height to use for the iframe when embedding the board within the course main page. This should be a valid CSS value, e.g. px, rem, %, etc...
* Accepted filetypes for background images – Select the filetypes for background images to be supported.
* Accepted filetypes for content images – Select the filetypes for content to be supported.
* Enabled single user modes – Allow/Disallow usage of certain single user modes. Does not affect already created boards.
