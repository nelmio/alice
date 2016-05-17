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

use Nelmio\Alice\Exception\ParameterNotFound;

final class ParameterBag implements \IteratorAggregate
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
     * will NOT be overridden.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function with(string $key, $value): self
    {
        $clone = clone $this;
        if (false === $clone->has($key)) {
            $clone->parameters[$key] = $value;
        }

        return $clone;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * @param string $key
     *
     * @throws ParameterNotFound
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        throw ParameterNotFound::create($key);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
