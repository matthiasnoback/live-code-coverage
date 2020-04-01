<?php

namespace LiveCodeCoverage;

use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Loader;
use SebastianBergmann\CodeCoverage\CodeCoverage;

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
        $filterConfiguration = $configuration->filter();

        // The following code is copied from PHPUnit\TextUI\TestRunner

        if ($filterConfiguration->hasNonEmptyWhitelist()) {
            $codeCoverage->setAddUncoveredFilesFromWhitelist(
                $filterConfiguration->addUncoveredFilesFromWhitelist()
            );

            $codeCoverage->setProcessUncoveredFilesFromWhitelist(
                $filterConfiguration->processUncoveredFilesFromWhitelist()
            );
        }

        foreach ($filterConfiguration->directories() as $directory) {
            $codeCoverageFilter->addDirectoryToWhitelist(
                $directory->path(),
                $directory->suffix(),
                $directory->prefix()
            );
        }

        foreach ($filterConfiguration->files() as $file) {
            $codeCoverageFilter->addFileToWhitelist($file->path());
        }

        foreach ($filterConfiguration->excludeDirectories() as $directory) {
            $codeCoverageFilter->removeDirectoryFromWhitelist(
                $directory->path(),
                $directory->suffix(),
                $directory->prefix()
            );
        }

        foreach ($filterConfiguration->excludeFiles() as $file) {
            $codeCoverageFilter->removeFileFromWhitelist($file->path());
        }

    }

    /**
     * @return CodeCoverage
     */
    public static function createDefault()
    {
        return new CodeCoverage();
    }
}
