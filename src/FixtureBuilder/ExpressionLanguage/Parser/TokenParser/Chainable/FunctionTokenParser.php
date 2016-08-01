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
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;

final class FunctionTokenParser extends AbstractChainableParserAwareParser
{
    /** @interval */
    const REGEX = '/^<(?<function>.+?)\((?<arguments>.*)\)>$/';

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::FUNCTION_TYPE;
    }

    /**
     * Parses expressions such as '<foo()>', '<foo(arg1, arg2)>'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token): FunctionCallValue
    {
        parent::parse($token);

        if (1 !== preg_match(self::REGEX, $token->getValue(), $matches)) {
            throw ParseException::createForToken($token);
        }

        $function = $matches['function'];
        $arguments = ('identity' === $function)
            ? [$matches['arguments']]
            : $this->parseArguments($this->parser, trim($matches['arguments']))
        ;

        return new FunctionCallValue($function, $arguments);
    }

    private function parseArguments(ParserInterface $parser, string $arguments)
    {
        if ('' === $arguments) {
            return null;
        }

        $arguments = preg_split('/\s*,\s*/', $arguments);
        foreach ($arguments as $index => $argument) {
            $arguments[$index] = $parser->parse(trim($argument));
        }

        return $arguments;
    }
}
