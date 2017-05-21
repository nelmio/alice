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

use Nelmio\Alice\Definition\Value\UniqueValue;

/**
 * @private
 */
final class UniqueValueGenerationLimitReachedExceptionFactory
{
    public static function create(UniqueValue $value, int $limit): UniqueValueGenerationLimitReachedException
    {
        return new UniqueValueGenerationLimitReachedException(
            sprintf(
                'Could not generate a unique value after %d attempts for "%s".',
                $limit,
                $value->getId()
            )
        );
    }

    private function __construct()
    {
    }
}
