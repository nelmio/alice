<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Throwable\Exception;

/**
 * @private
 */
final class InvalidSeedExceptionFactory
{
    public static function createForNullSeed(): InvalidSeedException
    {
        return new InvalidSeedException('Cannot geenrate a cache key when the generation seed is null.');
    }
}
