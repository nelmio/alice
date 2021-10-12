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

use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use TypeError;

/**
 * @internal
 */
final class StringArrayTokenParser extends AbstractChainableParserAwareParser
{
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::STRING_ARRAY_TYPE;
    }

    /**
     * Parses '<{paramKey}>', '<{nested_<{param}>}>'.
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): ValueInterface
    {
        parent::parse($token);

        $value = $token->getValue();

        try {
            $elements = substr($value, 1, -1);

            return new ArrayValue($this->parseElements($this->parser, $elements));
        } catch (TypeError $error) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $error);
        }
    }

    private function parseElements(ParserInterface $parser, string $elements)
    {
        if ('' === $elements) {
            return [];
        }

        $elements = preg_split('/\s*,\s*/', $elements);
        foreach ($elements as $index => $argument) {
            $elements[$index] = $parser->parse(trim($argument));
        }

        return $elements;
    }
}
