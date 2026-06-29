# Contributing

## Svelte

The client side components are written using [Svelte](https://svelte.dev).

### Developement server

1. Set this setting in `config.php`:
   ```
   $CFG->local_mail_devserver = 'http://localhost:5173';
   ```

2. Start the developement server with:
   ```
   cd local/mail/svelte
   npm ci
   npm run dev
   ```

### Code checker and linter

```
cd local/mail/svelte
npm ci
npm run check
npm run lint
```

### Production build

```
cd local/mail/svelte
npm ci
npm run build
```

The production code is stored in `local/mail/svelte/build`.

## PHPUnit

See: https://moodledev.io/general/development/tools/phpunit

Initialize test environment:
```
php admin/tool/phpunit/cli/init.php
php admin/tool/phpunit/cli/util.php --buildcomponentconfigs
```

Run unit tests:
```
vendor/bin/phpunit -c local/mail
```

Run unit tests and generate code coverage report:
```
php -dpcov.enabled=1 vendor/bin/phpunit -c local/mail \
    --coverage-html=local/mail/coverage
```

## PHP CodeSniffer

See: https://moodledev.io/general/development/tools/phpcs

Install latest Moodle rules:
```
composer global config minimum-stability dev
composer global require moodlehq/moodle-cs
```

Check code:
```
cd local/mail
phpcs .
```

## Test data generator

This script generates random fake messages amongst users for testing.

WARNING: The script deletes all existing mail data.

```
php local/mail/cli/generate.php
```

## Copyright and licensing

Copyright and licensing is done following [REUSE](https://reuse.software/) recommendations.

See the `version.php` file for an example.

## Changelog file

Changelog file uses the format from [Keep a Changelog](https://keepachangelog.com).

## Language strings

Translations of the plugin are maintained in AMOS:
https://lang.moodle.org/
