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

namespace Nelmio\Alice\Exception;

final class LogicExceptionFactory
{
    public static function createForUncallableMethod(string $method): \LogicException
    {
        return new \LogicException(
            sprintf(
                'By its nature, "%s()" should not be called.',
                $method
            )
        );
    }

    public static function createForCannotDenormalizerForChainableFixtureBuilderDenormalizer(string $method): \LogicException
    {
        return new \LogicException(
            sprintf(
                'As a chainable denormalizer, "%s" should be called only if "::canDenormalize() returns true. Got '
                .'false instead.',
                __METHOD__
            )
        );
    }
}
