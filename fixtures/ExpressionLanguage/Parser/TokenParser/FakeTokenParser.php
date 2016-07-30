<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\TokenParser;

use Nelmio\Alice\ExpressionLanguage\Parser\TokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\NotCallableTrait;

class FakeTokenParser implements TokenParserInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
