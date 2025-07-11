<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class LicenseSectionRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        if (!preg_match('/^#+\s*License/im', $content)) {
            return new LintIssue(
                "Missing 'License' section in README.",
                'error',
                'Add a LICENSE section to the README file.'
            );
        }

        return null;
    }
}
