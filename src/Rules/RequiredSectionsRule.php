<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class RequiredSectionsRule implements RuleInterface
{
    private array $requiredSections = [
        'Installation', 'Usage', 'License'
    ];

    private function extractHeadings(string $markdown): array
    {
        preg_match_all('/^(#+)\s+(.*)$/m', $markdown, $matches);

        return array_map(
            fn ($heading) => strtolower(trim($heading)),
            $matches[2]
        );
    }

    /**
     * @param string $content
     * @return LintIssue|null
     */
    public function check(string $content): ?LintIssue
    {
        $headings = $this->extractHeadings($content);

        foreach ($this->requiredSections as $section) {
            if (!in_array(strtolower($section), $headings, true)) {
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
