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

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\GenerationThrowable;
use Nelmio\Alice\Throwable\ResolutionThrowable;

class UnresolvableValueDuringGenerationException extends UnresolvableValueException implements GenerationThrowable
{
    /**
     * @return static
     */
    public static function createFromResolutionThrowable(ResolutionThrowable $previous, int $code = 0)
    {
        return new static('Could not resolve value during the generation process.', $code, $previous);
    }
}
