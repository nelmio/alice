<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

class UniqueValueGenerationLimitReachedException extends \RuntimeException implements ResolutionThrowable
{
    public static function create(UniqueValue $value, int $limit): self
    {
        return new static(
            sprintf(
                'Could not generate a unique value after %d attempts for "%s".',
                $limit,
                $value->getId()
            )
        );
    }
}
