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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class IdentityTokenParser implements ChainableTokenParserInterface, ParserAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableTokenParserInterface
     */
    private $decoratedTokenParser;

    /**
     * @var FunctionTokenizer
     */
    private $tokenizer;

    public function __construct(ChainableTokenParserInterface $decoratedTokenParser, ParserInterface $parser = null)
    {
        if (null !== $parser && $decoratedTokenParser instanceof ParserAwareInterface) {
            $decoratedTokenParser = $decoratedTokenParser->withParser($parser);
        }

        $this->decoratedTokenParser = $decoratedTokenParser;
        $this->tokenizer = new FunctionTokenizer();
    }
    
    public function withParser(ParserInterface $parser): self
    {
        return new static($this->decoratedTokenParser, $parser);
    }
    
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::IDENTITY_TYPE;
    }

    /**
     * Parses expressions such as '<(something)>'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        $value = $this->tokenizer->detokenize($token->getValue());
        $realValue = preg_replace('/^<\((.*)\)>$/s', '<identity($1)>', $value);

        return $this->decoratedTokenParser->parse(
            new Token($realValue, new TokenType(TokenType::FUNCTION_TYPE))
        );
    }
}
