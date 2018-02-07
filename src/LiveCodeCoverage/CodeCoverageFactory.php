<?php

namespace LiveCodeCoverage;

use PHPUnit\Util\Configuration;
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

        // Accomodate for PHPUnit 5.7
        if (!class_exists('PHPUnit\Util\Configuration')) {
            class_alias('PHPUnit_Util_Configuration', 'PHPUnit\Util\Configuration');
        }

        self::configure($codeCoverage, Configuration::getInstance($phpunitFilePath));

        return $codeCoverage;
    }

    private static function configure(CodeCoverage $codeCoverage, Configuration $configuration)
    {
        $filter = $codeCoverage->filter();
        $filterConfiguration = $configuration->getFilterConfiguration();

        // The following code is copied from PHPUnit\TextUI\TestRunner

        $codeCoverage->setAddUncoveredFilesFromWhitelist(
            $filterConfiguration['whitelist']['addUncoveredFilesFromWhitelist']
        );

        $codeCoverage->setProcessUncoveredFilesFromWhitelist(
            $filterConfiguration['whitelist']['processUncoveredFilesFromWhitelist']
        );

        foreach ($filterConfiguration['whitelist']['include']['directory'] as $dir) {
            $filter->addDirectoryToWhitelist(
                $dir['path'],
                $dir['suffix'],
                $dir['prefix']
            );
        }

        foreach ($filterConfiguration['whitelist']['include']['file'] as $file) {
            $filter->addFileToWhitelist($file);
        }

        foreach ($filterConfiguration['whitelist']['exclude']['directory'] as $dir) {
            $filter->removeDirectoryFromWhitelist(
                $dir['path'],
                $dir['suffix'],
                $dir['prefix']
            );
        }

        foreach ($filterConfiguration['whitelist']['exclude']['file'] as $file) {
            $filter->removeFileFromWhitelist($file);
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
