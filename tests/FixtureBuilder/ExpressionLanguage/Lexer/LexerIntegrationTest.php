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

use InvalidArgumentException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @coversNothing
 */
class LexerIntegrationTest extends TestCase
{
    /**
     * @var LexerInterface
     */
    protected $lexer;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->lexer = (new NativeLoader())->getLexer();
    }

    /**
     * @dataProvider provideValues
     */
    public function testCanLexValues(string $value, $expected)
    {
        try {
            $actual = $this->lexer->lex($value);

            if (null === $expected) {
                $this->fail(
                    sprintf(
                        'Expected exception to be thrown for "%s", got "%s" instead.',
                        $value,
                        var_export($actual, true)
                    )
                );
            }
        } catch (InvalidArgumentException $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        } catch (LexException $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        }

        $this->assertEquals($expected, $actual, var_export($actual, true));
        $this->assertSameSize($expected, $actual);
    }

    /**
     * @link https://github.com/nelmio/alice/issues/377
     */
    public function provideValues()
    {
        // simple values
        yield 'empty string' => [
            '',
            [
                new Token('', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'regular string value' => [
            'dummy',
            [
                new Token('dummy', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string value with quotes' => [
            '\'dummy\'',
            [
                new Token('\'dummy\'', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string value with double quotes' => [
            '"dummy"',
            [
                new Token('"dummy"', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string ending with letter followed by reference character' => [
            'foo@example.com',
            [
                new Token('foo', new TokenType(TokenType::STRING_TYPE)),
                new Token('\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('example.com', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string ending with number followed by reference character' => [
            'foo55@example.com',
            [
                new Token('foo', new TokenType(TokenType::STRING_TYPE)),
                new Token('55@example.com', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string with percentage #1' => [
            '%',
            [
                new Token('%', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield 'string with percentage #2' => [
            'foo % bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('% bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        // Escaped character
        yield '[Escape character] nominal (1)' => [
            '\\',
            null,
        ];
        yield '[Escape character] nominal (2)' => [
            '\\\\',
            [
                new Token('\\\\', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escape character] with empty reference' => [
            '\\@',
            [
                new Token('\\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escape character] with reference' => [
            '\\@user0',
            [
                new Token('\\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('user', new TokenType(TokenType::STRING_TYPE)),
                new Token('0', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Escape character] with function reference' => [
            '\\@<foo()>',
            [
                new Token('\\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Escape character] double escape with reference' => [
            '\\\\@',
            [
                new Token('\\\\', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('@', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Escape character] with empty reference' => [
            '\\\\\\@',
            [
                new Token('\\\\', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('\\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];

        // Escaped arrow
        yield '[Escaped arrow] nominal (1)' => [
            '\<',
            [
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escaped arrow] nominal (2)' => [
            '\>',
            [
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escaped arrow] parameter' => [
            '\<{param}>',
            [
                new Token('\<{param}>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escaped arrow] function' => [
            '\<f()>',
            [
                new Token('\<aliceTokenizedFunction(FUNCTION_START__f__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escaped arrow] surrounded' => [
            'foo \< bar \> baz',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' bar ', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        // Escaped percent sign
        yield '[Escaped percent sign]' => [
            '\%',
            [
                new Token('\%', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Escaped percent sign]' => [
            'a\%b',
            [
                new Token('a', new TokenType(TokenType::STRING_TYPE)),
                new Token('\%', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('b', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Escaped percent sign]' => [
            '100\%',
            [
                new Token('100', new TokenType(TokenType::STRING_TYPE)),
                new Token('\%', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];

        // Parameters
        yield '[Parameter] nominal' => [
            '<{dummy_param}>',
            [
                new Token('<{dummy_param}>', new TokenType(TokenType::PARAMETER_TYPE)),
            ],
        ];
        yield '[Parameter] unbalanced (1)' => [
            '<{dummy_param>',
            null,
        ];
        yield '[Parameter] escaped unbalanced (1)' => [
            '\<{dummy_param\>',
            [
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('{dummy_param', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Parameter] unbalanced (2)' => [
            '<{dummy_param',
            [
                new Token('<{dummy_param', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Parameter] escaped unbalanced (2)' => [
            '\<{dummy_param',
            [
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('{dummy_param', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Parameter] unbalanced (3)' => [
            '<dummy_param}>',
            null,
        ];
        yield '[Parameter] escaped unbalanced (3)' => [
            '\<dummy_param}\>',
            [
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('dummy_param}', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Parameter] unbalanced (4)' => [
            'dummy_param}>',
            [
                new Token('dummy_param}', new TokenType(TokenType::STRING_TYPE)),
                new Token('>', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Parameter] escaped unbalanced (4)' => [
            'dummy_param}\>',
            [
                new Token('dummy_param}', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Parameter] successive' => [
            '<{param1}><{param2}>',
            [
                new Token('<{param1}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token('<{param2}>', new TokenType(TokenType::PARAMETER_TYPE)),
            ],
        ];
        yield '[Parameter] nested' => [
            '<{value_<{nested_param}>}>',
            null,
        ];
        yield '[Parameter] nested escape' => [
            '<{value_\<{nested_param}>}>',
            null,
        ];
        yield '[Parameter] surrounded - nested' => [
            'foo <{value_<{nested_param}>}> bar',
            null,
        ];

        // Functions
        yield '[Function] nominal' => [
            '<function()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] localized nominal' => [
            '<fr_FR:function()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__fr_FR:function__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] unbalanced (1)' => [
            '<function()',
            null,
        ];
        yield '[Function] escaped unbalanced (1)' => [
            '\<function()',
            null,
        ];
        yield '[Function] unbalanced (2)' => [
            'function()>',
            null,
        ];
        yield '[Function] escaped unbalanced (2)' => [
            'function()\>',
            [
                new Token('function()', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Function] unbalanced (3)' => [
            '<function(>',
            null,
        ];
        yield '[Function] escaped unbalanced (3)' => [
            '\<function(\>',
            null,
        ];
        yield '[Function] unbalanced (4)' => [
            '<function)>',
            null,
        ];
        yield '[Function] escaped unbalanced (4)' => [
            '\<function)\>',
            [
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('function)', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Function] with numeric characters' => [
            '<ipv6()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__ipv6__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] successive functions' => [
            '<f()><g()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__IDENTITY_OR_FUNCTION_END)><aliceTokenizedFunction(FUNCTION_START__g__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] correct successive functions' => [
            '<f()> <g()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__IDENTITY_OR_FUNCTION_END)> <aliceTokenizedFunction(FUNCTION_START__g__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] correct successive functions with non space 0' => [
            '<f()>_<g()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__IDENTITY_OR_FUNCTION_END)>_<aliceTokenizedFunction(FUNCTION_START__g__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] correct successive functions with non space 1' => [
            '<f()>h<g()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__IDENTITY_OR_FUNCTION_END)>h<aliceTokenizedFunction(FUNCTION_START__g__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nested functions' => [
            '<f(<g()>)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__FUNCTION_START__g__IDENTITY_OR_FUNCTION_ENDIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nominal surrounded' => [
            'foo <function()> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield '[Function] nominal identity' => [
            '<(function())>',
            [
                new Token('<aliceTokenizedFunction(IDENTITY_STARTfunction()IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] identity with args' => [
            '<(function(echo("hello")))>',
            [
                new Token('<aliceTokenizedFunction(IDENTITY_STARTfunction(echo("hello"))IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] identity with params' => [
            '<(function(echo(<{param}>))>',
            [
                new Token('<aliceTokenizedFunction(IDENTITY_STARTfunction(echo(<{param}>)IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[X] parameter, function, identity and escaped' => [
            '<{param}><function()><(echo("hello"))>\<escaped_value\>',
            [
                new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__IDENTITY_OR_FUNCTION_END)><aliceTokenizedFunction(IDENTITY_STARTecho("hello")IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('escaped_value', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Function] nominal with arguments' => [
            '<function($foo, $arg)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__$foo, $argIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nominal with string arguments which contains quotes' => [
            '<function(\'foo\', "bar")>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__\'foo\', "bar"IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nominal with array argument which contains string elements in quotes' => [
            '<function([\'foo\', "bar"])>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__[\'foo\', "bar"]IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] unbalanced with arguments (1)' => [
            '<function($foo, $arg)',
            null,
        ];
        yield '[Function] escaped unbalanced with arguments (1)' => [
            '\<function($foo, $arg)',
            null,
        ];
        yield '[Function] unbalanced with arguments (2)' => [
            'function($foo, $arg)>',
            null,
        ];
        yield '[Function] escaped unbalanced with arguments (2)' => [
            'function($foo, $arg)\>',
            [
                new Token('function(', new TokenType(TokenType::STRING_TYPE)),
                new Token('$foo', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token(', ', new TokenType(TokenType::STRING_TYPE)),
                new Token('$arg', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token(')', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Function] escaped unbalanced with arguments (4)' => [
            '\<function$foo, $arg)>',
            null,
        ];
        yield '[Function] successive functions with arguments' => [
            '<f($foo, $arg)><g($baz, $faz)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__$foo, $argIDENTITY_OR_FUNCTION_END)><aliceTokenizedFunction(FUNCTION_START__g__$baz, $fazIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] correct successive functions with arguments' => [
            '<f($foo, $arg)> <g($baz, $faz)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__$foo, $argIDENTITY_OR_FUNCTION_END)> <aliceTokenizedFunction(FUNCTION_START__g__$baz, $fazIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nested functions with arguments' => [
            '<f(<g($baz)>, $arg)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__FUNCTION_START__g__$bazIDENTITY_OR_FUNCTION_END, $argIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nested functions with multiple arguments' => [
            '<f(<g($baz, $faz)>, $arg)>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__FUNCTION_START__g__$baz, $fazIDENTITY_OR_FUNCTION_END, $argIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Function] nominal surrounded with arguments' => [
            'foo <function($foo, $arg)> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__$foo, $argIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield '[Function] nominal identity with arguments' => [
            '<(function($foo, $arg))>',
            [
                new Token('<aliceTokenizedFunction(IDENTITY_STARTfunction($foo, $arg)IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        // https://github.com/nelmio/alice/issues/773
        yield '[Function] with tricky string arguments' => [
            '<dateTimeBetween(\'something,\', \'-12 months\', \'\', \',\', $now, "something,", "-12 months", "", ",")>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__dateTimeBetween__\'something,\', \'-12 months\', \'\', \',\', $now, "something,", "-12 months", "", ","IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];

        yield '[Function] with unix type line break in single quoted string argument' => [
            '<function(\'foo\nbar\')>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__\'foo\nbar\'IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ]
        ];

        yield '[Function] with windows type line break in single quoted string argument' => [
            '<function(\'foo\\r\\nbar\')>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__\'foo\r\nbar\'IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ]
        ];

        yield '[Function] with unix type line break in double quoted string argument' => [
            '<function("foo\nbar")>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__"foo\nbar"IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE))
            ]
        ];

        yield '[Function] with windows type line break in double quoted string argument' => [
            '<function("foo\r\nbar")>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__"foo\r\nbar"IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE))
            ]
        ];

        $arg = \str_repeat('a', 2000);
        yield '[Function] with long argument' => [
            '<function("'.$arg.'")>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__function__"'.$arg.'"IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE))
            ]
        ];

        // Arrays
        yield '[Array] nominal string array' => [
            '10x @user',
            [
                new Token('10x @user', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] string array with negative number' => [
            '-10x @user',
            null,
        ];
        yield '[Array] string array with left member' => [
            'foo 10x @user',
            null,
        ];
        yield '[Array] string array with right member' => [
            '10x @user bar',
            [
                new Token('10x @user bar', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] string array with P1' => [
            '<dummy>x 50x <hello>',
            [
                new Token('<dummy>x 50x <hello>', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] string array with string array' => [
            '10x [@user*->name, @group->name]',
            [
                new Token('10x [@user*->name, @group->name]', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] escaped array' => [
            '\[X]',
            [
                new Token('\[X]', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Array] malformed escaped array 1' => [
            '[X\]',
            [
                new Token('[X\]', new TokenType(TokenType::STRING_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] malformed escaped array 2' => [
            '[X\]',
            [
                new Token('[X\]', new TokenType(TokenType::STRING_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] surrounded escaped array' => [
            'foo \[X] bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('\[X]', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Array] surrounded escaped array with param' => [
            'foo \[X] yo <{param}> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('\[X]', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' yo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Array] simple string array' => [
            '[@user*->name, @group->name]',
            [
                new Token('[@user*->name, @group->name]', new TokenType(TokenType::STRING_ARRAY_TYPE)),
            ],
        ];

        // Optional
        yield '[Optional] nominal' => [
            '80%? Y',
            [
                new Token('80%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with negative number' => [
            '-50%? Y',
            [
                new Token('-', new TokenType(TokenType::STRING_TYPE)),
                new Token('50%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with float' => [
            '0.5%? Y',
            [
                new Token('0.5%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with <X>' => [
            '<dummy>%? Y',
            [
                new Token('<dummy>%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete' => [
            '80%? Y: Z',
            [
                new Token('80%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with superfluous space' => [
            '80%?  Y :  Z  ',
            [
                new Token('80%?  Y :  Z', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token('  ', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] complete with negative number' => [
            '-50%? Y: Z',
            [
                new Token('-', new TokenType(TokenType::STRING_TYPE)),
                new Token('50%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with float' => [
            '0.5%? Y: Z',
            [
                new Token('0.5%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with <X>' => [
            '<dummy>%? Y: Z',
            [
                new Token('<dummy>%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] nominal with left member' => [
            'foo 80%? Y',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('80%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with negative number and left member' => [
            'foo -50%? Y',
            [
                new Token('foo -', new TokenType(TokenType::STRING_TYPE)),
                new Token('50%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with float and left member' => [
            'foo 0.5%? Y',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('0.5%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with <X> and left member' => [
            'foo <dummy>%? Y',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<dummy>%? Y', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with left member' => [
            'foo 80%? Y: Z',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('80%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with negative number and left member' => [
            'foo -50%? Y: Z',
            [
                new Token('foo -', new TokenType(TokenType::STRING_TYPE)),
                new Token('50%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with float and left member' => [
            'foo 0.5%? Y: Z',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('0.5%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] complete with <X> and left member' => [
            'foo <dummy>%? Y: Z',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<dummy>%? Y: Z', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] without members' => [
            '80%? ',
            [
                new Token('80', new TokenType(TokenType::STRING_TYPE)),
                new Token('%? ', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] without members 2' => [
            '80%?',
            [
                new Token('80', new TokenType(TokenType::STRING_TYPE)),
                new Token('%?', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] without first member but with second' => [
            '80%? :Z',
            null,
        ];
        yield '[Optional] with first member containing a string' => [
            '80%? foo bar',
            [
                new Token('80%? foo bar', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with first member containing a space and second member' => [
            '80%? foo bar: baz',
            [
                new Token('80%? foo bar: baz', new TokenType(TokenType::OPTIONAL_TYPE)),
            ],
        ];
        yield '[Optional] with first member containing a space and second member too' => [
            '80%? foo bar: baz faz',
            [
                new Token('80%? foo bar: baz', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' faz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] with second member containing a space' => [
            '80%? foo: bar baz',
            [
                new Token('80%? foo: bar', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] with second member without the space after semicolon' => [
            '80%? foo:bar baz',
            null,
        ];
        yield '[Optional] without space after quantifier' => [
            '80%?foo bar',
            [
                new Token('80', new TokenType(TokenType::STRING_TYPE)),
                new Token('%?foo bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] without space after quantifier with second member' => [
            '80%?foo: bar baz',
            [
                new Token('80', new TokenType(TokenType::STRING_TYPE)),
                new Token('%?foo: bar baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] surrounded with params' => [
            'foo 80%? <{dummy}>: <another()> baz',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('80%? <{dummy}>: <aliceTokenizedFunction(FUNCTION_START__another__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] surrounded with params and nested' => [
            '<foo()> -80%? <{dum10}>%? y: z my: \<another\> <baz()>',
            [
                new Token('<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)> -80%? <{dum10}>%? y: z', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' my: ', new TokenType(TokenType::STRING_TYPE)),
                new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('another', new TokenType(TokenType::STRING_TYPE)),
                new Token('\>', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__baz__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];

        // References
        yield '[Reference] empty reference' => [
            '@',
            [
                new Token('@', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] empty escaped reference' => [
            '\@',
            [
                new Token('\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Reference] empty reference with second member' => [
            '@ foo',
            [
                new Token('@ foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] escaped empty reference with second member' => [
            '\@ foo',
            [
                new Token('\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] alone with strings' => [
            '@user0',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] with function call' => [
            '@user0_<current()>',
            [
                new Token('@user0_', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Reference] escaped alone with strings' => [
            '\@user0',
            [
                new Token('\@', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('user', new TokenType(TokenType::STRING_TYPE)),
                new Token('0', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] left with strings' => [
            'foo @user0',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] right with strings' => [
            '@user0 bar',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] alone with prop' => [
            '@user0->username',
            [
                new Token('@user0->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] wildcard with prop' => [
            '@user*->username',
            [
                new Token('@user*->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] list with prop' => [
            '@user{alice, bob}->username',
            [
                new Token('@user{alice, bob}->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] range with prop' => [
            '@user{1..2}->username',
            [
                new Token('@user{1..2}->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] variable with prop' => [
            '@user$current->username',
            [
                new Token('@user$current->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] left with prop' => [
            'foo @user0->username',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] right with prop' => [
            '@user0->username bar',
            [
                new Token('@user0->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] successive prop' => [
            '@user0->username->value',
            [
                new Token('@user0->username->value', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded successive prop' => [
            'foo @user0->username->value bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->username->value', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),

            ],
        ];
        yield '[Reference] with nested' => [
            '@user0@user1',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('@user1', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] with nested surrounded' => [
            'foo @user0@user1 bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('@user1', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),

            ],
        ];
        yield '[Reference] nominal range' => [
            '@user{1..2}',
            [
                new Token('@user{1..2}', new TokenType(TokenType::RANGE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded range' => [
            'foo @user{1..2} bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user{1..2}', new TokenType(TokenType::RANGE_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] successive' => [
            '@user{1..2}@group{3..4}',
            [
                new Token('@user{1..2}', new TokenType(TokenType::RANGE_REFERENCE_TYPE)),
                new Token('@group{3..4}', new TokenType(TokenType::RANGE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] range-prop' => [
            '@user->username{1..2}',
            [
                new Token('@user->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token('{', new TokenType(TokenType::STRING_TYPE)),
                new Token('1..2}', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] range-method' => [
            '@user->getUserName(){1..2}',
            [
                new Token('@user->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token('{', new TokenType(TokenType::STRING_TYPE)),
                new Token('1..2}', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] with nested with prop' => [
            '@user0->@user1',
            null,
        ];
        yield '[Reference] with nested with prop surrounded' => [
            'foo @user0->@user1 bar',
            null,
        ];
        yield '[Reference] with successive with prop surrounded' => [
            'foo @user0->username @user1->name bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token(' ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user1->name', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] alone with function' => [
            '@user0->getUserName()',
            [
                new Token('@user0->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] wildcard alone with function' => [
            '@user*->getUserName()',
            [
                new Token('@user*->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] function surrounded' => [
            'foo @user0->getUserName() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] alone with function with arguments' => [
            '@user0->setName($foo, $bar)',
            [
                new Token('@user0->setName($foo, $bar)', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] alone with function with arguments containing nested reference' => [
            '@user0->getUserName($username, @group->getName($foo))',
            [
                new Token('@user0->getUserName($username, @group->getName($foo))', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] alone with fluent function' => [
            '@user0->getUserName()->getName()',
            [
                new Token('@user0->getUserName()->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded with fluent function' => [
            'foo @user0->getUserName()->getName() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->getUserName()->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] nominal wildcard' => [
            '@user*',
            [
                new Token('@user*', new TokenType(TokenType::WILDCARD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded wildcard' => [
            'foo @user* bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user*', new TokenType(TokenType::WILDCARD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] nominal list' => [
            '@user0{alice, bob}',
            [
                new Token('@user0{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] list with function' => [
            '@user{alice, bob}->getUserName()',
            [
                new Token('@user{alice, bob}->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded list' => [
            'foo @user0{alice, bob} bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] reference variable' => [
            '@user0$foo',
            [
                new Token('@user0$foo', new TokenType(TokenType::VARIABLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] reference function' => [
            '@user0<current()>',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Reference] reference which is entirely a function' => [
            '@<current()>',
            [
                new Token('@', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Reference] surrounded reference function' => [
            'foo @user0<current()> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] reference function with args' => [
            '@user0<f($foo, $bar)>',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__$foo, $barIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[Reference] surrounded reference function with args' => [
            'foo @user0<f($foo, $bar)> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__f__$foo, $barIDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] function successive' => [
            '@user0->getUserName()@user1->getName()',
            [
                new Token('@user0->getUserName()@user1->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] function successive surrounded' => [
            'foo @user0->getUserName()@user1->getName() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->getUserName()@user1->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] function nested with function' => [
            '@user0->@user1->getUsername()',
            null,
        ];
        yield '[Reference] function nested with function surrounded' => [
            'foo @user0->@user1->getUsername() bar',
            null,
        ];
        yield '[Reference] current' => [
            'foo @user0<current()> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token('<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        yield '[Reference] property reference with a function call' => [
            'foo @user0<current()>->prop bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0<aliceTokenizedFunction(FUNCTION_START__current__IDENTITY_OR_FUNCTION_END)>->prop', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        // Variables
        yield '[Variable] empty variable' => [
            '$',
            [
                new Token('$', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Variable] empty variable with second member' => [
            '$ foo',
            [
                new Token('$ foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Variable] alone' => [
            '$username',
            [
                new Token('$username', new TokenType(TokenType::VARIABLE_TYPE)),
            ],
        ];
        yield '[Variable] numerical' => [
            '$0',
            [
                new Token('$0', new TokenType(TokenType::VARIABLE_TYPE)),
            ],
        ];
        yield '[Variable] left' => [
            'foo $username',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('$username', new TokenType(TokenType::VARIABLE_TYPE)),
            ],
        ];
        yield '[Variable] right' => [
            '$username bar',
            [
                new Token('$username', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Variable] successive' => [
            '$foo$bar',
            [
                new Token('$foo', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token('$bar', new TokenType(TokenType::VARIABLE_TYPE)),
            ],
        ];
        yield '[Variable] successive surrounded' => [
            'faz $foo$bar baz',
            [
                new Token('faz ', new TokenType(TokenType::STRING_TYPE)),
                new Token('$foo', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token('$bar', new TokenType(TokenType::VARIABLE_TYPE)),
                new Token(' baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Variable] empty escaped variable' => [
            '\$',
            [
                new Token('\$', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
            ],
        ];
        yield '[Variable] escaped empty variable with second member' => [
            '\$ foo',
            [
                new Token('\$', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token(' foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Variable] alone with strings' => [
            '\$username',
            [
                new Token('\$', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                new Token('username', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
    }
}
