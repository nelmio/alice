<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer
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
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone $this->lexer;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid token "@u->" found.
     */
    public function testThrowsAnExceptionWhenAnInvalidValueIsGiven()
    {
        $this->lexer->lex('@u->');
    }
}
