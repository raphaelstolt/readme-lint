<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Stolt\ReadmeLint\Configuration;
use Stolt\ReadmeLint\Configuration\Resolver as ConfigurationResolver;
use Stolt\ReadmeLint\Linter;
use Stolt\ReadmeLint\LintIssue;
use Stolt\ReadmeLint\Rules\BadgeRule;
use Stolt\ReadmeLint\Rules\CodeBlockRule;
use Stolt\ReadmeLint\Rules\CurrentCodeBlockRule;
use Stolt\ReadmeLint\Rules\HeadingHierarchyRule;
use Stolt\ReadmeLint\Rules\MaxLineLengthRule;
use Stolt\ReadmeLint\Rules\NoTodoCommentRule;
use Stolt\ReadmeLint\Rules\RequiredSectionsRule;
use Stolt\ReadmeLint\Rules\Resolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        )->addOption(
            'rules',
            null,
            InputOption::VALUE_OPTIONAL,
            'Comma-separated list of lint rules to apply (e.g. RequiredSectionsRule, MaxLineLengthRule)'
        )->addOption(
            'config',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path to a readme-lint configuration file'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('file');

        if (!\file_exists($path)) {
            $output->writeln('README <error>not</error> found at <info>' . $path . '</info>');
            return Command::FAILURE;
        }

        if (\is_dir($path)) {
            $directoryPath = $path;
            $path = $path . DIRECTORY_SEPARATOR . 'README.md';
            if (!\file_exists($path)) {
                $output->writeln('README <error>not</error> found at <info>' . $directoryPath . '</info>');
                return Command::FAILURE;
            }
        }

        $linter = (new Linter($path));
        $rulesResolver = new Resolver();
        $configurationResolver = new ConfigurationResolver();

        $viaOptionSetConfigurationPath = (string) $input->getOption('config');
        $viaOptionSetRules = (string) $input->getOption('rules');

        $configurationPath = $configurationResolver->resolveConfigurationPath($viaOptionSetConfigurationPath);

        if ($configurationPath !== null) {
            if (!\file_exists($configurationPath)) {
                $output->writeln('Configuration file <error>not</error> found at <info>' . $configurationPath . '</info>');
                return Command::FAILURE;
            }

            $config = require $configurationPath;

            if ($config instanceof Configuration) {
                $linter->addRules($config->getRulesToApply());
            } elseif (\is_array($config)) {
                // Expecting ['rules' => [FQCN|string,...]]
                if (isset($config['rules']) && \is_array($config['rules'])) {
                    $resolved = $rulesResolver->resolveRulesArray($config['rules']);
                    $linter->addRules($resolved);
                }
            }
        }

        // Override config rules with --rules option if provided
        if ($viaOptionSetRules !== '') {
            $names = \array_values(\array_filter(\array_map('trim', \explode(',', $viaOptionSetRules))));
            // Clear previously added rules and use only the ones from --rules option
            $linter = (new Linter($path));
            $linter->addRules($rulesResolver->resolveRulesByNames($names));
        } elseif ($configurationPath === null) {
            // No config found and no rules option provided, use defaults
            $linter->addRules([
                new RequiredSectionsRule(),
                new BadgeRule(),
                new CodeBlockRule(),
                new CurrentCodeBlockRule(),
                new MaxLineLengthRule(),
                new NoTodoCommentRule(),
                new HeadingHierarchyRule()
            ]);
        }

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
