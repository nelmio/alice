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

/**
 * Value object representing '<current()>'.
 */
final class ValueForCurrentValue implements ValueInterface
{
    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return 'current';
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return 'current';
    }
}
