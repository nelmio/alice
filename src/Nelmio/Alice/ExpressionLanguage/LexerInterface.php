<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage;

use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\Throwable\ParseThrowable;

interface LexerInterface
{
    /**
     * Converts a string into a sequence of tokens.
     *
     * @param string $value
     *
     * @throws ParseThrowable
     * 
     * @return Token[]
     */
    public function lex(string $value): array;
}
