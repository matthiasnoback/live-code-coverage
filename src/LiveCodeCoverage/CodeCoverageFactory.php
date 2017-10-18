<?php
declare(strict_types=1);

namespace LiveCodeCoverage;

use PHPUnit\Util\Configuration;
use PHPUnit\Util\Filter;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class CodeCoverageFactory
{
    public static function createFromPhpUnitConfiguration($phpunitFilePath): CodeCoverage
    {
        $codeCoverage = new CodeCoverage();

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
}
