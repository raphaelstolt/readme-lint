<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Integration;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stolt\ReadmeLint\Commands\LintCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class LintCommandTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        $this->application = new Application();
        $this->application->add(new LintCommand());
    }

    private function getCommandTester(): CommandTester
    {
        $command = $this->application->find('lint');
        return new CommandTester($command);
    }

    #[Test]
    public function failsIfFileDoesNotExist(): void
    {
        $tester = $this->getCommandTester();
        $exitCode = $tester->execute(['file' => '/non/existing/README.md']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('README not found', $tester->getDisplay());
    }

    #[Test]
    public function failsIfDirectoryHasNoReadme(): void
    {
        $dir = \sys_get_temp_dir() . '/readme-lint-dir';
        if (!\is_dir($dir)) {
            \mkdir($dir);
        }

        $tester = $this->getCommandTester();
        $exitCode = $tester->execute(['file' => $dir]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('README not found', $tester->getDisplay());

        \rmdir($dir);
    }

    #[Test]
    #[RunInSeparateProcess]
    public function passesWithValidReadme(): void
    {
        $path = \sys_get_temp_dir() . '/ValidREADME.md';
        file_put_contents($path, <<<MD
# Project Title

[![Build Status](https://example.com/build.svg)](https://example.com)

## Installation
Run composer install.

## Usage
Do something useful.

```php
echo "Hello World";
```

## License
MIT

## Changelog

## Running tests

MD);

        $tester = $this->getCommandTester();

        $exitCode = $tester->execute(['file' => $path]);

        $display = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('looks good', $display);
        $this->assertStringContainsString('README score: 1', $display);

        \unlink($path);
    }

    #[Test]
    public function failsWithInvalidReadme(): void
    {
        $path = \sys_get_temp_dir() . '/InvalidREADME.md';
        file_put_contents($path, <<<MD
# Installation
Run composer install.

# License
MIT
MD);

        $tester = $this->getCommandTester();
        $exitCode = $tester->execute(['file' => $path]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Found issues in', $tester->getDisplay());
        $this->assertStringContainsString('README score:', $tester->getDisplay());

        \unlink($path);
    }
}
