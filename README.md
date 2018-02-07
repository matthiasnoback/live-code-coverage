# Live code coverage

[![Build Status](https://travis-ci.org/matthiasnoback/live-code-coverage.svg?branch=master)](https://travis-ci.org/matthiasnoback/live-code-coverage)

This library should help you generate code coverage reports on a live server (it doesn't have to be a production server of course).

Install this library using:

```bash
composer require matthiasnoback/live-code-coverage
```

## Collecting code coverage data

In your front controller (e.g. `index.php`), add the following:

```php
<?php

use LiveCodeCoverage\LiveCodeCoverage;

$liveCodeCoverage = LiveCodeCoverage::bootstrap(
    __DIR__ . '/../var/coverage',
    __DIR__ . '/../phpunit.xml.dist'
);

// Run your web application now

$liveCodeCoverage->stopAndSave();
```

- The first argument is the directory where all the collected coverage data will be stored (`*.cov` files). This directory should already exist and be writable.
- The second argument is the path to a PHPUnit configuration file. Its `<filter>` section will be used to configure the code coverage whitelist. For example, this `phpunit.xml.dist` file might look something like this:

```xml
<?xml version="1.0" encoding="utf-8"?>
<phpunit>
    <filter>
        <whitelist>
            <directory suffix=".php">src</directory>
        </whitelist>
    </filter>
</phpunit>
```

Any configuration directive that's [available in PHPUnit](https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.whitelisting-files) works for this library too.

If you don't provide a PHPUnit configuration file, no filters will be applied, so you will get a coverage report for all the code in your project, including vendor and test code if applicable.

If your application is a legacy application which `exit()`s or `die()`s before execution reaches the end of your front controller, the bootstrap should be slightly different:

```php
$liveCodeCoverage = LiveCodeCoverage::bootstrap(
    // ...
);
$liveCodeCoverage->stopAndSaveOnExit();

// Run your web application now
```

## Generating code coverage reports (HTML, Clover, etc.)

To merge all the coverage data and generate a report for it, install Sebastian Bergmann's [`phpcov` tool](https://github.com/sebastianbergmann/phpcov). Run it like this (or in any other way you like):

```bash
phpcov merge --html=./coverage/html ./var/coverage
```

## Downsides

Please note that collecting code coverage data will make your application run much slower. Just see for yourself if that's acceptable. 
