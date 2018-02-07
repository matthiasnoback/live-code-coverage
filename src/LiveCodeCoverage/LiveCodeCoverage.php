<?php

namespace LiveCodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class LiveCodeCoverage
{
    /**
     * @private
     */
    private $coverageId;

    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    /**
     * @var string
     */
    private $storageDirectory;

    private function __construct(CodeCoverage $codeCoverage, $storageDirectory, $coverageId)
    {
        $this->codeCoverage = $codeCoverage;
        $this->coverageId = $coverageId;
        $this->storageDirectory = $storageDirectory;
    }

    /**
     * @param callable $coverageEnabled
     * @param string $storageDirectory
     * @param string|null $phpunitConfigFilePath
     * @param string $coverageId
     * @return callable
     */
    public static function bootstrap($coverageEnabled, $storageDirectory, $phpunitConfigFilePath = null, $coverageId = 'live-coverage')
    {
        Assert::isCallable($coverageEnabled);
        if (!$coverageEnabled()) {
            return function () {
                // do nothing - code coverage is not enabled
            };
        }

        if ($phpunitConfigFilePath !== null) {
            Assert::file($phpunitConfigFilePath);
            $codeCoverage = CodeCoverageFactory::createFromPhpUnitConfiguration($phpunitConfigFilePath);
        } else {
            $codeCoverage = CodeCoverageFactory::createDefault();
        }

        $liveCodeCoverage = new self($codeCoverage, $storageDirectory, $coverageId);

        $liveCodeCoverage->start();

        return [$liveCodeCoverage, 'stopAndSave'];
    }

    /**
     * @param callable $coverageEnabled
     * @param string $storageDirectory
     * @param null $phpunitConfigFilePath
     * @return callable
     */
    public static function bootstrapRemoteCoverage($coverageEnabled, $storageDirectory, $phpunitConfigFilePath = null)
    {
        Assert::isCallable($coverageEnabled);
        if (!$coverageEnabled()) {
            return function () {
                // do nothing - code coverage is not enabled
            };
        }

        $coverageGroup = isset($_GET['coverage_group']) ? $_GET['coverage_group'] :
            (isset($_COOKIE['coverage_group']) ? $_COOKIE['coverage_group'] : null);

        $storageDirectory .= ($coverageGroup ? '/' . $coverageGroup : '');

        if (isset($_GET['export_code_coverage'])) {
            self::exportCoverageData($storageDirectory);
            exit;
        }

        $coverageId = isset($_GET['coverage_id']) ? $_GET['coverage_id'] :
            (isset($_COOKIE['coverage_id']) ? $_COOKIE['coverage_id'] : 'live-coverage');

        return self::bootstrap($coverageEnabled, $storageDirectory, $phpunitConfigFilePath, $coverageId);
    }

    private function start()
    {
        $this->codeCoverage->start($this->coverageId);
    }

    /**
     * @param $coverageDirectory
     * @return void
     */
    public static function exportCoverageData($coverageDirectory)
    {
        $codeCoverage = Storage::loadFromDirectory($coverageDirectory);

        header('Content-Type: text/plain');
        echo serialize($codeCoverage);
    }

    public function stopAndSave()
    {
        $this->codeCoverage->stop();

        Storage::storeCodeCoverage($this->codeCoverage, $this->storageDirectory, $this->covFileName());
    }

    private function covFileName()
    {
        return uniqid(date('YmdHis'), true);
    }
}
