<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Configuration;

final class Resolver
{
    /**
     * Resolves the configuration file path to use.
     *
     * Priority:
     * 1. Explicit --config option of the Lint command
     * 2. .readme-lint.php in the current directory
     * 3. null (no config)
     */
    public function resolveConfigurationPath(?string $explicitConfigurationPath): ?string
    {
        if ($explicitConfigurationPath !== null && $explicitConfigurationPath !== '') {
            return $explicitConfigurationPath;
        }

        $defaultConfigPath = \getcwd() . DIRECTORY_SEPARATOR . '.readme-lint.php';

        // TODO: Validate that the file is valid PHP and returns a configuration object or array.

        if (\file_exists($defaultConfigPath)) {
            return $defaultConfigPath;
        }

        return null;
    }
}
