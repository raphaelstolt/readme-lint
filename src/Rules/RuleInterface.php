<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

interface RuleInterface
{
    public function check(string $content): ?LintIssue;
}
