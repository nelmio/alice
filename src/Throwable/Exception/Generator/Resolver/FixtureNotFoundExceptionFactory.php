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
final class FixtureNotFoundExceptionFactory
{
    public static function create(string $id): FixtureNotFoundException
    {
        return new FixtureNotFoundException(
            sprintf(
                'Could not find the fixture "%s".',
                $id
            )
        );
    }

    private function __construct()
    {
    }
}
