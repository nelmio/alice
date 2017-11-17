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

use InvalidArgumentException;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\ValueForCurrentValue;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;

/**
 * @internal
 */
final class VariableReferenceTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::VARIABLE_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as "@user$foo".
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): FixtureReferenceValue
    {
        $parts = explode('$', $token->getValue());

        $variable = $parts[1];

        try {
            return new FixtureReferenceValue(
                new ListValue([
                    substr($parts[0], 1),
                    'current' === $variable
                        ? new FunctionCallValue(
                            'current',
                            [new ValueForCurrentValue()]
                        )
                        : new VariableValue($variable)
                ])
            );
        } catch (InvalidArgumentException $exception) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $exception);
        }
    }
}
