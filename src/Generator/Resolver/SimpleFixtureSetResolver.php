<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class SimpleFixtureSetResolver implements FixtureSetResolverInterface
{
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
     * Resolves the loaded parameters and merge the injected ones with them and also resolves the fixture flags.
     *
     * @param FixtureSet $fixtureSet
     *
     * @throws ResolutionThrowable
     *
     * @return ResolvedFixtureSet
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
