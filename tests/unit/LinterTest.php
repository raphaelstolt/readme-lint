<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Stolt\ReadmeLint\Linter;
use Stolt\ReadmeLint\LintIssue;
use Stolt\ReadmeLint\Rules\RuleInterface;

final class LinterTest extends TestCase
{
    private string $tempReadme;

    protected function setUp(): void
    {
        $this->tempReadme = tempnam(sys_get_temp_dir(), 'readme_');
        file_put_contents($this->tempReadme, "# Title\nSome content");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempReadme)) {
            unlink($this->tempReadme);
        }
    }

    #[Test]
    public function throwsExceptionIfFileNotFound(): void
    {
        $linter = new Linter('/non/existing/path/README.md');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('README file not found.');

        $linter->lint();
    }

    #[Test]
    public function noRulesMeansPerfectScore(): void
    {
        $linter = new Linter($this->tempReadme);
        $result = $linter->lint();

        $this->assertSame(100, $result['score']);
        $this->assertEmpty($result['issues']);
    }

    #[Test]
    public function ruleReturningNullDoesNotAddIssue(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('check')->willReturn(null);

        $linter = new Linter($this->tempReadme);
        $linter->addRule($rule);

        $result = $linter->lint();

        $this->assertSame(100, $result['score']);
        $this->assertEmpty($result['issues']);
    }

    #[Test]
    public function ruleReturningErrorIssueDecreasesScore(): void
    {
        $issue = new LintIssue(
            'Missing section',
            LintIssue::SEVERITY_ERROR
        );

        $rule = $this->createMock(RuleInterface::class);
        $rule->method('check')->willReturn($issue);

        $linter = new Linter($this->tempReadme);
        $linter->addRule($rule);

        $result = $linter->lint();

        $this->assertSame(90, $result['score']); // -10 per error
        $this->assertCount(1, $result['issues']);
        $this->assertSame($issue, $result['issues'][0]);
    }

    #[Test]
    public function ruleReturningWarningIssueDecreasesScore(): void
    {
        $issue = new LintIssue(
            'Optional section missing',
            LintIssue::SEVERITY_WARNING
        );

        $rule = $this->createMock(RuleInterface::class);
        $rule->method('check')->willReturn($issue);

        $linter = new Linter($this->tempReadme);
        $linter->addRule($rule);

        $result = $linter->lint();

        $this->assertSame(95, $result['score']); // -5 per warning
        $this->assertCount(1, $result['issues']);
        $this->assertSame($issue, $result['issues'][0]);
    }

    #[Test]
    public function addRulesAllowsMultipleRules(): void
    {
        $errorIssue = new LintIssue('Error issue', LintIssue::SEVERITY_ERROR);
        $warningIssue = new LintIssue('Warning issue', LintIssue::SEVERITY_WARNING);

        $errorRule = $this->createMock(RuleInterface::class);
        $errorRule->method('check')->willReturn($errorIssue);

        $warningRule = $this->createMock(RuleInterface::class);
        $warningRule->method('check')->willReturn($warningIssue);

        $linter = new Linter($this->tempReadme);
        $linter->addRules([$errorRule, $warningRule]);

        $result = $linter->lint();

        $this->assertSame(85, $result['score']); // 100 - 10 - 5
        $this->assertCount(2, $result['issues']);
        $this->assertContains($errorIssue, $result['issues']);
        $this->assertContains($warningIssue, $result['issues']);
    }
}
