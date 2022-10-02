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

namespace Nelmio\Alice\Definition;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use function Nelmio\Alice\deep_clone;
use Traversable;

/**
 * Collection of flags.
 */
final class FlagBag implements IteratorAggregate, Countable
{
    /**
     * @var FlagInterface[]
     */
    private $flags = [];

    /**
     * @var string
     */
    private $key;

    /**
     * @param string $key String elements from which the flags come from stripped from its flags.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function withKey(string $key): self
    {
        $clone = clone ($this);
        $clone->key = $key;

        return $clone;
    }

    /**
     * Creates a new instance of the bag with the given flag. If a flag with the same identifier already exists, the
     * existing value will be replaced.
     */
    public function withFlag(FlagInterface $flag): self
    {
        $clone = clone ($this);
        $clone->flags[$flag->__toString()] = deep_clone($flag);

        return $clone;
    }

    /**
     * Creates a new instance with the two bags merged together.
     *
     * The original key is kept.
     *
     * @param bool $override If some flags overlaps, the existing one are overridden if the value is true, and left
     *                       untouched otherwise.
     */
    public function mergeWith(self $flags, bool $override = true): self
    {
        if ($override) {
            $clone = clone ($this);
            foreach ($flags as $flag) {
                /** @var FlagInterface $flag */
                $clone->flags[$flag->__toString()] = clone ($flag);
            }
        } else {
            $clone = clone ($flags);
            $clone->key = $this->key;
            foreach ($this as $flag) {
                /** @var FlagInterface $flag */
                $clone->flags[$flag->__toString()] = clone ($flag);
            }
        }

        return $clone;
    }

    public function getKey(): string
    {
        return $this->key;
    }
    
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->flags));
    }
    
    public function count(): int
    {
        return count($this->flags);
    }
}
