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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\NotCallableTrait;

class DummyLexer implements LexerInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function lex(string $value): array
    {
        return [$value];
    }
}
