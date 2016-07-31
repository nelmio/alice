<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Lexer;

use Nelmio\Alice\Exception\ExpressionLanguage\LexException;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class EmptyValueLexer implements LexerInterface
{
    use NotClonableTrait;

    /**
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        if ('' === $value) {
            return [new Token('', new TokenType(TokenType::STRING_TYPE))];
        }

        throw LexException::create($value);
    }
}
