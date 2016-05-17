<?php

/**
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

use Nelmio\Alice\Exception\ParameterNotFound;

final class ParameterBag
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
     * @param mixed[] $parameters Keys/values pair of parameters. Existing parameters are not overridden.
     *
     * @return ParameterBag
     */
    public function with(array $parameters): self
    {
        $clone = clone $this;
        $clone->parameters = array_merge($parameters, $this->parameters);

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

        throw new ParameterNotFound(sprintf('No parameter with the key "%s" found.', $key));
    }
}
