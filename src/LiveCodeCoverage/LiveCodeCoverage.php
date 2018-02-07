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

    public static function bootstrap($storageDirectory, $phpunitConfigFilePath = null, $coverageId = 'live-coverage')
    {
        if ($phpunitConfigFilePath !== null) {
            Assert::file($phpunitConfigFilePath);
            $codeCoverage = CodeCoverageFactory::createFromPhpUnitConfiguration($phpunitConfigFilePath);
        } else {
            $codeCoverage = CodeCoverageFactory::createDefault();
        }

        $liveCodeCoverage = new self($codeCoverage, $storageDirectory, $coverageId);

        $liveCodeCoverage->start();

        return $liveCodeCoverage;
    }

    public static function bootstrapRemoteCoverage($storageDirectory, $phpunitConfigFilePath = null)
    {
        $coverageGroup = isset($_GET['coverage_group']) ? $_GET['coverage_group'] :
            (isset($_COOKIE['coverage_group']) ? $_COOKIE['coverage_group'] : null);

        $coverageId = isset($_GET['coverage_id']) ? $_GET['coverage_id'] :
            (isset($_COOKIE['coverage_id']) ? $_COOKIE['coverage_id'] : 'live-coverage');

        $storageDirectory .= ($coverageGroup ? '/' . $coverageGroup : '');

        if (isset($_GET['export_code_coverage'])) {
            self::exportCoverageData($storageDirectory);
            exit;
        }

        return self::bootstrap($storageDirectory, $phpunitConfigFilePath, $coverageId);
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

    public function stopAndSaveOnExit()
    {
        register_shutdown_function([$this, 'stopAndSave']);
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
