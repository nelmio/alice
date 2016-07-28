<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\ExpressionLanguage;

use Nelmio\Alice\Throwable\ParseThrowable;

class ParseException extends \Exception implements ParseThrowable
{
    public static function create(string $value)
    {
        return new static(
            sprintf(
                'Could not lex the value "%s".',
                $value
            )
        );
    }
}
