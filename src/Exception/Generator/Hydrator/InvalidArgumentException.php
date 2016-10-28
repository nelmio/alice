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
use Nelmio\Alice\Throwable\HydrationThrowable;

/**
 * Unlike most InvalidArgumentException thrown, this one is not a LogicException as in the context of hydration, this
 * exception can be thrown because the wrong accessor is used and hence should be catchable to try another accessor
 * for example.
 */
class InvalidArgumentException extends \RuntimeException implements HydrationThrowable
{
    /**
     * @inheritdoc
     */
    public static function create(ObjectInterface $object, Property $property, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Invalid value given for the property "%s" of the object "%s" (class: %s).',
                $property->getName(),
                $object->getId(),
                get_class($object->getInstance())
            ),
            $code,
            $previous
        );
    }
}
