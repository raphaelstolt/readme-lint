<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Integration;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stolt\ReadmeLint\Commands\SpellcheckCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SpellcheckCommandTest extends TestCase
{
    #[Test]
    #[Group('integration')]
    public function failsWhenPeckBinaryIsMissing(): void
    {
        $mockedSpellchecker = Mockery::mock(
            'Stolt\ReadmeLint\Spellchecker[checkSpellcheckerAvailability]',
            ['./vendor/bin/peck']
        );
        $mockedSpellchecker->shouldReceive('checkSpellcheckerAvailability')
            ->once()
            ->andReturn(false);

        $application = $this->getApplicationWithMockedSpellchecker($mockedSpellchecker);

        $command = $application->find('spellcheck');
        ;
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--init' => true,
        ]);

        $expectedDisplay = 'Failed to find the spellchecker binary.';

        $this->assertSame($expectedDisplay, $commandTester->getDisplay());
        $this->assertTrue($commandTester->getStatusCode() === Command::FAILURE);
    }

    #[Test]
    #[Group('integration')]
    public function initsSpellcheckerAndSucceeds(): void
    {
        $mockedSpellchecker = Mockery::mock(
            'Stolt\ReadmeLint\Spellchecker[runSpellcheckerInit]',
            ['./vendor/bin/peck']
        );

        $mockedProcess = Mockery::mock(
            'Symfony\Component\Process\Process[isSuccessful]',
            [['some-command']]
        );

        $mockedProcess->shouldReceive('isSuccessful')->once()->andReturn(true);

        $mockedSpellchecker->shouldReceive('runSpellcheckerInit')
            ->once()
            ->andReturn($mockedProcess);

        $application = $this->getApplicationWithMockedSpellchecker($mockedSpellchecker);

        $command = $application->find('spellcheck');
        ;
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--init' => true,
        ]);

        $expectedDisplay = 'Initiated the spellchecker configuration.';

        $this->assertSame($expectedDisplay, $commandTester->getDisplay());
        $this->assertTrue($commandTester->getStatusCode() === Command::SUCCESS);
    }

    #[Test]
    #[Group('integration')]
    public function succeedsWhenPeckRunIsSuccessful(): void
    {
        $mockedSpellchecker = Mockery::mock(
            'Stolt\ReadmeLint\Spellchecker[runSpellchecker]',
            ['./vendor/bin/peck']
        );

        $mockedProcess = Mockery::mock(
            'Symfony\Component\Process\Process[isSuccessful]',
            [['some-command']]
        );

        $mockedProcess->shouldReceive('isSuccessful')->once()->andReturn(true);

        $mockedSpellchecker->shouldReceive('runSpellchecker')
            ->once()
            ->andReturn($mockedProcess);

        $application = $this->getApplicationWithMockedSpellchecker($mockedSpellchecker);

        $command = $application->find('spellcheck');
        ;
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $this->assertSame('', $commandTester->getDisplay());
        $this->assertTrue($commandTester->getStatusCode() === Command::SUCCESS);
    }

    #[Test]
    #[Group('integration')]
    public function reportsErrorsWhenPeckRunFails(): void
    {
        $mockedSpellchecker = Mockery::mock(
            'Stolt\ReadmeLint\Spellchecker[runSpellchecker]',
            ['./vendor/bin/peck']
        );

        $mockedProcess = Mockery::mock(
            'Symfony\Component\Process\Process[isSuccessful,getOutput]',
            [['some-command']]
        );

        $mockedProcess->shouldReceive('isSuccessful')->once()->andReturn(false);
        $mockedProcess->shouldReceive('getOutput')->once()->andReturn('mocked-peck-output');

        $mockedSpellchecker->shouldReceive('runSpellchecker')
            ->once()
            ->andReturn($mockedProcess);

        $application = $this->getApplicationWithMockedSpellchecker($mockedSpellchecker);

        $command = $application->find('spellcheck');
        ;
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $expectedPartialConsoleOutput = 'mocked-peck-output';

        $this->assertStringContainsString($expectedPartialConsoleOutput, $commandTester->getDisplay());

        $expectedPartialConsoleOutput = 'The spellchecker found the following errors:';

        $this->assertStringContainsString($expectedPartialConsoleOutput, $commandTester->getDisplay());

        $this->assertTrue($commandTester->getStatusCode() === Command::FAILURE);
    }

    /**
     * @param  MockInterface $mockedSpellchecker
     * @return Application
     */
    protected function getApplicationWithMockedSpellchecker(MockInterface $mockedSpellchecker): Application
    {
        $application = new Application();
        $application->add(new SpellcheckCommand($mockedSpellchecker));

        return $application;
    }
}
