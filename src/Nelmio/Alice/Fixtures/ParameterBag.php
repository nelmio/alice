<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

class ParameterBag
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * @param  string     $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->parameters[$key] : null;
    }

    /**
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }
}
