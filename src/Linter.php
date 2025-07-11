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

    public function addRule(RuleInterface $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    public function addRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    public function lint(): array
    {
        if (!file_exists($this->readmePath)) {
            throw new \RuntimeException("README file not found.");
        }

        $content = file_get_contents($this->readmePath);
        $results = [];

        foreach ($this->rules as $rule) {
            $lintIssue = $rule->check($content);
            if ($lintIssue !== null) {
                $results[] = $rule->check($content);
            }
        }

        $score = max(0, 100 - (count(array_filter($results, fn ($i) => $i->severity === LintIssue::SEVERITY_ERROR)) * 10 + count(array_filter($results, fn ($i) => $i->severity === LintIssue::SEVERITY_WARNING)) * 5));

        return [
            'score' => $score,
            'issues' => $results,
        ];
    }
}
