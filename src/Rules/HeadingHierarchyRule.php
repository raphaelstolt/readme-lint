<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class HeadingHierarchyRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        $matches = [];
        \preg_match_all('/^(#+)\s+.+$/m', $content, $matches, PREG_SET_ORDER);

        $previousLevel = 0;
        $issues = null;

        foreach ($matches as $match) {
            $level = \strlen($match[1]);
            if ($previousLevel && ($level - $previousLevel) > 1) {
                $issues .= "Improper heading level progression: jumped from H{$previousLevel} to H{$level}.";
            }
            $previousLevel = $level;
        }

        if ($issues !== null) {
            return new LintIssue(
                $issues,
                LintIssue::SEVERITY_WARNING,
                'Avoid skipping heading levels to maintain structure.'
            );
        }

        return null;
    }
}
