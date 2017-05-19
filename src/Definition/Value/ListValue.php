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

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use function Nelmio\Alice\deep_clone;

/**
 * Value representing a list of values which will be chained. For example '<foo()> <{bar}>' will be composed of a
 * function result, a static string (' ') and a parameter value.
 */
final class ListValue implements ValueInterface
{
    /**
     * @var ValueInterface[]|array
     */
    private $values;

    /**
     * @param ValueInterface[]|array $values
     */
    public function __construct(array $values)
    {
        $this->values = deep_clone($values);
    }

    /**
     * {@inheritdoc}
     *
     * @return ValueInterface[]|array
     */
    public function getValue(): array
    {
        return deep_clone($this->values);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return implode('', $this->values);
    }
}
