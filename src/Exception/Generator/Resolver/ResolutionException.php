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

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

class ResolutionException extends \RuntimeException implements ResolutionThrowable
{
    public static function create(ValueInterface $value): self
    {
        return new static(
            sprintf(
                'Could not resolve value %d.',
                get_class($value)
            )
        );
    }
}
