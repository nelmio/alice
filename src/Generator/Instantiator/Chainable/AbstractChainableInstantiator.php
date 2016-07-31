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
use Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Throwable\InstantiationThrowable;

abstract class AbstractChainableInstantiator implements ChainableInstantiatorInterface
{
    use NotClonableTrait;

    /**
     * {@inheritdoc}
     *
     * @throws InstantiationException
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        try {
            $instance = $this->createInstance($fixture);
        } catch (InstantiationThrowable $throwable) {
            throw $throwable;
        } catch (\Throwable $throwable) {
            throw InstantiationException::create($fixture, $throwable);
        }

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
