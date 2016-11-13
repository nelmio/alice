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

class ParameterNotFoundException extends \UnexpectedValueException
{
    public static function create(string $key, int $code = 0, \Throwable $previous = null): self
    {
        return new static(
            sprintf(
                'Could not find the parameter "%s".',
                $key
            ),
            $code,
            $previous
        );
    }

    public static function createForWhenResolvingParameter(string $key, Parameter $parameter): self
    {
        return sprintf(
            'Could not find the parameter "%s" when resolving "%s".',
            $key,
            $parameter->getKey()
        );
    }
}
