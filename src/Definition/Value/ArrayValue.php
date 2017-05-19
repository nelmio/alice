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
        return deep_clone($this->values);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return var_export($this->values, true);
    }
}
