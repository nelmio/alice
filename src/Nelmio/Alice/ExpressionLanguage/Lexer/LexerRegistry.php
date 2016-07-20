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

final class LexerRegistry implements LexerInterface
{
    /**
     * @var LexerInterface[]
     */
    private $lexers;

    /**
     * @param LexerInterface[] $lexers
     */
    public function __construct(array $lexers)
    {
        $this->lexers = (function (LexerInterface ...$lexers) { return $lexers; })(...$lexers);
    }

    /**
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        $lastException = null;
        foreach ($this->lexers as $lexer) {
            try {
                return $lexer->lex($value);
            } catch (LexException $exception) {
                $lastException = $exception;
                // continue (try the next one)
            }
        }

        throw LexException::create($value, 0, $lastException);
    }
}
