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

namespace Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;

class ParserNotFoundException extends \LogicException
{
    /**
     * @return static
     */
    public static function create(Token $token, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'No suitable token parser found to handle the token "%s" (type: %s).',
                $token->getValue(),
                $token->getType()
            ),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createUnexpectedCall(string $method, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a parser.',
                $method
            ),
            $code,
            $previous
        );
    }
}
