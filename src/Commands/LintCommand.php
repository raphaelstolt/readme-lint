<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Stolt\ReadmeLint\Linter;
use Stolt\ReadmeLint\LintIssue;
use Stolt\ReadmeLint\Rules\CurrentCodeBlockRule;
use Stolt\ReadmeLint\Rules\HeadingHierarchyRule;
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

        if (is_dir($path)) {
            $directoryPath = $path;
            $path = $path . DIRECTORY_SEPARATOR . 'README.md';
            if (!file_exists($path)) {
                $output->writeln('README <error>not</error> found at <info>' . $directoryPath . '</info>');
                return Command::FAILURE;
            }
        }

        $linter = (new Linter($path))->addRules([
            new RequiredSectionsRule(),
            new BadgeRule(),
            new CodeBlockRule(),
            new CurrentCodeBlockRule(),
            new MaxLineLengthRule(),
            new NoTodoCommentRule(),
            new HeadingHierarchyRule()
        ]);

        $lintResult = $linter->lint();
        $score = $lintResult['score'];

        if ($lintResult['issues'] === []) {
            $output->writeln("The README at <info>$path</info> looks good.");
            $output->writeln("README score: " . $score / 100);
            return Command::SUCCESS;
        }

        $output->writeln("Found issues in <info>$path</info>:");

        foreach ($lintResult['issues'] as $lintIssue) {
            $emoji = $lintIssue->severity === LintIssue::SEVERITY_ERROR ? '❌' : '⚠️';
            $output->writeln("{$emoji} {$lintIssue->message} 📌 {$lintIssue->suggestion}");
        }

        $output->writeln("README score: " . $score / 100);

        return $score < 100 ? Command::FAILURE : Command::SUCCESS;
    }
}
