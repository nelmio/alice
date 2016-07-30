<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class StringArrayTokenParser extends AbstractChainableParserAwareParser
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::STRING_ARRAY_TYPE;
    }

    /**
     * Parses '<{paramKey}>', '<{nested_<{param}>}>'.
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): array
    {
        parent::parse($token);

        $value = $token->getValue();
        $elements = substr($value, 1, strlen($value) - 2);

        return $this->parseElements($this->parser, $elements);
    }

    private function parseElements(ParserInterface $parser, string $arguments)
    {
        if ('' === $arguments) {
            return null;
        }

        $arguments = preg_split('/\s*,\s*/', $arguments);
        foreach ($arguments as $index => $argument) {
            $arguments[$index] = $parser->parse($argument);
        }

        return $arguments;
    }
}
