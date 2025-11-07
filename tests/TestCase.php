<?php declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected string $temporaryDirectory;

    /**
     * Set up a temporary directory.
     *
     * @return void
     */
    protected function setUpTemporaryDirectory(): void
    {
        $this->temporaryDirectory = \sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'readme-lint';

        if (!\file_exists($this->temporaryDirectory)) {
            \mkdir($this->temporaryDirectory);
        }
    }

    /**
     * Remove the directory and files in it.
     *
     * @param string $directory
     * @return void
     */
    protected function removeDirectory(string $directory): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $fileinfo */
        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                @\rmdir($fileinfo->getRealPath());
                continue;
            }
            @\unlink($fileinfo->getRealPath());
        }

        @\rmdir($directory);
    }
}
