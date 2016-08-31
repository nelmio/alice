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

class ResolverNotFoundException extends \LogicException
{
    public static function createForParameter(string $parameterKey)
    {
        return new static(
            sprintf(
                'No resolver found to resolve parameter "%s".',
                $parameterKey
            )
        );
    }

    public static function createForValue(ValueInterface $value)
    {
        return new static(
            sprintf(
                'No resolver found to resolve value "%s".',
                $value
            )
        );
    }

    public static function createUnexpectedCall(string $method)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a resolver.',
                $method
            )
        );
    }
}
