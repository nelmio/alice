<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureResolverInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParameterBagResolverInterface;
use Nelmio\Alice\UnresolvedFixtureSet;

final class DefaultGenerator implements GeneratorInterface
{
    /**
     * @var FixtureResolverInterface
     */
    private $fixtureGenerator;

    /**
     * @var ParameterBagResolverInterface
     */
    private $parameterResolver;

    public function __construct(ParameterBagResolverInterface $parameterResolver, FixtureBagGeneratorInterface $fixtureGenerator)
    {
        $this->parameterResolver = $parameterResolver;
        $this->fixtureGenerator = $fixtureGenerator;
    }

    /**
     * @inheritdoc
     */
    public function generate(UnresolvedFixtureSet $fixtureSet, ParameterBag $injectedParameters, ObjectBag $injectedObjects): ObjectBag
    {
        $parameters = $this->parameterResolver->resolve($fixtureSet->getParameters(), $injectedParameters);

        return $this->fixtureGenerator->generate($fixtureSet->getFixtures(), $parameters, $injectedObjects);
    }
}
