<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\ObjectGenerator;

class ObjectGeneratorNotFoundException extends \LogicException
{
    public static function createUnexpectedCall(string $method)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a generator.',
                $method
            )
        );
    }
}
