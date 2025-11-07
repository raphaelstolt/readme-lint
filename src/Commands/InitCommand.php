<?php declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'init',
    description: 'Create an initial README.md file in the current working directory'
)]
final class InitCommand extends Command
{
    private const DEFAULT_FILENAME = 'README.md';

    protected function configure(): void
    {
        $this->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Target path (directory or full file path) for the README.',
            \getcwd() . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathOption = (string) $input->getOption('path');

        $targetPath = $this->normalizeTargetPath($pathOption);
        $dir = \is_dir($pathOption) ? \rtrim($pathOption, DIRECTORY_SEPARATOR) : \dirname($targetPath);

        if (!\is_dir($dir)) {
            if (!@\mkdir($dir, 0777, true) && !\is_dir($dir)) {
                $output->writeln('<error>Failed to create directory: <info>' . $dir . '</info></error>');

                return Command::FAILURE;
            }
        }

        if (\file_exists($targetPath)) {
            $output->writeln('<comment>' . self::DEFAULT_FILENAME . ' already exists: ' . $targetPath . '</comment>');

            return Command::FAILURE;
        }

        $template = $this->defaultReadmeTemplate();

        if (@file_put_contents($targetPath, $template) === false) {
            $output->writeln('<error>Failed to write ' . self::DEFAULT_FILENAME . ': ' . $targetPath . '</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>' . self::DEFAULT_FILENAME . ' created at: ' . $targetPath . '</info>');

        return Command::SUCCESS;
    }

    private function normalizeTargetPath(string $pathOption): string
    {
        // If a directory is given, append README.md
        if ($pathOption === '' || \substr($pathOption, -1) === DIRECTORY_SEPARATOR || \is_dir($pathOption)) {
            return \rtrim($pathOption ?: \getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;
        }

        // If a file-like path is given, use as-is
        return $pathOption;
    }

    private function defaultReadmeTemplate(): string
    {
        $projectName = \basename(\getcwd());

        return <<<MD
# {$projectName}

Short project description.

## Installation

```bash
composer install
```

## Usage

Describe how to use the project.

## Contributing

Please submit issues and pull requests.

## License

This project is licensed under the MIT License. See LICENSE.md for details.

MD;
    }
}
