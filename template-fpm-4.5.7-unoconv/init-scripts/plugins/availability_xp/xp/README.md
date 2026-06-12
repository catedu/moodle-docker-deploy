Level Up XP Availability (availability_xp)
==========================================

Moodle plugin to limit the access to course content according to the user level of experience.

Features
--------

- Restrict access to users strictly at level x.
- Restrict access to users with a level greater or equal than x.

Requirements
------------

- Moodle 3.1 or greater.
- Block [Level Up XP](https://github.com/FMCorz/moodle-block_xp)

_Running behats tests requires XP 18.0 or greater._

How to use
----------

1. Install the block [Level Up XP](https://github.com/FMCorz/moodle-block_xp)
2. Install this plugin
3. Add the block 'Level Up XP' to a course
4. Find the new 'Level' restriction under 'Restrict access' in activity/section settings

Installation
------------

### Zip upload

If you have configured Moodle to allow plugin installation from the user interface, and you received a zip of the plugin, follow the following steps. If not, refer to the manual process.

1. Visit the _Install plugins_ admin page (Site administration > Plugins > Install plugins)
2. Drag & drop the plugin in the _Zip package_ area
3. Click _Install plugin from the ZIP file_ and follow the process

That's it!

### Manual process

1. Place the content of this plugin in the folder `availability/condition/xp`.
2. Visit your admin's _Notifications_ page (Site administration > Notifications)
3. Follow the upgrade process

That's it!

License
-------

Licensed under the [GNU GPL License](http://www.gnu.org/copyleft/gpl.html)
