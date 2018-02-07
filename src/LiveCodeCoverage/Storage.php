<?php

namespace LiveCodeCoverage;

use DirectoryIterator;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Webmozart\Assert\Assert;

final class Storage
{
    /**
     * @param CodeCoverage $coverage
     * @param string $storageDirectory
     * @param $name
     * @return void
     * @throws \RuntimeException
     */
    public static function storeCodeCoverage(CodeCoverage $coverage, $storageDirectory, $name)
    {
        Assert::string($storageDirectory);

        if (!is_dir($storageDirectory)) {
            if (!mkdir($storageDirectory, 0777, true) && !is_dir($storageDirectory)) {
                throw new \RuntimeException(sprintf('Could not create directory "%s"', $storageDirectory));
            }
        }

        $cov = '<?php return unserialize(' . var_export(serialize($coverage), true) . ');';
        $filePath = $storageDirectory . '/' . $name . '.cov';
        file_put_contents($filePath, $cov);
    }

    /**
     * @param string $storageDirectory
     * @return CodeCoverage
     */
    public static function loadFromDirectory($storageDirectory)
    {
        Assert::string($storageDirectory);

        $coverage = new CodeCoverage();

        if (!is_dir($storageDirectory)) {
            return $coverage;
        }

        foreach (new DirectoryIterator($storageDirectory) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $partialCodeCoverage = include $file->getPathname();
            Assert::isInstanceOf($partialCodeCoverage, CodeCoverage::class);

            $coverage->merge($partialCodeCoverage);
        }

        return $coverage;
    }
}
