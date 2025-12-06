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

namespace Nelmio\Alice\Symfony\PropertyAccess;

use Nelmio\Alice\NotCallableTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class FakePropertyAccessor implements PropertyAccessorInterface
{
    use NotCallableTrait;

    public function setValue(&$objectOrArray, $propertyPath, $value): never
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getValue($objectOrArray, $propertyPath): never
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function isWritable($objectOrArray, $propertyPath): never
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function isReadable($objectOrArray, $propertyPath): never
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
