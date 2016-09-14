<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

final class ArrayValue implements ValueInterface
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
     * @return array The first element is the quantifier and the second the element.
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('array(%s)', implode('', $this->values));
    }
}
