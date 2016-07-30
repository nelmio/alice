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

use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class ParameterTokenParser implements ChainableTokenParserInterface
{
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
     */
    public function parse(Token $token): ParameterValue
    {
        $value = $token->getValue();
        $paramKey = substr($value, 2, strlen($value) - 4);

        return new ParameterValue($paramKey);
    }
}
