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

    private function __construct(CodeCoverage $codeCoverage, $storageDirectory, $coverage_id)
    {
        Assert::regex($coverage_id, '/^[\w\-]+$/');
        $this->codeCoverage = $codeCoverage;
        $this->coverageId = $coverage_id;
        Assert::directory($storageDirectory);
        Assert::writable($storageDirectory);
        $this->storageDirectory = $storageDirectory;
    }

    public static function bootstrap($storageDirectory, $phpunitConfigFilePath = null, $coverage_id = 'live-coverage')
    {
        if ($phpunitConfigFilePath !== null) {
            Assert::file($phpunitConfigFilePath);
            $codeCoverage = CodeCoverageFactory::createFromPhpUnitConfiguration($phpunitConfigFilePath);
        } else {
            $codeCoverage = CodeCoverageFactory::createDefault();
        }

        $liveCodeCoverage = new self($codeCoverage, $storageDirectory, $coverage_id);

        $liveCodeCoverage->start();
        register_shutdown_function([$liveCodeCoverage, 'stopAndSave']);
    }

    private function start()
    {
        $this->codeCoverage->start($this->coverageId);
    }

    public function stopAndSave()
    {
        $this->codeCoverage->stop();

        $cov = '<?php return unserialize(' . var_export(serialize($this->codeCoverage), true) . ');';
        file_put_contents($this->generateCovFileName(), $cov);
    }

    private function generateCovFileName()
    {
        $fileNameParts = [
            $this->coverageId,
            date('YmdHis'),
            uniqid('', true)
        ];

        return $this->storageDirectory . '/' . implode('-', $fileNameParts) . '.cov';
    }
}
