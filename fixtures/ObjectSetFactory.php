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

namespace Nelmio\Alice;

class ObjectSetFactory
{
    public static function create(ParameterBag $parameters = null, ObjectBag $objects = null): ObjectSet
    {
        return new ObjectSet(
            $parameters ?? new ParameterBag(),
            $objects ?? new ObjectBag()
        );
    }
}
