<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;
use Throwable;

final class EvaluatedValueResolver implements ChainableValueResolverInterface
{
    use IsAServiceTrait;
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof EvaluatedValue;
    }

    /**
     * @param EvaluatedValue $value
     *
     * @throws UnresolvableValueException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        // Scope exclusive to the evaluated expression
        // We make use of the underscore prefix (`_`) here to limit the possible conflicts with the variables injected
        // in the scope.
        $_scope = $scope;

        try {
            $_scope['current'] = $fixture->getValueForCurrent();

            if ($_scope['current'] instanceof FixtureInterface) {
                $_scope['current'] = $fixtureSet->getObjects()->get($_scope['current'])->getInstance();
            }
        } catch (NoValueForCurrentException $exception) {
            // Continue
        }

        $expression = $this->replacePlaceholders($value->getValue());
        // Closure in which the expression is evaluated; This is done in a closure to avoid the expression to have
        // access to this method variables (which should remain unknown) and we injected only the variables of the
        // closure.
        $evaluateExpression = static function ($_expression) use ($_scope) {
            foreach ($_scope as $_scopeVariableName => $_scopeVariableValue) {
                $$_scopeVariableName = $_scopeVariableValue;
            }

            return eval("return $_expression;");
        };

        try {
            $evaluatedExpression = $evaluateExpression($expression);
        } catch (Throwable $throwable) {
            throw UnresolvableValueExceptionFactory::createForCouldNotEvaluateExpression($value, 0, $throwable);
        }

        return new ResolvedValueWithFixtureSet($evaluatedExpression, $fixtureSet);
    }

    /**
     * Replaces references to another fixtures, e.g. "@another_dummy" by the variable of the scope
     * "$_instances['another_dummy']".
     */
    private function replacePlaceholders(string $expression): string
    {
        return preg_replace('/(@(?<id>[^ @\-]+))/', '\$_instances[\'$2\']', $expression);
    }
}
