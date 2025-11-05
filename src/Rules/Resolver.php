<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use InvalidArgumentException;

final class Resolver
{
    private string $rulesNamespace = __NAMESPACE__;

    /**
     * @param string[] $names Short class names or FQCNs
     * @throws InvalidArgumentException
     * @return array<object>
     */
    public function resolveRulesByNames(array $names): array
    {
        $rules = [];
        foreach ($names as $name) {
            $fqcnCandidates = [];
            if (\str_contains($name, '\\')) {
                $fqcnCandidates[] = $name;
            } else {
                $fqcnCandidates[] = $this->rulesNamespace . '\\' . $name;
            }

            $instance = null;

            foreach ($fqcnCandidates as $fqcn) {
                if (\class_exists($fqcn) && \is_subclass_of($fqcn, RuleInterface::class)) {
                    $instance = new $fqcn();
                    break;
                }
            }

            if ($instance === null) {
                throw new InvalidArgumentException("Unknown lint rule: {$name}");
            }

            $rules[] = $instance;
        }

        return $rules;
    }

    /**
     * @param array<int,string|object> $rules
     * @throws InvalidArgumentException
     * @return array<object>
     */
    public function resolveRulesArray(array $rules): array
    {
        $resolved = [];
        foreach ($rules as $r) {
            if (\is_object($r) && $r instanceof RuleInterface) {
                $resolved[] = $r;
            } elseif (\is_string($r)) {
                $resolved[] = $this->resolveRulesByNames([$r])[0];
            } else {
                throw new InvalidArgumentException('Rules must be objects or class names.');
            }
        }
        return $resolved;
    }
}
