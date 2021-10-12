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

use Nelmio\Alice\Definition\Value\ParameterValue;
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
final class ParameterTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::PARAMETER_TYPE;
    }

    /**
     * Parses '<{paramKey}>', '<{nested_<{param}>}>', etc.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token): ParameterValue
    {
        $value = $token->getValue();

        try {
            $paramKey = strlen($value) > 3 ? substr($value, 2, -2) : false;

            return new ParameterValue($paramKey);
        } catch (TypeError $error) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $error);
        }
    }
}
