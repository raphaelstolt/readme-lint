<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class BadgeRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        if (!\preg_match('/\!\[[^\]]*\]\([^\)]+\)/', $content)) {
            return new LintIssue(
                'No badges found in the README (e.g. build status, coverage, etc.).',
                LintIssue::SEVERITY_WARNING,
                'Add at least one badge.'
            );
        }

        return null;
    }
}
