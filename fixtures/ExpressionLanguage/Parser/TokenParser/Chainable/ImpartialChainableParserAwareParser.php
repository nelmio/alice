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

use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\NotCallableTrait;

class ImpartialChainableParserAwareParser extends AbstractChainableParserAwareParser
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
