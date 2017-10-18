<?php

namespace LiveCodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class LiveCodeCoverage
{
    /**
     * @private
     */
    const COVERAGE_ID = 'live-coverage';

    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    /**
     * @var string
     */
    private $storageDirectory;

    private function __construct(CodeCoverage $codeCoverage, $storageDirectory)
    {
        $this->codeCoverage = $codeCoverage;

        Assert::directory($storageDirectory);
        Assert::writable($storageDirectory);
        $this->storageDirectory = $storageDirectory;
    }

    public static function bootstrap($storageDirectory, $phpunitConfigFilePath = null)
    {
        if ($phpunitConfigFilePath !== null) {
            Assert::file($phpunitConfigFilePath);
            $codeCoverage = CodeCoverageFactory::createFromPhpUnitConfiguration($phpunitConfigFilePath);
        } else {
            $codeCoverage = CodeCoverageFactory::createDefault();
        }

        $liveCodeCoverage = new self($codeCoverage, $storageDirectory);

        $liveCodeCoverage->start();
        register_shutdown_function([$liveCodeCoverage, 'stopAndSave']);
    }

    private function start()
    {
        $this->codeCoverage->start(self::COVERAGE_ID);
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
            self::COVERAGE_ID,
            date('YmdHis'),
            uniqid('', true)
        ];

        return $this->storageDirectory . '/' . implode('-', $fileNameParts) . '.cov';
    }
}
