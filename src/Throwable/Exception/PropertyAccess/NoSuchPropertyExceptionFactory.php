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

namespace Nelmio\Alice\Throwable\Exception\PropertyAccess;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

/**
 * @private
 */
final class NoSuchPropertyExceptionFactory
{
    public static function createForUnreadablePropertyFromStdClass(string $propertyPath): NoSuchPropertyException
    {
        return new NoSuchPropertyException(
            sprintf(
                'Cannot read property "%s" from stdClass.',
                $propertyPath
            )
        );
    }

    private function __construct()
    {
    }
}
