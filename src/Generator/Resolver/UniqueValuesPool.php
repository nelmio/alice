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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

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

        return $this->hasIdenticalValueInCache($valueId, $value->getValue());
    }

    private function hasIdenticalValueInCache(string $valueId, $value): bool
    {
        $cachedValues = $this->pool[$valueId];
        foreach ($cachedValues as $cachedValue) {
            if ($this->isIdentical($cachedValue, $value)) {
                return true;
            }
        }

        return false;
    }

    private function isIdentical($val1, $val2): bool
    {
        if (gettype($val1) !== gettype($val2)) {
            return false;
        }

        if (is_object($val1)) {
            $comparator = Factory::getInstance()->getComparatorFor($val1, $val2);

            try {
                $comparator->assertEquals($val1, $val2);

                return true;
            } catch (ComparisonFailure $failure) {
                return false;
            }
        }

        if (is_scalar($val1) || null === $val1) {
            return $val1 === $val2;
        }

        foreach ($val1 as $key => $item) {
            if (is_string($key)) {
                if (false === $this->isIdentical($item, $val2[$key])) {
                    return false;
                }

                continue;
            }

            if (false === $this->arrayHasValue($item, $val2)) {
                return false;
            }
        }

        return true;
    }

    private function arrayHasValue($value, array $array): bool
    {
        foreach ($array as $arrayValue) {
            if ($this->isIdentical($arrayValue, $value)) {
                return true;
            }
        }

        return false;
    }

    public function add(UniqueValue $value): void
    {
        $valueId = $value->getId();
        if (false === array_key_exists($valueId, $this->pool)) {
            $this->pool[$valueId] = [];
        }

        $this->pool[$valueId][] = $value->getValue();
    }
}
