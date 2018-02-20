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
use Nelmio\Alice\IsAServiceTrait;

/**
 * Resolver handing over the resolution to a decorated resolver to then remove any object which has a fixture. This
 * ensures that when a fixture will refer to another, e.g. "@dummy", it will pick the object "dummy" described by
 * the fixture and not the injected one if it exists.
 */
final class RemoveConflictingObjectsResolver implements FixtureSetResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var FixtureSetResolverInterface
     */
    private $resolver;

    public function __construct(FixtureSetResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(FixtureSet $unresolvedFixtureSet): ResolvedFixtureSet
    {
        $resolvedFixtureSet = $this->resolver->resolve($unresolvedFixtureSet);

        $fixtures = $resolvedFixtureSet->getFixtures();
        $objects = $resolvedFixtureSet->getObjects();

        foreach ($fixtures as $fixture) {
            if ($objects->has($fixture)) {
                $objects = $objects->without($fixture);
            }
        }

        return $resolvedFixtureSet->withObjects($objects);
    }
}
