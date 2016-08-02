<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Entity;

class StdClassFactory
{
    public static function create(array $attributes = []): \stdClass
    {
        $instance = new \stdClass();
        foreach ($attributes as $attribute => $value) {
            $instance->$attribute = $value;
        }

        return $instance;
    }
}
