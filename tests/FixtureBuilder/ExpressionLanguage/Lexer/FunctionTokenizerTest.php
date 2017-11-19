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

use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\MalformedFunctionException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionTokenizer
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionTreeTokenizer
 */
class FunctionTokenizerTest extends TestCase
{
    /**
     * @var FunctionTokenizer
     */
    private $tokenizer;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->tokenizer = new FunctionTokenizer();
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FunctionTokenizer::class))->isCloneable());
    }

    /**
     * @dataProvider provideValues
     */
    public function testTokenizeValues($value, $expected)
    {
        try {
            $actual = $this->tokenizer->tokenize($value);
            if (null === $expected) {
                $this->fail('Expected exception to be thrown.');
            }

            $this->assertEquals($expected, $actual);
        } catch (MalformedFunctionException $exception) {
            if (null !== $expected) {
                throw $exception;
            }
        }
    }

    public function provideValues()
    {
        yield 'non function' => [
            'foo',
            'foo',
        ];

        yield 'single function' => [
            '<foo()>',
            '<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)>',
        ];

        yield 'surrounded single function' => [
            'ping <foo()> pong',
            'ping <aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'single function with 1 arg' => [
            '<foo(bar)>',
            '<aliceTokenizedFunction(FUNCTION_START__foo__barIDENTITY_OR_FUNCTION_END)>',
        ];

        yield 'surrounded single function with 1 arg' => [
            'ping <foo(bar)> pong',
            'ping <aliceTokenizedFunction(FUNCTION_START__foo__barIDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'single function with 2 args' => [
            '<foo(bar, baz)>',
            '<aliceTokenizedFunction(FUNCTION_START__foo__bar, bazIDENTITY_OR_FUNCTION_END)>',
        ];

        yield 'surrounded single function with 2 args' => [
            'ping <foo(bar, baz)> pong',
            'ping <aliceTokenizedFunction(FUNCTION_START__foo__bar, bazIDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'single function with 1 nested function' => [
            '<foo(<bar()>)>',
            '<aliceTokenizedFunction(FUNCTION_START__foo__FUNCTION_START__bar__IDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_END)>',
        ];

        yield 'surrounded single function with 1 nested function' => [
            'ping <foo(<bar()>)> pong',
            'ping <aliceTokenizedFunction(FUNCTION_START__foo__FUNCTION_START__bar__IDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'complex function' => [
            'ping <foo($foo, <bar()>, <baz($arg1, <baw($arg2)>)>)> pong',
            'ping <aliceTokenizedFunction(FUNCTION_START__foo__$foo, FUNCTION_START__bar__IDENTITY_OR_FUNCTION_END, FUNCTION_START__baz__$arg1, FUNCTION_START__baw__$arg2IDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'complex identities' => [
            'ping <($foo, <(bar)>, <($arg1, <($arg2)>)>)> pong',
            'ping <aliceTokenizedFunction(IDENTITY_START$foo, IDENTITY_STARTbarIDENTITY_OR_FUNCTION_END, IDENTITY_START$arg1, IDENTITY_START$arg2IDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_END)> pong',
        ];

        yield 'unclosed function' => [
            '<foo(>',
            null,
        ];
    }
}
