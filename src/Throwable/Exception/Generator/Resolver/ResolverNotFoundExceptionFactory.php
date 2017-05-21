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

use Nelmio\Alice\Definition\ValueInterface;

/**
 * @private
 */
final class ResolverNotFoundExceptionFactory
{
    public static function createForParameter(string $parameterKey): ResolverNotFoundException
    {
        return new ResolverNotFoundException(
            sprintf(
                'No resolver found to resolve parameter "%s".',
                $parameterKey
            )
        );
    }

    public static function createForValue(ValueInterface $value): ResolverNotFoundException
    {
        return new ResolverNotFoundException(
            sprintf(
                'No resolver found to resolve value "%s".',
                $value
            )
        );
    }

    public static function createUnexpectedCall(string $method): ResolverNotFoundException
    {
        return new ResolverNotFoundException(
            sprintf(
                'Expected method "%s" to be called only if it has a resolver.',
                $method
            )
        );
    }

    private function __construct()
    {
    }
}
