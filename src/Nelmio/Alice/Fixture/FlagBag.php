<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

final class FlagBag implements \IteratorAggregate, \Countable
{
    /**
     * @var FlagInterface[]
     */
    private $flags = [];
    
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Creates a new instance of the bag with the given flag. If a flag with the same identifier already exists, the
     * existing value will be replaced.
     *
     * @param FlagInterface $flag
     *
     * @return FlagBag
     */
    public function with(FlagInterface $flag): self
    {
        $clone = clone $this;
        $clone->flags[$flag->__toString()] = $flag;
        
        return $clone;
    }

    /**
     * Creates a new instance with the two bags merged together. If some flags overlaps, the existing one are
     * overridden.
     *
     * @param self $flags
     *
     * @return self
     */
    public function mergeWith(self $flags): self
    {
        $clone = clone $this;
        foreach ($flags as $flag) {
            $clone = $clone->with($flag);
        }

        return $clone;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->flags);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->flags);
    }
}
