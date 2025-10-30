<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Stolt\ReadmeLint\Spellchecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'spellcheck',
    description: 'Check a README.md file for wording or spelling mistakes'
)]
final class SpellcheckCommand extends Command
{
    private Spellchecker $spellchecker;

    /**
     * @param string $peckPath
     */
    public function __construct(Spellchecker $spellchecker)
    {
        parent::__construct();
        $this->spellchecker = $spellchecker;
    }

    protected function configure(): void
    {
        $this->addOption(
            'init',
            null,
            InputOption::VALUE_NONE,
            'Initiate the spellchecker configuration'
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('init')) {

            if ($this->spellchecker->checkSpellcheckerAvailability() === false) {
                $output->write('Failed to find the spellchecker binary.');
                return Command::FAILURE;
            }

            $process = $this->spellchecker->runSpellcheckerInit();

            if ($process->isRunning()) {
                $output->write('Initiating the spellchecker configuration.', OutputInterface::VERBOSITY_VERBOSE);
            }

            if ($process->isSuccessful()) {
                $output->write('Initiated the spellchecker configuration.');
                return Command::SUCCESS;
            }

            $output->write('Failed to initiated the spellchecker configuration.');

            return Command::FAILURE;
        }

        $process = $this->spellchecker->runSpellchecker();

        if ($process->isRunning()) {
            $output->write('Running the spellchecker binary.', OutputInterface::VERBOSITY_VERBOSE);
        }

        if (!$process->isSuccessful()) {
            $output->write('The spellchecker found the following errors: ' . PHP_EOL . $process->getOutput());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
