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

use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ValueForCurrentValue;
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;

/**
 * @internal
 */
final class FunctionTokenParser extends AbstractChainableParserAwareParser
{
    /** @private */
    const REGEX = '/^<(?<function>.+?)\((?<arguments>.*)\)>$/';

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::FUNCTION_TYPE;
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
        if ('identity' === $function) {
            $arguments = [new EvaluatedValue($matches['arguments'])];
        } elseif ('current' === $function) {
            $arguments = [new ValueForCurrentValue()];
        } else {
            $arguments = $this->parseArguments($this->parser, trim($matches['arguments']));
        }

        return new FunctionCallValue($function, $arguments);
    }

    private function parseArguments(ParserInterface $parser, string $arguments): array
    {
        if ('' === $arguments) {
            return [];
        }

        $arguments = preg_split('/\s*,\s*/', $arguments);
        foreach ($arguments as $index => $argument) {
            $argument = trim($argument);
            if (is_string($argument) && preg_match('/^\'(.*)\'$|^"(.*)"$/', $argument, $match)) {
                $argument = array_key_exists(2, $match) ? $match[2] : $match[1];
            }
            $argument = $parser->parse($argument);

            $arguments[$index] = $argument;
        }

        return $arguments;
    }
}
