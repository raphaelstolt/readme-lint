<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint;

use ReflectionClass;
use ReflectionException;
use Stolt\ReadmeLint\Rules\RuleInterface;
use Symfony\Component\Finder\Finder;

final class Configuration
{
    private array $rulesToApply = [];
    private array $rulesAvailable = [];
    private string $customRulesDirectory = '';
    private string $customRulesNamespace = '';

    private Linter $linter;

    /**
     * @param Linter $linter
     * @throws ReflectionException
     *
     * TODO: Remove linter dependency from configuration.
     */
    public function __construct(Linter $linter)
    {
        $this->linter = $linter;

        if ($this->linter->hasRules() === false) {
            $finder = (new Finder())->files()->in(__DIR__ . '/Rules');
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $fileNameWithoutExtension = $file->getFilenameWithoutExtension();
                    $this->rulesAvailable[] = $fileNameWithoutExtension;
                    $class = 'Stolt\ReadmeLint\Rules\\' . $fileNameWithoutExtension;
                    $reflection = new ReflectionClass($class);
                    if ($reflection->implementsInterface(RuleInterface::class) && $reflection->isInstantiable()) {
                        $this->linter->addRule($reflection->newInstanceWithoutConstructor());
                    }
                }

                $this->rulesToApply = $this->linter->getRules();
            }
        }
    }

    /**
     * Rules to apply should be named as the file name without the extension in /src/Rules.
     *
     * @param array $rules
     * @return $this
     */
    public function addRulesToApply(array $rules): self
    {
        $this->rulesToApply = [];

        foreach ($rules as $ruleToApply) {
            if (\in_array($ruleToApply, $this->rulesAvailable, true)) {
                $this->rulesToApply[] = $ruleToApply;
            }
        }

        return $this;
    }

    public function getRulesToApply(): array
    {
        return $this->rulesToApply;
    }

    public function setCustomRulesDirectory(string $directory, string $namespace): self
    {
        $this->customRulesDirectory = $directory;
        $this->customRulesNamespace = \str_ends_with($namespace, '\\') ? $namespace : $namespace . '\\';

        $finder = (new Finder())->files()->in($this->getCustomRulesDirectory());
        if ($finder->hasResults()) {
            $customRules = [];
            foreach ($finder as $file) {
                $fileNameWithoutExtension = $file->getFilenameWithoutExtension();
                $this->rulesAvailable[] = $fileNameWithoutExtension;
                $class = $this->getCustomRulesNamespace() . $fileNameWithoutExtension;
                $reflection = new ReflectionClass($class);
                if ($reflection->implementsInterface(RuleInterface::class) && $reflection->isInstantiable()) {
                    $customRules[]= $reflection->newInstanceWithoutConstructor();
                }

                $this->rulesToApply = \array_unique(
                    \array_merge($this->rulesToApply, $customRules),
                    SORT_REGULAR
                );
            }
        }

        return $this;
    }

    public function getCustomRulesDirectory(): string
    {
        return $this->customRulesDirectory;
    }

    public function getCustomRulesNamespace(): string
    {
        return $this->customRulesNamespace;
    }
}
