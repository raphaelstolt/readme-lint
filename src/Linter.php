<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint;

use Stolt\ReadmeLint\Rules\RuleInterface;

final class Linter
{
    private string $readmePath;
    private array $rules = [];

    public function __construct(string $readmePath)
    {
    $this->readmePath = $readmePath;
    }

    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    public function lint(): array
    {
        if (!file_exists($this->readmePath)) {
            throw new \RuntimeException("README file not found.");
        }

        $content = file_get_contents($this->readmePath);
        $results = [];

        foreach ($this->rules as $rule) {
            $results[] = $rule->check($content);
        }

        return array_filter($results);
    }
}
