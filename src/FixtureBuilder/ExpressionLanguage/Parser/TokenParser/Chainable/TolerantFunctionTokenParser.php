<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\NestedValue;
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class TolerantFunctionTokenParser extends AbstractChainableParserAwareParser
{
    use NotClonableTrait;

    /** @internal */
    const REGEX = '/(\)>)(\ *<)/';

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
    public function withParser(ParserInterface $parser)
    {
        return new self($this->functionTokenParser, $parser);
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::FUNCTION_TYPE;
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

        try {
            return $this->functionTokenParser->parse($token);
        } catch (LexException $exception) {
            // Continue
        }

        $split = preg_split(self::REGEX, $token->getValue(), 2, PREG_SPLIT_DELIM_CAPTURE);
        if (4 !== count($split)) {
            throw $exception;
        }

        $firstValue = $this->parser->parse($split[0].$split[1]);
        $secondValue = $this->parser->parse($split[2].$split[3]);

        return $this->mergeValues($firstValue, $secondValue);
    }

    private function mergeValues($firstValue, $secondValue): NestedValue
    {
        $parsedValues = [$firstValue];

        $secondValue = ($secondValue instanceof ListValue || $secondValue instanceof NestedValue)
            ? $secondValue->getValue()
            : [$secondValue]
        ;
        foreach ($secondValue as $value) {
            $parsedValues[] = $value;
        }

        return new NestedValue($parsedValues);
    }
}
