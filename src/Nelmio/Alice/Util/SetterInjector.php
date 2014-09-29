<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Util;

class SetterInjector
{
    public static function inject($object, $setter, $value)
    {
        if (method_exists($object, $setter)) {
            $object->$setter($value);
        }
    }
}
