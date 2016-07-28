<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Lexer;

use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeLexer implements LexerInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function lex(string $value): array
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
