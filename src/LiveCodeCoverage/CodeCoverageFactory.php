<?php

namespace LiveCodeCoverage;

use PHPUnit\TextUI\XmlConfiguration\Configuration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

final class CodeCoverageFactory
{
    /**
     * @param string $phpunitFilePath
     * @return CodeCoverage
     */
    public static function createFromPhpUnitConfiguration($phpunitFilePath)
    {
        $codeCoverage = self::createDefault();

        $loader = new Loader();
        self::configure($codeCoverage, $loader->load($phpunitFilePath));

        return $codeCoverage;
    }

    private static function configure(CodeCoverage $codeCoverage, Configuration $configuration)
    {
        $codeCoverageFilter = $codeCoverage->filter();
        $codeCoverageConfiguration = $configuration->codeCoverage();

        // The following code is copied from PHPUnit\TextUI\TestRunner
        if ($codeCoverageConfiguration->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            if ($codeCoverageConfiguration->includeUncoveredFiles()) {
                $codeCoverage->includeUncoveredFiles();
            } else {
                $codeCoverage->excludeUncoveredFiles();
            }

            if ($codeCoverageConfiguration->processUncoveredFiles()) {
                $codeCoverage->processUncoveredFiles();
            } else {
                $codeCoverage->doNotProcessUncoveredFiles();
            }
        }

        foreach ($codeCoverageConfiguration->directories() as $directory) {
            $codeCoverageFilter->includeDirectory(
                $directory->path(),
                $directory->suffix(),
                $directory->prefix()
            );
        }

        foreach ($codeCoverageConfiguration->files() as $file) {
            $codeCoverageFilter->includeFile($file->path());
        }

        foreach ($codeCoverageConfiguration->excludeDirectories() as $directory) {
            $codeCoverageFilter->excludeDirectory(
                $directory->path(),
                $directory->suffix(),
                $directory->prefix()
            );
        }

        foreach ($codeCoverageConfiguration->excludeFiles() as $file) {
            $codeCoverageFilter->excludeFile($file->path());
        }

    }

    /**
     * @return CodeCoverage
     */
    public static function createDefault()
    {
        $filter = new Filter();
        $driverSelector = new Selector();
        $driver = $driverSelector->forLineCoverage($filter);
        return new CodeCoverage($driver, $filter);
    }
}
