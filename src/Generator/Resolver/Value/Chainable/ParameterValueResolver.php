<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Exception\ParameterNotFoundException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class ParameterValueResolver implements ChainableValueResolverInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof ParameterValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param ParameterValue $value
     *
     * @throws ParameterNotFoundException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = [],
        int $tryCounter = 0
    ): ResolvedValueWithFixtureSet
    {
        $parameterKey = $value->getValue();
        $parameters = $fixtureSet->getParameters();
        if (false === $parameters->has($parameterKey)) {
            throw new UnresolvableValueException(
                sprintf(
                    'Could not find the parameter "%s".',
                    $parameterKey
                )
            );
        }

        return new ResolvedValueWithFixtureSet(
            $parameters->get($parameterKey),
            $fixtureSet
        );
    }
}
