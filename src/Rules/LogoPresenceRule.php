<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class LogoPresenceRule implements RuleInterface
{
    /**
     * @param string $content
     * @return LintIssue|null
     */
    public function check(string $content): ?LintIssue
    {
        // Check for image references in Markdown
        // Matches both ![alt](path) and <img src="path"> formats
        $markdownImagePattern = '/!\[.*?\]\(([^)]+)\)/';
        $htmlImagePattern = '/<img[^>]+src=["\']([^"\']+)["\']/i';

        \preg_match_all($markdownImagePattern, $content, $markdownMatches);
        \preg_match_all($htmlImagePattern, $content, $htmlMatches);

        $allImagePaths = \array_merge(
            $markdownMatches[1] ?? [],
            $htmlMatches[1] ?? []
        );

        // Check if any image is from the art/ directory or base directory
        $hasLogo = false;
        foreach ($allImagePaths as $imagePath) {
            // Skip external URLs
            if (\preg_match('/^https?:\/\//', $imagePath)) {
                continue;
            }

            // Check if it's in art/ directory or base directory (no subdirectory)
            if (
                \str_starts_with($imagePath, 'art/') ||
                (\str_contains(\basename($imagePath), 'logo') && !\str_contains($imagePath, '/'))
            ) {
                $hasLogo = true;
                break;
            }
        }

        if (!$hasLogo) {
            return new LintIssue(
                'No logo found in README',
                LintIssue::SEVERITY_WARNING,
                'Add a logo reference either in art/ directory or in the base directory (e.g., ![Logo](logo.png) or ![Logo](art/logo.png))'
            );
        }

        return null;
    }
}
