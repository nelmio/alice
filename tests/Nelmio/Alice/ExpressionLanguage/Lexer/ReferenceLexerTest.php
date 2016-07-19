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

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Lexer\ReferenceLexer
 */
class ReferenceLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReferenceLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = new ReferenceLexer();
    }

    public function testIsALexer()
    {
        $this->assertInstanceOf(LexerInterface::class, $this->lexer);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "@ ".
     */
    public function testThrowExceptionIfNoMatchingPatternFound()
    {
        $this->lexer->lex('@ ');
    }
}
