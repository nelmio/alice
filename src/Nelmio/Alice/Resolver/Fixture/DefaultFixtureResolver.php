<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Fixture;

use Nelmio\Alice\FixtureResolutionResult;
use Nelmio\Alice\FixtureResolverInterface;
use Nelmio\Alice\Generator\FixtureGeneratorInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\FixtureGeneratorInterfaceAwareResolver;
use Nelmio\Alice\UnresolvedFixtureBag;
use Nelmio\Alice\UnresolvedFixtureInterface;

final class DefaultFixtureResolver implements FixtureResolverInterface, FixtureGeneratorInterfaceAwareResolver
{
    /**
     * @var FixtureGeneratorInterface|null
     */
    private $generator;

    /**
     * @inheritdoc
     */
    public function withGenerator(FixtureGeneratorInterface $generator): self
    {
        $clone = clone $this;
        $clone->generator = $generator;

        return $clone;
    }

    public function resolve(
        UnresolvedFixtureInterface $fixture,
        ParameterBag $parameters,
        UnresolvedFixtureBag $fixtures,
        ObjectBag $objects,
        ResolvingContext $context
    ): FixtureResolutionResult
    {
        //TODO
    }
}
