<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint;

final class LintIssue
{
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_ERROR = 'error';

    public function __construct(
        public readonly string $message,
        public readonly string $severity = LintIssue::SEVERITY_ERROR,
        public readonly ?string $suggestion = null
    ) {
    }
}
