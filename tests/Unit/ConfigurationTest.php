<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Stolt\ReadmeLint\Configuration;
use Stolt\ReadmeLint\Linter;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function autoloadsRulesIfNotSet(): void
    {
        $linter = new Linter('/tmp/rl');

        $configuration = new Configuration($linter);

        $this->assertCount(9, $configuration->getRulesToApply());
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function loadsCustomRules(): void
    {
        $linter = new Linter('/tmp/rl');

        $configuration = new Configuration($linter);

        $configuration->setCustomRulesDirectory(
            \realpath(\getcwd() . '/tests/Fixtures/CustomRules'),
            'Stolt\ReadmeLint\CustomRules\\'
        );

        $this->assertCount(11, $configuration->getRulesToApply());
    }

    #[Test]
    public function setsTheExpectedRulesToApply(): void
    {
        $linter = new Linter('/tmp/rl');

        $configuration = new Configuration($linter);
        $configuration->addRulesToApply(['CodeBlockRule', 'LogoPresenceRule', 'PhpVersionBadgeRule']);

        $this->assertCount(3, $configuration->getRulesToApply());
    }

    #[Test]
    public function setsTheExpectedRulesToApplyAndACustomRule(): void
    {
        $linter = new Linter('/tmp/rl');

        $configuration = new Configuration($linter);
        $configuration->setCustomRulesDirectory(
            \realpath(\getcwd() . '/tests/Fixtures/CustomRules'),
            'Stolt\ReadmeLint\CustomRules'
        );

        $configuration->addRulesToApply(['CodeBlockRule', 'LogoPresenceRule', 'PhpVersionBadgeRule', 'RuleB']);

        $this->assertCount(4, $configuration->getRulesToApply());
        $this->assertContains('RuleB', $configuration->getRulesToApply());
        $this->assertContains('LogoPresenceRule', $configuration->getRulesToApply());
    }
}
