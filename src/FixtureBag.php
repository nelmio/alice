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

namespace Nelmio\Alice;

use Nelmio\Alice\Exception\FixtureNotFoundException;

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
        $clone->fixtures[$fixture->getId()] = clone $fixture;
        
        return $clone;
    }

    /**
     * Creates a new instance which will not contain the fixture of the given ID. Will still proceed even if such
     * fixture does not exist.
     *
     * @param FixtureInterface $fixture
     *
     * @return self
     */
    public function without(FixtureInterface $fixture): self
    {
        $clone = clone $this;
        unset($clone->fixtures[$fixture->getId()]);

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
     * @param string $id Fixture ID.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->fixtures);
    }

    /**
     * @param string $id Fixture ID.
     *
     * @throws FixtureNotFoundException
     *
     * @return FixtureInterface
     */
    public function get(string $id): FixtureInterface
    {
        if ($this->has($id)) {
            return clone $this->fixtures[$id];
        }

        throw FixtureNotFoundException::create($id);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fixtures);
    }

    public function toArray(): array
    {
        return $this->fixtures;
    }
}
