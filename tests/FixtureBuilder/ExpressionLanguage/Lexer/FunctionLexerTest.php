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

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer
 */
class FunctionLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FunctionLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = new FunctionLexer(new DummyLexer());
    }

    public function testIsAParser()
    {
        $this->assertTrue(is_a(FunctionLexer::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FunctionLexer(new FakeLexer());
    }

    /**
     * @dataProvider provideValues
     */
    public function testLexValues($value, $expected)
    {
        try {
            $actual = $this->lexer->lex($value);
            if (null === $expected) {
                $this->fail('Expected exception to be thrown.');
            }
            $this->assertEquals($expected, $actual);
        } catch (ExpressionLanguageParseThrowable $exception) {
            if (null !== $expected) {
                throw $expected;
            }
        }
    }

    public function provideValues()
    {
        yield 'non function' => [
            'foo',
            [
                'foo',
            ]
        ];

        yield 'single function' => [
            '<foo()>',
            [
                '<foo()>',
            ]
        ];

        yield 'surrounded single function' => [
            'ping <foo()> pong',
            [
                'ping ',
                '<foo()>',
                ' pong',
            ]
        ];

        yield 'single function with 1 arg' => [
            '<foo(bar)>',
            [
                '<foo(bar)>',
            ]
        ];

        yield 'surrounded single function with 1 arg' => [
            'ping <foo(bar)> pong',
            [
                'ping ',
                '<foo(bar)>',
                ' pong',
            ]
        ];

        yield 'single function with 2 args' => [
            '<foo(bar, baz)>',
            [
                '<foo(bar, baz)>',
            ]
        ];

        yield 'surrounded single function with 2 args' => [
            'ping <foo(bar, baz)> pong',
            [
                'ping ',
                '<foo(bar, baz)>',
                ' pong',
            ]
        ];

        yield 'single function with 1 nested function' => [
            '<foo(<bar()>)>',
            [
                '<foo(<bar()>)>',
            ]
        ];

        yield 'surrounded single function with 1 nested function' => [
            'ping <foo(<bar()>)> pong',
            [
                'ping ',
                '<foo(<bar()>)>',
                ' pong',
            ]
        ];

        yield 'complex function' => [
            'ping <foo($foo, <bar()>, <baz($arg1, <baw($arg2)>)>)> pong',
            [
                'ping ',
                '<foo($foo, <bar()>, <baz($arg1, <baw($arg2)>)>)>',
                ' pong',
            ]
        ];

        yield 'complex identities' => [
            'ping <($foo, <(bar)>, <($arg1, <($arg2)>)>)> pong',
            [
                'ping ',
                '<($foo, <(bar)>, <($arg1, <($arg2)>)>)>',
                ' pong',
            ]
        ];

        yield 'unclosed function' => [
            '<foo(>',
            null,
        ];

    }
}
