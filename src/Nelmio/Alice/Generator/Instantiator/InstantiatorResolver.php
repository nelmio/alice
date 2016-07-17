<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\Throwable\InstantiationThrowable;

final class InstantiatorResolver implements InstantiatorInterface
{
    /**
     * @var ValueResolverInterface
     */
    private $valueResolver;

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    public function __construct(ValueResolverInterface $valueResolver, InstantiatorInterface $instantiator)
    {
        $this->valueResolver = $valueResolver;
        $this->instantiator = $instantiator;
    }

    /**
     * Instantiates the object described by the given fixture. Has access to the current fixture set and returns the new
     * fixture set containing the instantiated the object.
     *
     * @param FixtureInterface   $fixture
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @throws InstantiationThrowable
     *
     * @return ResolvedFixtureSet
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $specs = $fixture->getSpecs();
        $constructor = $specs->getConstructor();
        
        $result = $this->getResolvedArguments($constructor->getArguments(), $this->valueResolver, $fixture, $fixtureSet);
        $fixtureSet = $result->getSet();
        /** @var array $arguments */
        $resolvedArguments = $result->getValue();
        
        return $this->instantiator->instantiate(
            $fixture->withSpecs(
                $specs->withConstructor(
                    $constructor->withArguments($resolvedArguments)
                )
            ),
            $fixtureSet
        );
    }

    /**
     * @param array|ValueInterface[] $arguments
     * @param ValueResolverInterface      $resolver
     * @param FixtureInterface            $fixture
     * @param ResolvedFixtureSet          $fixtureSet
     *
     * @return ResolvedValueWithFixtureSet
     */
    private function getResolvedArguments(
        array $arguments,
        ValueResolverInterface $resolver,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet
    ): ResolvedValueWithFixtureSet
    {
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                $result = $resolver->resolve($argument, $fixture, $fixtureSet);

                $fixtureSet = $result->getSet();
                $arguments[$index] = $result->getValue();
            }
        }
        
        return new ResolvedValueWithFixtureSet($arguments, $fixtureSet);
    }
}
