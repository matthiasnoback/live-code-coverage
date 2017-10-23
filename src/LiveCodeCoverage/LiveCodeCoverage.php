<?php

namespace LiveCodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class LiveCodeCoverage
{
    /**
     * @private
     */
    private $coverage_id = 'live-coverage';

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
        $this->codeCoverage = $codeCoverage;
        $this->coverage_id = $coverage_id;
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
        $this->codeCoverage->start($this->coverage_id);
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
            $this->coverage_id,
            date('YmdHis'),
            uniqid('', true)
        ];

        return $this->storageDirectory . '/' . implode('-', $fileNameParts) . '.cov';
    }
}
