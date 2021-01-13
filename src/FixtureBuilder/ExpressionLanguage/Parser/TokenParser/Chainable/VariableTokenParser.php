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
use Nelmio\Alice\Definition\Value\ValueForCurrentValue;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use TypeError;

/**
 * @internal
 */
final class VariableTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::VARIABLE_TYPE;
    }

    /**
     * Parses expressions such as '$username'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        $variable = !is_string($token->getValue()) || $token->getValue() === ''
            ? false
            : substr($token->getValue(), 1);

        if ('current' === $variable) {
            return new FunctionCallValue(
                'current',
                [new ValueForCurrentValue()]
            );
        }

        try {
            return new VariableValue($variable);
        } catch (TypeError $error) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $error);
        }
    }
}
