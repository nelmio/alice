<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

/**
 * Value object containing a list of fixtures.
 */
final class FixtureBag implements \IteratorAggregate
{
    /**
     * @var FixtureInterface[]
     */
    private $fixtures = [];

    /**
     * Creates a new instance which will have the given fixture. If a fixture of that id already existed, it will be
     * overridden.
     *
     * @param FixtureInterface $fixture
     *
     * @return self
     */
    public function with(FixtureInterface $fixture): self
    {
        $clone = clone $this;
        $clone->fixtures[$fixture->getId()] = $fixture;
        
        return $clone;
    }

    public function mergeWith(self $newFixtures): self
    {
        $clone = clone $this;
        foreach ($newFixtures as $fixture) {
            /** @var FixtureInterface $fixture */
            $clone->fixtures[$fixture->getId()] = $fixture;
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->fixtures));
    }
}
