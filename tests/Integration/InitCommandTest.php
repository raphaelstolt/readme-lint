<?php declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use Stolt\ReadmeLint\Commands\InitCommand;
use Stolt\ReadmeLint\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class InitCommandTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setUpTemporaryDirectory();
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->temporaryDirectory);
    }

    private function getCommandTester(): CommandTester
    {
        $application = new Application('readme-lint', 'test');
        $application->add(new InitCommand());

        /** @var Command $command */
        $command = $application->find('init');

        return new CommandTester($command);
    }


    #[Test]
    public function createsReadmeInCwdWhenNoneExists(): void
    {
        \chdir($this->temporaryDirectory);

        $tester = $this->getCommandTester();
        $exit = $tester->execute([]);

        $this->assertSame(0, $exit, $tester->getDisplay());
        $this->assertFileExists($this->temporaryDirectory . DIRECTORY_SEPARATOR . 'README.md');

        $content = \file_get_contents($this->temporaryDirectory . DIRECTORY_SEPARATOR . 'README.md');

        $this->assertIsString($content);
        $this->assertStringContainsString('# ' . \basename($this->temporaryDirectory), $content);
    }

    #[Test]
    public function createsReadmeAtCustomFilePath(): void
    {
        $target = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'PROJECT_README.md';

        $tester = $this->getCommandTester();
        $exit = $tester->execute(['--path' => $target]);

        $this->assertSame(0, $exit, $tester->getDisplay());
        $this->assertFileExists($target);
        $this->assertStringContainsString('# readme-lint', (string) \file_get_contents($target));
    }
}
