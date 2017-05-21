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

namespace Nelmio\Alice\Throwable\Exception;

/**
 * @private
 */
final class ObjectNotFoundExceptionFactory
{
    public static function create(string $id, string $className): ObjectNotFoundException
    {
        return new ObjectNotFoundException(
            sprintf(
                'Could not find the object "%s" of the class "%s".',
                $id,
                $className
            )
        );
    }

    private function __construct()
    {
    }
}
