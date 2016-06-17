<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Exception\Resolver\CircularReferenceException;

/**
 * Counter to keep track of the parameters being resolved and detect circular references.
 */
final class ResolvingContext
{
    /**
     * @var array
     */
    private $resolving;

    public function __construct(string $key = null)
    {
        $this->resolving = isset($key) ? $this->add([], $key) : [];
    }

    /**
     * Creates a new instance from the given one and ensure it has the given key. If the key is already present, will
     * not increment the counter (unlike the ::with() method).
     *
     * @param ResolvingContext|null $resolving
     * @param string                $key
     *
     * @return self
     */
    public static function createFrom(self $resolving = null, string $key): self
    {
        $instance = null === $resolving ? new self() : clone $resolving;
        if (false === $instance->has($key)) {
            $instance = $instance->with($key);
        }

        return $instance;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->resolving);
    }

    /**
     * @param string $key Parameter key
     *
     * @return self
     */
    public function with(string $key): self
    {
        $clone = clone $this;
        $clone->resolving = $this->add($clone->resolving, $key);

        return $clone;
    }

    /**
     * @param string $key Parameter key
     *
     * @throws CircularReferenceException
     */
    public function checkForCircularReference(string $key)
    {
        if (true === $this->has($key) && 1 < $this->resolving[$key]) {
            throw CircularReferenceException::createForParameter($key, $this->resolving);
        }
    }

    private function add(array $resolving, string $key): array
    {
        $resolving[$key] = array_key_exists($key, $resolving)
            ? $resolving[$key] + 1
            : 1
        ;

        return $resolving;
    }
}
