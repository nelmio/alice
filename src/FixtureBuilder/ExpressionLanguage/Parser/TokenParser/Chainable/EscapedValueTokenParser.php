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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionTokenizer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;

/**
 * @internal
 */
final class EscapedValueTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    /**
     * @var FunctionTokenizer
     */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new FunctionTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return TokenType::ESCAPED_VALUE_TYPE === $token->getType();
    }

    /**
     * Parses '<<', '@@'...
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): string
    {
        $value = $token->getValue();
        if ('' === $value) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        return $this->tokenizer->detokenize(substr($value, 1));
    }
}
