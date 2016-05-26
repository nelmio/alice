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

/**
 * Value object representing a parameter. The parameter may or not be already resolved.
 */
final class Parameter
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $key, $value)
    {
        if (1 !== preg_match('/[\p{L}\d\._\/]+/', $key)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid parameter key "%s". A parameter key can only be composed of letters, digits, periods, '
                    .'underscores and slahes.',
                    $key
                )
            );
        }

        $this->key = $key;
        $this->value = $value;
    }

    public function withValue($value): self
    {
        $clone = clone $this;
        $clone->value = $value;
        
        return $clone;
    }
    
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (is_object($this->value)) {
            return clone $this->value;
        }
        
        return $this->value;
    }

    public function __clone()
    {
        if (is_object($this->value)) {
            $this->value = clone $this->value;
        }
    }
}
