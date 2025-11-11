<?php declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands;

use Stolt\ReadmeLint\Commands\Repository\TemplateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'init',
    description: 'Create an initial README.md file in the current working or provided directory'
)]
final class InitCommand extends Command
{
    private const DEFAULT_FILENAME = 'README.md';
    private TemplateRepository $templateRepository;

    public function __construct(TemplateRepository $templateRepository = new TemplateRepository())
    {
        parent::__construct();

        $this->templateRepository = $templateRepository;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'Target path for the README.md file',
            realpath(\getcwd())
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathArgument = (string) $input->getArgument('path');

        $targetPath = $this->normalizeTargetPath($pathArgument);
        $directory = \is_dir($pathArgument) ? \rtrim($pathArgument, DIRECTORY_SEPARATOR) : \dirname($targetPath);

        if (!\is_dir($directory)) {
            if (!@\mkdir($directory, 0777, true) && !\is_dir($directory)) {
                $output->writeln('Failed to create directory <info>' . $directory . '</info>.');

                return Command::FAILURE;
            }
        }

        $directoryOperatedIn = realpath(dirname($targetPath));

        if (\file_exists($targetPath)) {
            $output->writeln('<info>' . self::DEFAULT_FILENAME . '</info> already exists at <info>' . $directoryOperatedIn . '</info>.');

            return Command::FAILURE;
        }

        $template = $this->templateRepository->getDefaultReadmeTemplate(\basename(\getcwd()));

        // TODO: Validate target path has no README.md file.

        if (@file_put_contents($targetPath, $template) === false) {
            $output->writeln('Failed to write <info>' . self::DEFAULT_FILENAME . '</info> in <info>' . $directoryOperatedIn . '</info>.');

            return Command::FAILURE;
        }

        $output->writeln('Created <info>' . self::DEFAULT_FILENAME . '</info> in <info>' . $directoryOperatedIn . '</info>.');

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
}
