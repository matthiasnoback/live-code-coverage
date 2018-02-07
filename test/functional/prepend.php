<?php

use LiveCodeCoverage\LiveCodeCoverage;

$shutDownCodeCoverage = LiveCodeCoverage::bootstrap(
    true,
    __DIR__ . '/coverage',
    __DIR__ . '/phpunit.xml.dist'
);
register_shutdown_function($shutDownCodeCoverage);
