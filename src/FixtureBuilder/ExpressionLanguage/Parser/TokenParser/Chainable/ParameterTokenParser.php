<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class ParameterTokenParser implements ChainableTokenParserInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::PARAMETER_TYPE;
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
            $paramKey = substr($value, 2, strlen($value) - 4);

            return new ParameterValue($paramKey);
        } catch (\TypeError $error) {
            throw ParseException::createForToken($token, $error);
        }
    }
}
