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

use Nelmio\Alice\Exception\ParameterNotFoundException;

final class ParameterBag implements \IteratorAggregate, \Countable
{
    /**
     * @var mixed[]
     */
    private $parameters = [];

    /**
     * @param mixed[] $parameters Keys/values pair of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns a new instance which will include the passed parameter. If a parameter with that key already exist, it
     * WILL NOT be overridden.
     *
     * @param Parameter $parameter
     *
     * @return self
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
     * @param string $key
     *
     * @throws ParameterNotFoundException
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        throw ParameterNotFoundException::create($key);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->parameters);
    }
}
