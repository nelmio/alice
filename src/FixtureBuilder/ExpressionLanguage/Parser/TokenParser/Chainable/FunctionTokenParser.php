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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class FunctionTokenParser implements ChainableTokenParserInterface, ParserAwareInterface
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = '/^<(?<function>(.|\r?\n)+?)\((?<arguments>.*)\)>$/';

    /**
     * @var ArgumentEscaper
     */
    private $argumentEscaper;

    /**
     * @var ParserInterface|null
     */
    protected $parser;

    public function __construct(ArgumentEscaper $argumentEscaper, ParserInterface $parser = null)
    {
        $this->argumentEscaper = $argumentEscaper;
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser)
    {
        return new static($this->argumentEscaper, $parser);
    }

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
        if (null === $this->parser) {
            throw ExpressionLanguageExceptionFactory::createForExpectedMethodCallOnlyIfHasAParser(__METHOD__);
        }

        if (1 !== preg_match(self::REGEX, $token->getValue(), $matches)) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $argumentEscaper = $this->argumentEscaper;

        $function = $matches['function'];
        if ('identity' === $function) {
            $value = preg_replace_callback(
                '/__ARG_TOKEN__[\da-z]{32}/',
                function (array $matches) use ($argumentEscaper): string {
                    return '\''.$argumentEscaper->unescape($matches[0]).'\'';
                },
                $matches['arguments']
            );

            $arguments = [new EvaluatedValue($value)];
        } elseif ('current' === $function) {
            $arguments = [new ValueForCurrentValue()];
        } else {
            $arguments = $this->parseArguments($this->parser, trim($matches['arguments']));
        }

        return new FunctionCallValue($function, $arguments);
    }

    private function parseArguments(ParserInterface $parser, string $argumentsString): array
    {
        if ('' === $argumentsString) {
            return [];
        }

        $argumentEscaper = $this->argumentEscaper;

        $escapedString = preg_replace_callback(
            '/\'(.*?)\'|"(.*?)"/',
            function (array $matches) use ($argumentEscaper): string {
                $string = end($matches);
                if (preg_match('/"(.*?)"/', reset($matches))) {
                    $lineBreak = \DIRECTORY_SEPARATOR === '\\' ? '\r\n' : '\n';
                    $string = str_replace($lineBreak, PHP_EOL, $string);
                }

                return $argumentEscaper->escape($string);
            },
            $argumentsString
        );

        $arguments = [];
        preg_match_all('/\[[^[]+\]|[^,\s]+/', $escapedString, $argumentsList);
        foreach ($argumentsList[0] as $index => $argument) {
            $argument = $parser->parse($argument);

            $arguments[$index] = $argument;
        }

        return $arguments;
    }
}
