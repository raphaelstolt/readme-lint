<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint;

final class LintIssue
{
    public function __construct(
        public readonly string $message,
        public readonly string $severity = 'error',
        public readonly ?string $suggestion = null
    ) {}
}
