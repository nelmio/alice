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
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\Fixture\ResolvingContext;
use Nelmio\Alice\Resolver\FixtureGeneratorInterfaceAwareResolver;
use Nelmio\Alice\SerializerInterface;
use Nelmio\Alice\UnresolvedFixtureBag;
use Nelmio\Alice\UnresolvedFixtureInterface;

final class ResolvedFixtureGenerator implements FixtureGeneratorInterface
{
    /**
     * @var FixtureResolverInterface
     */
    private $resolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(FixtureResolverInterface $resolver, SerializerInterface $serializer)
    {
        $this->resolver = ($resolver instanceof FixtureGeneratorInterfaceAwareResolver)
            ? $resolver->withGenerator($this)
            : $resolver
        ;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        UnresolvedFixtureInterface $fixture,
        ParameterBag $parameters,
        UnresolvedFixtureBag $fixtures,
        ObjectBag $injectedObjects,
        ResolvingContext $context
    ): ObjectBag
    {
        $resolutionResult = $this->resolver->resolve(
            $fixture,
            $parameters,
            $fixtures,
            $injectedObjects,
            $context
        );

        $objects = $injectedObjects->mergeWith($resolutionResult->getObjects());
        $object = $this->serializer->serialize($resolutionResult->getResolvedFixture());

        return $objects->with($object);
    }
}
