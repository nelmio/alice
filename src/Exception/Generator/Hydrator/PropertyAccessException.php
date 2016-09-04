<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\ObjectInterface;

class PropertyAccessException extends HydrationException
{
    /**
     * @inheritdoc
     */
    public static function create(
        ObjectInterface $object,
        Property $property,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new static(
            sprintf(
                'Could not access to the property "%s" of the object "%s" (class: %s).',
                $object->getReference(),
                $property->getName(),
                get_class($object->getInstance())
            ),
            $code,
            $previous
        );
    }
}
