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

use BadMethodCallException;

/**
 * @private
 */
final class BadMethodCallExceptionFactory
{
    public static function createForUnknownMethod(string $method): BadMethodCallException
    {
        return new BadMethodCallException(
            sprintf(
                'Unknown method "%s".',
                $method
            )
        );
    }

    private function __construct()
    {
    }
}
