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

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\FixtureInterface;
use Throwable;

/**
 * @private
 */
final class NoSuchPropertyExceptionFactory
{
    public static function createForFixture(
        FixtureInterface $fixture,
        FixturePropertyValue $value,
        int $code = 0,
        Throwable $previous = null
    ): NoSuchPropertyException {
        return new NoSuchPropertyException(
            sprintf(
                'Could not find the property "%s" of the object "%s" (class: %s).',
                $value->getProperty(),
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
