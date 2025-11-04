<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class PhpVersionBadgeRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        if (!\preg_match('/\bphp\b.*badge.*shields\.io/i', $content) && !\preg_match('/img\.shields\.io\/badge\/php-/i', $content)) {
            return new LintIssue(
                'Missing PHP version badge.',
                LintIssue::SEVERITY_WARNING,
                'Consider adding a PHP version badge (e.g. from shields.io) to indicate version support.'
            );
        }

        return null;
    }
}
