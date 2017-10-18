<?php
declare(strict_types=1);

namespace LiveCodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class LiveCodeCoverage
{
    /**
     * @var CodeCoverage
     */
    private $codeCoverage;
    private $fileNamePrefix;
    private $reportDirectory;

    private function __construct(CodeCoverage $codeCoverage, $reportDirectory, $fileNamePrefix)
    {
        $this->codeCoverage = $codeCoverage;

        Assert::directory($reportDirectory);
        Assert::writable($reportDirectory);
        $this->reportDirectory = $reportDirectory;

        $this->fileNamePrefix = $fileNamePrefix;
    }

    public static function bootstrap($projectRootDir, $reportDirectory, $fileNamePrefix = 'live-coverage')
    {
        $codeCoverage = CodeCoverageFactory::createFromPhpUnitConfiguration($projectRootDir . '/phpunit.xml.dist');

        $liveCodeCoverage = new self($codeCoverage, $reportDirectory, $fileNamePrefix);

        $liveCodeCoverage->start();
        register_shutdown_function([$liveCodeCoverage, 'stopAndSave']);
    }

    private function start()
    {
        $this->codeCoverage->start('Live coverage');
    }

    public function stopAndSave()
    {
        $this->codeCoverage->stop();

        $cov = '<?php return unserialize(' . var_export(serialize($this->codeCoverage), true) . ');';
        file_put_contents($this->generateCovFileName(), $cov);
    }

    /**
     * @return string
     */
    private function generateCovFileName()
    {
        $fileNameParts = [
            $this->fileNamePrefix,
            date('YmdHis'),
            uniqid('', false)
        ];

        return $this->reportDirectory . '/' . implode('-', $fileNameParts) . '.cov';
    }
}
