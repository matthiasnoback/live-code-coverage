<?php

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

final class LiveCodeCoverageTest extends TestCase
{
    private $coverageDirectory;

    protected function setUp()
    {
        $this->coverageDirectory = __DIR__ . '/coverage';

        $filesystem = new Filesystem();
        $filesystem->remove($this->coverageDirectory);
        $filesystem->mkdir([$this->coverageDirectory]);
    }

    /**
     * @test
     */
    public function it_generates_cov_files_with_serialized_CodeCoverage_objects()
    {
        $aProcess = new Process('php ' . __DIR__ . '/src/a.php');
        $bProcess = new Process('php ' . __DIR__ . '/src/b.php');

        $aProcess->run();
        $bProcess->run();

        $this->assertProcessSuccessful($aProcess);
        $this->assertProcessSuccessful($bProcess);

        /** @var Finder $finder */
        $finder = Finder::create()->name('*.cov')->in([$this->coverageDirectory]);
        self::assertCount(2, $finder);

        foreach ($finder as $covFile) {
            $filePath = (string)$covFile;
            $this->assertFileNameStartsWithExpectedPrefix($filePath);
            $this->assertIncludedFileReturnsCodeCoverageObject($filePath);
        }
    }

    /**
     * @param $filePath
     */
    private function assertFileNameStartsWithExpectedPrefix($filePath)
    {
        $this->assertRegExp('/^live-coverage/', basename($filePath));
    }

    /**
     * @param $filePath
     */
    private function assertIncludedFileReturnsCodeCoverageObject($filePath)
    {
        $cov = include $filePath;
        $this->assertInstanceOf(CodeCoverage::class, $cov);
    }

    private function assertProcessSuccessful(Process $process)
    {
        if (!$process->isSuccessful()) {
            $this->fail(
                sprintf(
                    "Process was not successful. Output:\n%s",
                    $process->getOutput()
                )
            );
        }
    }
}
