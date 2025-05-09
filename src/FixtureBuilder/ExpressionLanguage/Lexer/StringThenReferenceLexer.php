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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @internal
 */
final class StringThenReferenceLexer implements LexerInterface
{
    use IsAServiceTrait;

    /**
     * @var LexerInterface
     */
    private $decoratedLexer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->decoratedLexer = $decoratedLexer;
    }

    public function lex(string $value): array
    {
        $tokens = $this->decoratedLexer->lex($value);

        $idx = 0;
        do {
            $token = $tokens[$idx] ?? false;
            $nextToken = $tokens[$idx + 1] ?? false;

            if (!($token instanceof Token) || !($nextToken instanceof Token)) {
                continue;
            }

            if (TokenType::STRING_TYPE === $token->getType()
                && TokenType::SIMPLE_REFERENCE_TYPE === $nextToken->getType()
                && '' !== mb_trim($token->getValue())
                && !in_array(mb_substr($token->getValue(), -1), [' ', '\''], true)
            ) {
                array_splice($tokens, $idx, 2, [
                    new Token(
                        $token->getValue().$nextToken->getValue(),
                        new TokenType(TokenType::STRING_TYPE),
                    ),
                ]);
            }

            ++$idx;
        } while (false !== $token && false !== $nextToken);

        return $tokens;
    }
}
