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

/**
 * Value object placeholder for '<current()>'.
 */
final class ValueForCurrentValue implements ValueInterface
{
    public function getValue(): string
    {
        return 'current';
    }
    
    public function __toString(): string
    {
        return 'current';
    }
}
