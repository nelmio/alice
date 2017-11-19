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

/**
 * Value object representing a fixture property.
 */
final class Property
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ValueInterface|mixed
     */
    private $value;

    /**
     * @param string               $name  Fixture property name, e.g. 'username' (no flags expected)
     * @param ValueInterface|mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * param ValueInterface|mixed $value
     */
    public function withValue($value): self
    {
        $clone = clone $this;
        $clone->value = $value;

        return $clone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ValueInterface|mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
