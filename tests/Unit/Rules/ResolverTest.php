<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Tests\Unit\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stolt\ReadmeLint\Rules\MaxLineLengthRule;
use Stolt\ReadmeLint\Rules\Resolver;

final class ResolverTest extends TestCase
{
    #[Test]
    public function resolvesShortRuleNamesFromDefaultNamespace(): void
    {
        $resolver = new Resolver();

        $rules = $resolver->resolveRulesByNames(['MaxLineLengthRule']);

        $this->assertCount(1, $rules);
        $this->assertInstanceOf(MaxLineLengthRule::class, $rules[0]);
    }

    #[Test]
    public function resolvesFqcnRuleNamesWithoutNamespacePrefixing(): void
    {
        $resolver = new Resolver();

        $rules = $resolver->resolveRulesByNames([MaxLineLengthRule::class]);

        $this->assertCount(1, $rules);
        $this->assertInstanceOf(MaxLineLengthRule::class, $rules[0]);
    }

    #[Test]
    public function throwsExpectedExceptionForUnknownRuleName(): void
    {
        $resolver = new Resolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown lint rule: DoesNotExistRule');

        $resolver->resolveRulesByNames(['DoesNotExistRule']);
    }

    #[Test]
    public function resolveRulesArrayAcceptsInstancesAndClassNames(): void
    {
        $resolver = new Resolver();

        $instance = new MaxLineLengthRule();
        $resolved = $resolver->resolveRulesArray([
            $instance,
            MaxLineLengthRule::class,
            'MaxLineLengthRule',
        ]);

        $this->assertCount(3, $resolved);
        $this->assertInstanceOf(MaxLineLengthRule::class, $resolved[0]);
        $this->assertSame($instance, $resolved[0]); // keeps instance as-is
        $this->assertInstanceOf(MaxLineLengthRule::class, $resolved[1]);
        $this->assertInstanceOf(MaxLineLengthRule::class, $resolved[2]);
    }

    #[Test]
    #[DataProvider('invalidRulesProvider')]
    public function resolveRulesArrayRejectsInvalidEntries(array $input): void
    {
        $resolver = new Resolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rules must be objects or class names.');

        $resolver->resolveRulesArray($input);
    }

    public static function invalidRulesProvider(): array
    {
        return [
            'integer' => [[123]],
            'bool' => [[true]],
            'null' => [[null]],
            'array' => [[['MaxLineLengthRule']]],
            'object-non-rule' => [[new \stdClass()]],
        ];
    }
}
