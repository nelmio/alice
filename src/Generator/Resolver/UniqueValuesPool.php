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

        if (is_object($val1) && is_object($val2)) {
            return $this->objectsAreIdentical($val1, $val2);
        }

        if (
            (is_object($val1) && !is_object($val2))
            || (!is_object($val1) && is_object($val2))
        ) {
            return false;
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

    public function add(UniqueValue $value)
    {
        $valueId = $value->getId();
        if (false === array_key_exists($valueId, $this->pool)) {
            $this->pool[$valueId] = [];
        }

        $this->pool[$valueId][] = $value->getValue();
    }

    private function objectsAreIdentical($o1, $o2): bool
    {
        //Only compare instances of the same class, all others are unequal
        if (!($o1 instanceof $o2)) {
            return false;
        }

        //Now prepare strict(er) comparison using reflection.
        $objReflection1 = new \ReflectionObject($o1);
        $objReflection2 = new \ReflectionObject($o2);

        //do compare internal objects in loose type mode
        //no chance of cyclic reference here
        if (!$objReflection1->isUserDefined()) {
            return $o1 == $o2;
        }

        //get properties, assumed to be equal between objects of the same class
        $arrProperties1 = $objReflection1->getProperties();

        //compare properties between objects
        //used to avoid infinite recursions due to cyclic redundancy (a->b->a like data modell)
        foreach ($arrProperties1 as $key=>$propName) {
            //loose-compare scalar properties (by value),
            if (!is_object($objReflection1->getProperty($propName->name))) {
                return $o1 == $o2;
            } elseif ($objReflection1->getProperty($propName->name) instanceof \ArrayAccess) {
                //recursive compare array-like objects, which may hold other sub-objects
                return $this->objectsAreIdentical($objReflection1->getProperty($propName->name), $objReflection2->getProperty($propName->name));
            } else {
                //strict-compare all other objects
                return $objReflection1->getProperty($propName->name) === $objReflection2->getProperty($propName->name);
            }
        }

        //All tests passed.
        return true;
    }
}
