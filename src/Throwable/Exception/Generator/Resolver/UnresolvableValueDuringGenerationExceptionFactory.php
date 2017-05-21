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

use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @private
 */
final class UnresolvableValueDuringGenerationExceptionFactory
{
    public static function createFromResolutionThrowable(ResolutionThrowable $previous): UnresolvableValueDuringGenerationException
    {
        return new UnresolvableValueDuringGenerationException(
            'Could not resolve value during the generation process.',
            0,
            $previous
        );
    }

    private function __construct()
    {
    }
}
