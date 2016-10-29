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

namespace Nelmio\Alice\Exception\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\HydrationThrowable;

class HydrationException extends \RuntimeException implements HydrationThrowable
{
    /**
     * @return static
     */
    public static function create(
        ObjectInterface $object,
        Property $property,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new static(
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
}
