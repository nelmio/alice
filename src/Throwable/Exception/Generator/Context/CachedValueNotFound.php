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

namespace Nelmio\Alice\Throwable\Exception\Generator\Context;

use Nelmio\Alice\Throwable\GenerationThrowable;

final class CachedValueNotFound extends \RuntimeException implements GenerationThrowable
{
    public static function create(string $key): self
    {
        return new self(
            sprintf(
                'No value with the key "%s" was found in the cache.',
                $key
            )
        );
    }
}
