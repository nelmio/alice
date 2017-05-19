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

namespace Nelmio\Alice\Definition;

class SpecificationBagFactory
{
    public static function create(
        MethodCallInterface $constructor = null,
        PropertyBag $properties = null,
        MethodCallBag $calls = null
    ): SpecificationBag {
        return new SpecificationBag(
            $constructor,
            (null === $properties) ? new PropertyBag() : $properties,
            (null === $calls) ? new MethodCallBag() : $calls
        );
    }
}
