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
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\MalformedFunctionException;

/**
 * @internal
 */
final class FunctionLexer implements LexerInterface
{
    use IsAServiceTrait;

    /** @private */
    const DELIMITER= '___##';

    /**
     * @var LexerInterface
     */
    private $decoratedLexer;

    /**
     * @var FunctionTokenizer
     */
    private $functionTokenizer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->decoratedLexer = $decoratedLexer;
        $this->functionTokenizer = new FunctionTokenizer();
    }

    /**
     * {@inheritdoc}
     *
     * @throws MalformedFunctionException
     */
    public function lex(string $value): array
    {
        if (false === $this->functionTokenizer->isTokenized($value)) {
            $value = $this->functionTokenizer->tokenize($value);
        }

        return $this->decoratedLexer->lex($value);
    }
}
