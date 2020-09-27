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

$shutDownCodeCoverage = LiveCodeCoverage::bootstrap(
    (bool)getenv('CODE_COVERAGE_ENABLED'),
    __DIR__ . '/../var/coverage',
    __DIR__ . '/../phpunit.xml.dist'
);

// Run your web application now...

// This will save and store collected coverage data:
$shutDownCodeCoverage();
```

- The first argument passed to `LiveCodeCoverage::bootstrap()` is a boolean that will be used to determine if code coverage is enabled at all. The example shows how you can use an environment variable for that.
- The second argument is the directory where all the collected coverage data will be stored (`*.cov` files). If this directory doesn't exist yet, it will be created.
- The third argument is the path to a PHPUnit configuration file. Its `<filter>` section will be used to configure the code coverage whitelist. For example, this `phpunit.xml.dist` file might look something like this:

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

Most configuration directives that are [available in PHPUnit](https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.whitelisting-files) work for this library too.
If you notice that something doesn't work, please submit an issue.

If you don't provide a PHPUnit configuration file, no filters will be applied, so you will get a coverage report for all the code in your project, including vendor and test code if applicable.

If your application is a legacy application which `exit()`s or `die()`s before execution reaches the end of your front controller, the bootstrap should be slightly different:

```php
$shutDownCodeCoverage = LiveCodeCoverage::bootstrap(
    // ...
);
register_shutdown_function($shutDownCodeCoverage);

// Run your web application now...
```

## Generating code coverage reports (HTML, Clover, etc.)

To merge all the coverage data and generate a report for it, install Sebastian Bergmann's [`phpcov` tool](https://github.com/sebastianbergmann/phpcov). Run it like this (or in any other way you like):

```bash
phpcov merge --html=./coverage/html ./var/coverage
```

## Downsides

Please note that collecting code coverage data will make your application run much slower. Just see for yourself if that's acceptable. 
