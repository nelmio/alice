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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Value\ValueForCurrentValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;

final class ValueForCurrentValueResolver implements ChainableValueResolverInterface
{
    use IsAServiceTrait;
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof ValueForCurrentValue;
    }

    /**
     * @param ValueForCurrentValue $value
     *
     * @throws NoValueForCurrentException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        $valueForCurrent = $fixture->getValueForCurrent();

        if ($valueForCurrent instanceof FixtureInterface) {
            $valueForCurrent = new SimpleFixture(
                $valueForCurrent->getId(),
                $valueForCurrent->getClassName(),
                $valueForCurrent->getSpecs(),
                $fixtureSet->getObjects()->get($valueForCurrent)->getInstance()
            );
        } else {
            $valueForCurrent = $fixtureSet->getFixtures()->get($fixture->getId());
        }

        return new ResolvedValueWithFixtureSet(
            $valueForCurrent,
            $fixtureSet
        );
    }
}
