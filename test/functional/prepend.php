<?php

use LiveCodeCoverage\LiveCodeCoverage;

$liveCodeCoverage = LiveCodeCoverage::bootstrap(__DIR__ . '/coverage', __DIR__ . '/phpunit.xml.dist');
$liveCodeCoverage->stopAndSaveOnExit();
