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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceExceptionFactory;

/**
 * Counter to keep track of the parameters, fixtures etc. being resolved and detect circular references.
 */
final class ResolvingContext
{
    /**
     * @var array
     */
    private $resolving = [];

    public function __construct(string $key = null)
    {
        if (null !== $key) {
            $this->add($key);
        }
    }

    /**
     * Returns the existing instance if is an object or create a new one otherwise. It also ensure that the key will be
     * added also it won't increment the counter if already present.
     */
    public static function createFrom(self $resolving = null, string $key): self
    {
        $instance = $resolving ?? new self();
        if (false === $instance->has($key)) {
            $instance->add($key);
        }

        return $instance;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->resolving);
    }

    public function add(string $key): void
    {
        $this->resolving[$key] = array_key_exists($key, $this->resolving)
            ? $this->resolving[$key] + 1
            : 1
        ;
    }

    /**
     * @throws CircularReferenceException
     */
    public function checkForCircularReference(string $key): void
    {
        if (true === $this->has($key) && 1 < $this->resolving[$key]) {
            throw CircularReferenceExceptionFactory::createForParameter($key, $this->resolving);
        }
    }
}
