<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class CodeBlockRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        if (!\preg_match('/```[\s\S]*?```/', $content)) {
            return new LintIssue(
                'README is missing code examples (no fenced code blocks found).',
                LintIssue::SEVERITY_WARNING,
                'Add at least one code block.'
            );
        }

        return null;
    }
}
