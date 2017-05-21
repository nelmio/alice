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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\FixtureInterface;
use Throwable;

/**
 * @private
 */
final class NoSuchMethodExceptionFactory
{
    public static function createForFixture(
        FixtureInterface $fixture,
        FixtureMethodCallValue $value,
        int $code = 0,
        Throwable $previous = null
    ): NoSuchMethodException {
        return new NoSuchMethodException(
            sprintf(
                'Could not find the method "%s" of the object "%s" (class: %s).',
                $value->getFunctionCall()->getName(),
                $fixture->getId(),
                $fixture->getClassName()
            ),
            $code,
            $previous
        );
    }

    private function __construct()
    {
    }
}
