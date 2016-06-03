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

use Nelmio\Alice\Exception\FixtureNotFoundException;

final class UnresolvedFixtureBag implements \IteratorAggregate
{
    /**
     * @var UnresolvedFixtureInterface[]
     */
    private $fixtures = [];

    /**
     * Returns a new instance to which the given fixture has been added. In case of reference conflicts, the old value
     * is overridden.
     * 
     * @param UnresolvedFixtureInterface $fixture
     *
     * @return self
     */
    public function with(UnresolvedFixtureInterface $fixture): self
    {
        $clone = clone $this;
        $clone->fixtures[$fixture->getClassName().$fixture->getReference()] = $fixture;
        
        return $clone;
    }

    /**
     * Convenience method for applying ::with() to a collection.
     * 
     * @param self $fixtures
     *
     * @return self
     */
    public function mergeWith(self $fixtures): self
    {
        $clone = clone $this;
        foreach ($fixtures as $fixture) {
            /* @var UnresolvedFixtureInterface $fixture */
            $clone->fixtures[$fixture->getClassName().$fixture->getReference()] = $fixture;
        }
        
        return $clone;
    }

    /**
     * @param string $fullReference
     *
     * @throws FixtureNotFoundException
     * 
     * @return UnresolvedFixtureInterface
     */
    public function get(string $fullReference): UnresolvedFixtureInterface
    {
        if (array_key_exists($fullReference, $this->fixtures)) {
            return $this->fixtures[$fullReference];
        }
        
        throw new FixtureNotFoundException($fullReference);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fixtures);
    }
}
