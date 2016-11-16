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
final class EmptyValueLexer implements LexerInterface
{
    use IsAServiceTrait;

    private $lexer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->lexer = $decoratedLexer;
    }

    /**
     * {@inheritdoc}
     */
    public function lex(string $value): array
    {
        if ('' === $value) {
            return [new Token('', new TokenType(TokenType::STRING_TYPE))];
        }

        return $this->lexer->lex($value);
    }
}
