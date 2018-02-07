<?php

namespace LiveCodeCoverage;

use Webmozart\Assert\Assert;

final class RemoteCodeCoverage
{
    const COVERAGE_ID_KEY = 'coverage_id';
    const COLLECT_CODE_COVERAGE_KEY = 'collect_code_coverage';
    const COVERAGE_GROUP_KEY = 'coverage_group';
    const EXPORT_CODE_COVERAGE_KEY = 'export_code_coverage';

    /**
     * Enable remote code coverage.
     *
     * @param bool $coverageEnabled Whether or not code coverage should be enabled
     * @param string $storageDirectory Where to store the generated coverage data files
     * @param string $phpunitConfigFilePath The path to the PHPUnit XML file containing the coverage filter configuration
     * @return callable Call this value at the end of the request life cycle.
     */
    public static function bootstrap($coverageEnabled, $storageDirectory, $phpunitConfigFilePath = null)
    {
        Assert::boolean($coverageEnabled);
        if (!$coverageEnabled) {
            return function () {
                // do nothing - code coverage is not enabled
            };
        }

        $coverageGroup = isset($_GET[self::COVERAGE_GROUP_KEY]) ? $_GET[self::COVERAGE_GROUP_KEY] :
            (isset($_COOKIE[self::COVERAGE_GROUP_KEY]) ? $_COOKIE[self::COVERAGE_GROUP_KEY] : null);

        $storageDirectory .= ($coverageGroup ? '/' . $coverageGroup : '');

        if (isset($_GET[self::EXPORT_CODE_COVERAGE_KEY])) {
            header('Content-Type: text/plain');
            echo self::exportCoverageData($storageDirectory);
            exit;
        }

        $coverageId = isset($_GET[self::COVERAGE_ID_KEY]) ? $_GET[self::COVERAGE_ID_KEY] :
            (isset($_COOKIE[self::COVERAGE_ID_KEY]) ? $_COOKIE[self::COVERAGE_ID_KEY] : 'live-coverage');

        return LiveCodeCoverage::bootstrap(
            isset($_COOKIE[self::COLLECT_CODE_COVERAGE_KEY]) && (bool)$_COOKIE[self::COLLECT_CODE_COVERAGE_KEY],
            $storageDirectory,
            $phpunitConfigFilePath,
            $coverageId
        );
    }

    /**
     * Get previously collected coverage data (combines all coverage data stored in the given directory, merges and serializes it).
     *
     * @param string $coverageDirectory
     * @return string
     */
    public static function exportCoverageData($coverageDirectory)
    {
        $codeCoverage = Storage::loadFromDirectory($coverageDirectory);

        return serialize($codeCoverage);
    }
}
