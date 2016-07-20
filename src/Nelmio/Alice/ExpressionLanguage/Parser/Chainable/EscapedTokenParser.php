<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\Chainable;

use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class EscapedTokenParser implements ChainableTokenParserInterface
{
    const SUPPORTED_TYPES = [
        TokenType::ESCAPED_ARROW_TYPE => true,
        TokenType::ESCAPED_REFERENCE_TYPE => true,
        TokenType::ESCAPED_VARIABLE_TYPE => true,
    ];

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return isset(self::SUPPORTED_TYPES[$token->getType()->getValue()]);
    }

    /**
     * Parses '<<', '@@'...
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): string
    {
        return $token->getValue()[0];
    }
}
