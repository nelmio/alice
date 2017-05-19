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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer
 */
class ReferenceEscaperLexerTest extends TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(ReferenceEscaperLexer::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new ReferenceEscaperLexer(new FakeLexer());
    }

    /**
     * @dataProvider provideValues
     */
    public function testEscapesStringBeforeHandlingItOverToTheDecoratedLexer(string $value, string $expectedEscapedValue = null)
    {
        if (null === $expectedEscapedValue) {
            $expectedEscapedValue = $value;
        }

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($expectedEscapedValue)->willReturn($expected = [new \stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new ReferenceEscaperLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        $this->assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function provideValues()
    {
        yield 'empty string' => [''];

        yield 'regular string' => ['hello world'];

        yield 'string with a reference' => ['@foo'];

        yield 'string with a reference with members' => ['bar @foo baz'];

        yield 'reference in a middle of a word' => ['email@example', 'email\\@example'];
    }
}
