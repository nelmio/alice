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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundExceptionFactory;
use Traversable;

/**
 * Value object containing a list of parameters.
 */
final class ParameterBag implements IteratorAggregate, Countable
{
    /**
     * @var mixed[]
     */
    private $parameters;

    /**
     * @param mixed[] $parameters Keys/values pair of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = deep_clone($parameters);
    }

    /**
     * Returns a new instance which will include the passed parameter. If a parameter with that key already exist, it
     * WILL NOT be overridden.
     */
    public function with(Parameter $parameter): self
    {
        $key = $parameter->getKey();

        $clone = clone $this;
        if (false === $clone->has($key)) {
            $clone->parameters[$key] = $parameter->getValue();
        }

        return $clone;
    }
    
    public function without(string $key): self
    {
        $clone = clone $this;
        unset($clone->parameters[$key]);
        
        return $clone;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * @throws ParameterNotFoundException
     */
    public function get(string $key)
    {
        if ($this->has($key)) {
            return deep_clone($this->parameters[$key]);
        }

        throw ParameterNotFoundExceptionFactory::create($key);
    }
    
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->parameters);
    }
    
    public function count(): int
    {
        return count($this->parameters);
    }

    public function toArray(): array
    {
        return $this->parameters;
    }
}
