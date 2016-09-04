<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\ObjectGenerator;

class ObjectGeneratorNotFoundException extends \LogicException
{
    /**
     * @return static
     */
    public static function createUnexpectedCall(string $method, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a generator.',
                $method
            ),
            $code,
            $previous
        );
    }
}
