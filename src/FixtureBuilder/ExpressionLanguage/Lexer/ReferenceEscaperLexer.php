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
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;

/**
 * Escapes references found in a string to avoid the user to have to manually escape references. For
 * example will automatically escape the @ in "email@example.com".
 *
 * @internal
 */
final class ReferenceEscaperLexer implements LexerInterface
{
    use IsAServiceTrait;

    /**
     * @var LexerInterface
     */
    private $lexer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->lexer = $decoratedLexer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        $escapedValue = preg_replace('/(\\p{L})@/', '$1\\@', $value);

        return $this->lexer->lex($escapedValue);
    }
}
