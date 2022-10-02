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
use Traversable;

final class MethodCallBag implements IteratorAggregate, Countable
{
    /**
     * @var MethodCallInterface[]
     */
    private $methodCalls = [];

    public function with(MethodCallInterface $methodCall): self
    {
        $clone = clone $this;
        $clone->methodCalls[] = $methodCall;

        return $clone;
    }

    /**
     * Creates a new instance to which the given properties have been merged. In case of conflicts, the existing values
     * are kept.
     */
    public function mergeWith(self $methodCallsBag): self
    {
        $clone = clone $methodCallsBag;
        foreach ($this->methodCalls as $methodCall) {
            $clone->methodCalls[] = $methodCall;
        }

        return $clone;
    }

    public function isEmpty(): bool
    {
        return [] === $this->methodCalls;
    }
    
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->methodCalls));
    }
    
    public function count(): int
    {
        return count($this->methodCalls);
    }
}
