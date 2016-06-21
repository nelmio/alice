<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception;

class ObjectNotFoundException extends \UnexpectedValueException
{
    public static function create(string $id, string $className)
    {
        return new static(
            sprintf(
                'Could not find the object "%s" of the class "%s".',
                $id,
                $className
            )
        );
    }
}
