<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class NoTodoCommentRule implements RuleInterface
{
    private int $maxLength;

    public function __construct(int $maxLength = 120)
    {
        $this->maxLength = $maxLength;
    }

    public function check(string $content): ?LintIssue
    {
        $issues = null;
        $lines = \explode(PHP_EOL, $content);

        foreach ($lines as $i => $line) {
            if (\stripos($line, 'TODO') !== false) {
                $issues .= "Line " . ($i + 1) . " contains a TODO comment." . PHP_EOL;
            }
        }

        if ($issues !== null) {
            return new LintIssue($issues, LintIssue::SEVERITY_WARNING, 'Remove TODO comment(s).');
        }

        return null;
    }
}
