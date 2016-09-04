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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

class ParseException extends \Exception implements ExpressionLanguageParseThrowable
{
    /**
     * @return static
     */
    public static function createForToken(Token $token, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not parse the token "%s" (type: %s).',
                $token->getValue(),
                $token->getType()->getValue()
            ),
            $code,
            $previous
        );
    }
}
