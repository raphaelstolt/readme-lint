<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Stolt\ReadmeLint\Linter;
use Stolt\ReadmeLint\Rules\LicenseSectionRule;
use Stolt\ReadmeLint\Rules\MaxLineLengthRule;
use Stolt\ReadmeLint\Rules\NoTodoCommentRule;
use Stolt\ReadmeLint\Rules\RequiredSectionsRule;
use Stolt\ReadmeLint\Rules\BadgeRule;
use Stolt\ReadmeLint\Rules\CodeBlockRule;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lint',
    description: 'Lint a README.md file for common quality issues'
)]
final class LintCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'file',
            InputArgument::OPTIONAL,
            'The path to the README file',
            'README.md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('file');

        if (!file_exists($path)) {
            $output->writeln('README <error>not</error> found at <info>' . $path . '</info>');
            return Command::FAILURE;
        }

        $linter = new Linter($path);
        $linter->addRule(new RequiredSectionsRule());
        $linter->addRule(new BadgeRule());
        $linter->addRule(new CodeBlockRule());
        $linter->addRule(new MaxLineLengthRule());
        $linter->addRule(new NoTodoCommentRule());
        $linter->addRule(new LicenseSectionRule());

        $lintIssues = $linter->lint(file_get_contents($path));

        if (empty($issues)) {
            $output->writeln("The README at <info>$path</info> looks good.");
            return Command::SUCCESS;
        }

        $output->writeln("Found issues in <info>$path</info>:");
        foreach ($issues as $issue) {
            $output->writeln("- $issue");
        }

        return Command::FAILURE;
    }
}
