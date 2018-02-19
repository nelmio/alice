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

use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\NestedValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;

/**
 * @internal
 */
final class TolerantFunctionTokenParser extends AbstractChainableParserAwareParser
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = '/(\)>)(.*?)</';

    /**
     * @var ChainableTokenParserInterface
     */
    private $functionTokenParser;

    /**
     * @inheritdoc
     */
    public function __construct(ChainableTokenParserInterface $functionTokenParser, ParserInterface $parser = null)
    {
        parent::__construct($parser);

        if (null !== $parser && $functionTokenParser instanceof ParserAwareInterface) {
            $functionTokenParser = $functionTokenParser->withParser($parser);
        }

        $this->functionTokenParser = $functionTokenParser;
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser): self
    {
        return new self($this->functionTokenParser, $parser);
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::FUNCTION_TYPE;
    }

    /**
     * Handle cases like '<f()> <g()>' by trying to break down the token.
     *
     * {@inheritdoc}
     *
     * @throws LexException
     * @TODO: handle redundant ListValue tokens
     *
     * @return FunctionCallValue|ListValue
     */
    public function parse(Token $token)
    {
        parent::parse($token);

        $split = preg_split(self::REGEX, $token->getValue(), 2, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
        $splitSize = count($split);
        if (1 === $splitSize) {
            return $this->functionTokenParser->parse($token);
        }

        if (3 !== count($split) && 4 !== count($split)) {
            throw ExpressionLanguageExceptionFactory::createForCouldNotLexValue($token->getValue());
        }

        $values = [
            $this->parser->parse($split[0].$split[1]),
        ];
        if (3 === $splitSize) {
            $values[] = $this->parser->parse('<'.$split[2]);
        }

        if (4 === $splitSize) {
            $values[] = $this->parser->parse($split[2]);
            $values[] = $this->parser->parse('<'.$split[3]);
        }

        return $this->mergeValues($values);
    }

    private function mergeValues(array $values): NestedValue
    {
        $parsedValues = [];

        foreach ($values as $value) {
            if (false === ($value instanceof ListValue || $value instanceof NestedValue)) {
                $parsedValues[] = $value;

                continue;
            }

            $valuesList = $value->getValue();
            foreach ($valuesList as $value) {
                $parsedValues[] = $value;
            }
        }

        return new NestedValue($parsedValues);
    }
}
