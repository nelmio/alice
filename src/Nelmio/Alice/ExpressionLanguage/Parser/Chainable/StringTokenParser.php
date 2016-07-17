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

final class StringTokenParser implements ChainableTokenParserInterface
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::STRING_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        return $token->getValue();
    }
}
