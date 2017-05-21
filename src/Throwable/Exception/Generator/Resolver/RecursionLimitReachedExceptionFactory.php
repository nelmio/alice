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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

/**
 * @private
 */
final class RecursionLimitReachedExceptionFactory
{
    public static function create(int $limit, string $key): RecursionLimitReachedException
    {
        return new RecursionLimitReachedException(
            sprintf(
                'Recursion limit (%d tries) reached while resolving the parameter "%s"',
                $limit,
                $key
            )
        );
    }

    private function __construct()
    {
    }
}
