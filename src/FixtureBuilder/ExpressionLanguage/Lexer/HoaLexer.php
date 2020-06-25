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

use Hoa\Compiler\Llk\Parser as HoaParser;
use Hoa\Compiler\Llk\TreeNode;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;

final class HoaLexer implements LexerInterface
{
    private $parser;

    public function __construct(HoaParser $parser)
    {

        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function lex(string $value): TreeNode
    {
        return $this->parser->parse($value);
    }
}
