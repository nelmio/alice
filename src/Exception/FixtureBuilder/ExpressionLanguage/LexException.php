<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\Throwable\ParseThrowable;

class LexException extends \Exception implements ParseThrowable
{
    public static function create(string $value, int $code = 0, \Exception $previous = null)
    {
        return new static(
            sprintf(
                'Could not lex the value "%s".',
                $value
            ),
            $code,
            $previous
        );
    }
}
