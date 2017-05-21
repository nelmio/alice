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

namespace Nelmio\Alice\Throwable\Exception;

use Nelmio\Alice\Parameter;

/**
 * @private
 */
final class ParameterNotFoundExceptionFactory
{
    public static function create(string $key): ParameterNotFoundException
    {
        return new ParameterNotFoundException(
            sprintf(
                'Could not find the parameter "%s".',
                $key
            )
        );
    }

    public static function createForWhenResolvingParameter(string $key, Parameter $parameter): ParameterNotFoundException
    {
        return new ParameterNotFoundException(
            sprintf(
                'Could not find the parameter "%s" when resolving "%s".',
                $key,
                $parameter->getKey()
            )
        );
    }

    private function __construct()
    {
    }
}
