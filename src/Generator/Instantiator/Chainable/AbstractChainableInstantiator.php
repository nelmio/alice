<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;

abstract class AbstractChainableInstantiator implements ChainableInstantiatorInterface
{
    /**
     * @inheritdoc
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $instance = $this->createInstance($fixture);
        $objects = $fixtureSet->getObjects()->with(
            new SimpleObject(
                $fixture->getId(),
                $instance
            )
        );

        return new ResolvedFixtureSet(
            $fixtureSet->getParameters(),
            $fixtureSet->getFixtures(),
            $objects
        );
    }

    /**
     * @param FixtureInterface $fixture
     *
     * @return object
     */
    abstract protected function createInstance(FixtureInterface $fixture);
}
