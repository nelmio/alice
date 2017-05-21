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

namespace Nelmio\Alice\Throwable\Exception\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\ObjectInterface;
use Throwable;

/**
 * @private
 */
final class HydrationExceptionFactory
{
    public static function create(
        ObjectInterface $object,
        Property $property,
        int $code,
        Throwable $previous
    ): HydrationException {
        return new HydrationException(
            sprintf(
                'Could not hydrate the property "%s" of the object "%s" (class: %s).',
                $property->getName(),
                $object->getId(),
                get_class($object->getInstance())
            ),
            $code,
            $previous
        );
    }

    public static function createForInaccessibleProperty(
        ObjectInterface $object,
        Property $property,
        int $code = 0,
        Throwable $previous = null
    ): InaccessiblePropertyException {
        return new InaccessiblePropertyException(
            sprintf(
                'Could not access to the property "%s" of the object "%s" (class: %s).',
                $property->getName(),
                $object->getId(),
                get_class($object->getInstance())
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidProperty(
        ObjectInterface $object,
        Property $property,
        int $code = 0,
        Throwable $previous = null
    ): InvalidArgumentException {
        return new InvalidArgumentException(
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

    public static function createForCouldNotHydrateObjectWithProperty(
        ObjectInterface $object,
        Property $property,
        int $code = 0,
        Throwable $previous = null
    ): NoSuchPropertyException {
        return new NoSuchPropertyException(
            sprintf(
                'Could not hydrate the property "%s" of the object "%s" (class: %s).',
                $property->getName(),
                $object->getId(),
                get_class($object->getInstance())
            ),
            $code,
            $previous
        );
    }

    private function __construct()
    {
    }
}
