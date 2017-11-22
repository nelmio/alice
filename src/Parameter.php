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
     * @param mixed  $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = deep_clone($value);
    }

    public function withValue($value): self
    {
        $clone = clone $this;
        $clone->value = deep_clone($value);
        
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
        return deep_clone($this->value);
    }
}
