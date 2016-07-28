<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;

/**
 * Class storing all the unique values.
 */
final class UniqueValuesPool
{
    private $pool = [];

    public function has(UniqueValue $value): bool
    {
        $valueId = $value->getId();
        if (false === array_key_exists($valueId, $this->pool)) {
            return false;
        }

        $realValue = $value->getValue();
        $cachedValues = $this->pool[$valueId];

        foreach ($cachedValues as $cachedValue) {
            if ((is_object($realValue) && $realValue == $cachedValue) || $realValue === $cachedValue) {
                return true;
            }
        }

        return false;
    }

    public function add(UniqueValue $value)
    {
        $valueId = $value->getId();
        if (false === array_key_exists($valueId, $this->pool)) {
            $this->pool[$valueId] = [];
        }

        $this->pool[$valueId][] = $value->getValue();
    }
}
