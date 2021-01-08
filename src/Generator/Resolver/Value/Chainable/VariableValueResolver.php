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

use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;

final class VariableValueResolver implements ChainableValueResolverInterface
{
    use IsAServiceTrait;
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof VariableValue;
    }

    /**
     * @param VariableValue $value
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
        $variableName = $value->getValue();
        if (array_key_exists($variableName, $scope)) {
            return new ResolvedValueWithFixtureSet(
                $scope[$variableName],
                $fixtureSet
            );
        }

        throw UnresolvableValueExceptionFactory::createForCouldNotFindVariable($value);
    }
}
