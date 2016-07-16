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

use Nelmio\Alice\ExpressionLanguage\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class EscapedParameterTokenParser implements ChainableTokenParserInterface
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::ESCAPED_PARAMETER_TYPE;
    }

    /**
     * Parses '<<param>>' into '<param>'.
     * 
     * {@inheritdoc}
     */
    public function parse(Token $token): string
    {
        $value = $token->getValue();

        return substr($value, 1, strlen($value) - 3);
    }
}