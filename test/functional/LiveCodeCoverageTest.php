<?php

use Symfony\Component\Finder\Finder;

final class LiveCodeCoverageTest
{
    public function deleteCovFiles()
    {
        foreach (Finder::create()->in('')->name('*.cov') as $covFile) {
            @unlink($covFile);
        }
    }
    /**
     * @test
     */
    public function it_generates_cov_files_for_code_coverage()
    {

    }
}
