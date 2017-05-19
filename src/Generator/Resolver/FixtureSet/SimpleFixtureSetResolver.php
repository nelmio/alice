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

namespace Nelmio\Alice\Generator\Resolver\FixtureSet;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\Resolver\FixtureBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\IsAServiceTrait;

final class SimpleFixtureSetResolver implements FixtureSetResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var ParameterBagResolverInterface
     */
    private $parameterResolver;

    /**
     * @var FixtureBagResolverInterface
     */
    private $fixtureResolver;

    public function __construct(ParameterBagResolverInterface $parameterResolver, FixtureBagResolverInterface $fixtureResolver)
    {
        $this->parameterResolver = $parameterResolver;
        $this->fixtureResolver = $fixtureResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(FixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $parameters = $this->parameterResolver->resolve(
            $fixtureSet->getLoadedParameters(),
            $fixtureSet->getInjectedParameters()
        );

        $fixtures = $this->fixtureResolver->resolve($fixtureSet->getFixtures());

        return new ResolvedFixtureSet($parameters, $fixtures, $fixtureSet->getObjects());
    }
}
