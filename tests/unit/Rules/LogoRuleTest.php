<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Stolt\ReadmeLint\LintIssue;
use Stolt\ReadmeLint\Rules\LogoPresenceRule;
use Stolt\ReadmeLint\Rules\LogoRule;

final class LogoRuleTest extends TestCase
{
    private LogoPresenceRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LogoPresenceRule();
    }

    public function testPassesWithLogoInArtDirectory(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Logo](art/logo.png)

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testPassesWithLogoInBaseDirectory(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Project Logo](project-logo.png)

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testPassesWithHtmlImageInArtDirectory(): void
    {
        $content = <<<'MARKDOWN'
<p align="center">
    <img src="art/logo.svg" alt="Logo">
</p>

# My Project
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testPassesWithHtmlImageInBaseDirectory(): void
    {
        $content = <<<'MARKDOWN'
<p align="center">
    <img src="readme-lint-logo.png"
         alt="Readme-lint logo"
         title="Readme-lint logo">
</p>

# My Project
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testFailsWithoutLogo(): void
    {
        $content = <<<'MARKDOWN'
# My Project

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertInstanceOf(LintIssue::class, $result);
        $this->assertSame('No logo found in README', $result->getMessage());
        $this->assertSame(LintIssue::SEVERITY_WARNING, $result->getSeverity());
    }

    public function testFailsWithImageInSubdirectoryOtherThanArt(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Screenshot](docs/screenshot.png)

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertInstanceOf(LintIssue::class, $result);
        $this->assertSame('No logo found in README', $result->getMessage());
    }

    public function testIgnoresExternalUrls(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Badge](https://img.shields.io/badge/test-badge.svg)

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertInstanceOf(LintIssue::class, $result);
    }

    public function testPassesWithMultipleImagesIncludingLogo(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Logo](art/logo.png)

Some description here.

![Screenshot](docs/demo.png)
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testPassesWithLogoInBaseDirWithoutLogoInName(): void
    {
        $content = <<<'MARKDOWN'
# My Project

![Icon](art/icon.png)

Some description here.
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }

    public function testPassesWithSingleQuotesInHtmlImage(): void
    {
        $content = <<<'MARKDOWN'
<img src='art/logo.png' alt='Logo'>

# My Project
MARKDOWN;

        $result = $this->rule->check($content);

        $this->assertNull($result);
    }
}
