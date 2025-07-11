<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class MaxLineLengthRule implements RuleInterface
{
    private int $maxLength;

    public function __construct(int $maxLength = 180)
    {
        $this->maxLength = $maxLength;
    }

    public function check(string $content): ?LintIssue
    {
        $issues = null;
        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $i => $line) {
            if (strlen($line) > $this->maxLength) {
                $issues .= "Line " . ($i + 1) . " exceeds {$this->maxLength} characters." . PHP_EOL;
            }
        }

        if ($issues !== null) {
            return new LintIssue($issues, LintIssue::SEVERITY_WARNING, 'Fix line length of stated lines.');
        }

        return null;
    }
}
