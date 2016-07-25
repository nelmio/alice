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

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
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
     * Resolves the fixture consturctor arguments before instantiating it.
     *
     * {@inheritdoc}
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        list($fixture, $fixtureSet) = $this->resolveFixtureConstructor($fixture, $fixtureSet);

        return $this->instantiator->instantiate($fixture, $fixtureSet);
    }

    private function resolveFixtureConstructor(FixtureInterface $fixture, ResolvedFixtureSet $set): array
    {
        $specs = $fixture->getSpecs();
        $constructor = $specs->getConstructor();

        if (null === $constructor || $constructor instanceof NoMethodCall) {
            return [$fixture, $set];
        }

        list($resolvedArguments, $set) = $this->resolveArguments($constructor->getArguments(), $this->valueResolver, $fixture, $set);

        return [
            $fixture->withSpecs(
                $specs->withConstructor(
                    $constructor->withArguments($resolvedArguments)
                )
            ),
            $set,
        ];
    }

    private function resolveArguments(
        array $arguments,
        ValueResolverInterface $resolver,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet
    ): array
    {
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                $result = $resolver->resolve($argument, $fixture, $fixtureSet);

                $fixtureSet = $result->getSet();
                $arguments[$index] = $result->getValue();
            }
        }
        
        return [$arguments, $fixtureSet];
    }
}
