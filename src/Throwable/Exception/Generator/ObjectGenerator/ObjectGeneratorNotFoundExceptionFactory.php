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

namespace Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator;

/**
 * @private
 */
final class ObjectGeneratorNotFoundExceptionFactory
{
    public static function createUnexpectedCall(string $method): ObjectGeneratorNotFoundException
    {
        return new ObjectGeneratorNotFoundException(
            sprintf(
                'Expected method "%s" to be called only if it has a generator.',
                $method
            )
        );
    }

    private function __construct()
    {
    }
}
