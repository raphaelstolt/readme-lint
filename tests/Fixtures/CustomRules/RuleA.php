<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\CustomRules;

use Stolt\ReadmeLint\LintIssue;
use Stolt\ReadmeLint\Rules\RuleInterface;

final class RuleA implements RuleInterface
{

    public function check(string $content): ?LintIssue
    {
        return new LintIssue('Rule a', 'Rule a message');
    }
}
