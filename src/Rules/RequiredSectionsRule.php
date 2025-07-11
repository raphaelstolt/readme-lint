<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class RequiredSectionsRule implements RuleInterface
{
    private array $requiredSections = [
        'Installation', 'Usage', 'License'
    ];

    public function check(string $content): ?LintIssue
    {
        foreach ($this->requiredSections as $section) {
            if (!str_contains($content, "## $section") && !str_contains($content, strtolower($section))) {
                return new LintIssue(
                    'Missing required section ' . $section,
                    LintIssue::SEVERITY_ERROR,
                    'Add required section ' . $section
                );
            }
        }

        return null;
    }
}
