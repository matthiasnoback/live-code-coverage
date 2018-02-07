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

    private function start()
    {
        $this->codeCoverage->start($this->coverageId);
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
