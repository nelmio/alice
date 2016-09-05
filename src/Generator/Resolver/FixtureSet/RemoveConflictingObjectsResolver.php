<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\FixtureSet;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\Resolver\FixtureBagResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class RemoveConflictingObjectsResolver implements FixtureSetResolverInterface
{
    use NotClonableTrait;

    /**
     * @var FixtureBagResolverInterface
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
        if (count($fixtures) < count($objects)) {
            foreach ($fixtures as $fixture) {
                if ($objects->has($fixture)) {
                    $objects = $objects->without($fixture);
                }
            }

            return $resolvedFixtureSet->withObjects($objects);
        }
        foreach ($objects as $object) {
            $objectId = $object->getId();
            if ($fixtures->has($objectId)) {
                $objects = $objects->without($object);
            }
        }

        return $resolvedFixtureSet->withObjects($objects);
    }
}
