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

final class NestedValue implements ValueInterface
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
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     *
     * @return ValueInterface[]|array
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
        return sprintf('(nested) %s', var_export($this->values, true));
    }
}
