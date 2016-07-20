<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\ChoiceListValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Throwable\ParseThrowable;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\SimpleParser
 */
class SimpleParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var ParserInterface */
    private $parser;

    public function setUp()
    {
        $this->parser = (new NativeLoader())->getBuiltInExpressionLanguageParser();
    }

    public function testIsAParser()
    {
        $this->assertTrue(is_a(SimpleParser::class, ParserInterface::class, true));
    }

    public function testLexValueAndParseEachToken()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            $token2 = new Token('bar', new TokenType(TokenType::VARIABLE_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        $tokenParserProphecy->parse($token2)->willReturn('parsed_bar');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parser->parse($value);

        $lexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
        $tokenParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testReturnListValueIfSeveralTokensFound()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            $token2 = new Token('bar', new TokenType(TokenType::VARIABLE_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn(new ParameterValue('parsed_foo'));
        $tokenParserProphecy->parse($token2)->willReturn('parsed_bar');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parsedValue = $parser->parse($value);

        $this->assertEquals(
            new ListValue([
                new ParameterValue('parsed_foo'),
                'parsed_bar',
            ]),
            $parsedValue
        );
    }

    public function testValueIfOnlyOneTokensFound()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parsedValue = $parser->parse($value);

        $this->assertEquals(
            'parsed_foo',
            $parsedValue
        );
    }

    /**
     * @dataProvider provideValues
     */
    public function testParseValues($value, $expected)
    {
        \PHPUnit_Framework_Assert::markTestIncomplete('TODO');
        try {
            $actual = $this->parser->parse($value);
            if (null === $expected) {
                $this->fail(
                    sprintf(
                        'Expected exception to be thrown for "%s", got "%s" instead.',
                        $value,
                        var_export($actual, true)
                    )
                );
            }
        } catch (\InvalidArgumentException $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        } catch (ParseThrowable $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        }

        $this->assertEquals($expected, $actual, var_export($actual, true));
    }

    public function provideValues()
    {
        // simple values
        yield 'empty string' => [
            '',
            '',
        ];

        yield 'regular string value' => [
            'dummy',
            'dummy',
        ];

        // Escaped arrow
        yield '[Escaped arrow] nominal (1)' => [
            '<<',
            '<',
        ];
        yield '[Escaped arrow] nominal (2)' => [
            '>>',
            '>',
        ];
        yield '[Escaped arrow] parameter' => [
            '<<{param}>>',
            '<{param}>',
        ];
        yield '[Escaped arrow] function' => [
            '<<f()>>',
            '<f()>',
        ];
        yield '[Escaped arrow] surrounded' => [
            'foo << bar >> baz',
            'foo < bar > baz',
        ];

        // Parameters
        yield '[Parameter] nominal' => [
            '<{dummy_param}>',
            new ParameterValue('dummy_param'),
        ];
        yield '[Parameter] unbalanced (1)' => [
            '<{dummy_param>',
            null,
        ];
        yield '[Parameter] escaped unbalanced (1)' => [
            '<<{dummy_param>>',
            '<{dummy_param>',
        ];
        yield '[Parameter] unbalanced (2)' => [
            '<{dummy_param',
            null,
        ];
        yield '[Parameter] escaped unbalanced (2)' => [
            '<<{dummy_param',
            '<{dummy_param',
        ];
        yield '[Parameter] unbalanced (3)' => [
            '<dummy_param}>',
            null,
        ];
        yield '[Parameter] escaped unbalanced (3)' => [
            '<<dummy_param}>>',
            '<dummy_param}>',
        ];
        yield '[Parameter] unbalanced (4)' => [
            'dummy_param}>',
            null,
        ];
        yield '[Parameter] escaped unbalanced (4)' => [
            'dummy_param}>>',
            'dummy_param}>',
        ];
        yield '[Parameter] successive' => [
            '<{param1}><{param2}>',
            new ListValue([
                new ParameterValue('param1'),
                new ParameterValue('param2'),
            ]),
        ];
        yield '[Parameter] nested' => [
            '<{value_<{nested_param}>}>',
            null,
        ];
        yield '[Parameter] nested escape' => [
            '<{value_<<{nested_param}>>}>',
            null,
        ];
        yield '[Parameter] surrounded - nested' => [
            'foo <{value_<{nested_param}>}> bar',
            null,
        ];

        // Functions
        yield '[Function] nominal' => [
            '<function()>',
            new FunctionCallValue('function'),
        ];
        yield '[Function] unbalanced (1)' => [
            '<function()',
            null,
        ];
        yield '[Function] escaped unbalanced (1)' => [
            '<<function()',
            '<function()',
        ];
        yield '[Function] unbalanced (2)' => [
            'function()>',
            null,
        ];
        yield '[Function] escaped unbalanced (2)' => [
            'function()>>',
            'function()>',
        ];
        yield '[Function] unbalanced (3)' => [
            '<function(>',
            null,
        ];
        yield '[Function] escaped unbalanced (3)' => [
            '<<function(>>',
            '<function(>',
        ];
        yield '[Function] unbalanced (4)' => [
            '<function)>',
            null,
        ];
        yield '[Function] escaped unbalanced (4)' => [
            '<<function)>>',
            '<function)>',
        ];
        yield '[Function] successive functions' => [
            '<f()><g()>',
            null,
        ];
        yield '[Function] correct successive functions' => [
            '<f()> <g()>',
            new ListValue([
                new FunctionCallValue('f'),
                ' ',
                new FunctionCallValue('g'),
            ]),
        ];
        yield '[Function] nested functions' => [
            '<f(<g()>)>',
            new FunctionCallValue(
                'f',
                [
                    new FunctionCallValue('g'),
                ]
            ),
        ];
        yield '[Function] nominal surrounded' => [
            'foo <function()> bar',
            new ListValue([
                'foo ',
                new FunctionCallValue('function'),
                ' bar',
            ]),
        ];

        yield '[Function] nominal identity' => [
            '<(function())>',
            new FunctionCallValue(
                'identity',
                [
                    'function()'
                ]
            ),
        ];
        yield '[Function] identity with args' => [
            '<(function(echo("hello")))>',
            new FunctionCallValue(
                'identity',
                [
                    'function(echo("hello"))'
                ]
            ),
        ];
        yield '[Function] identity with params' => [
            '<(function(echo(<{param}>))>',
            new FunctionCallValue(
                'identity',
                [
                    'function(echo(<{param}>)'
                ]
            ),
        ];
        yield '[X] parameter, function, identity and escaped' => [
            '<{param}><function()><(echo("hello"))><<escaped_value>>',
            null,
        ];

        // Arrays
        yield '[Array] nominal string array' => [
            '10x @user',
            new DynamicArrayValue(
                '10',
                new FixtureReferenceValue('user')
            ),
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
            new DynamicArrayValue(
                '10',
                new ListValue([
                    new FixtureReferenceValue('user'),
                    ' bar',
                ])
            ),
        ];
        yield '[Array] string array with P1' => [
            '<dummy>x 50x <hello>',
            null,
        ];
        yield '[Array] string array with string array' => [
            '10x [@user->name, @group->getName()]',
            new DynamicArrayValue(
                '10',
                [
                    new FixturePropertyValue(
                        new FixtureReferenceValue('user'),
                        'name'
                    ),
                    new FixtureMethodCallValue(
                        new FixtureReferenceValue('group'),
                        new FunctionCallValue('getName')
                    ),
                ]
            ),
        ];
        yield '[Array] escaped array' => [
            '[[X]]',
            '[X]',
        ];
        yield '[Array] malformed escaped array 1' => [
            '[[X]',
            new ListValue([
                [
                    '[X'
                ]
            ]),
        ];
        yield '[Array] malformed escaped array 1' => [
            '[X]]',
            new ListValue([
                ['X'],
                ']',
            ]),
        ];
        yield '[Array] surrounded escaped array' => [
            'foo [[X]] bar',
            'foo [X] bar',
        ];
        yield '[Array] surrounded escaped array with param' => [
            'foo [[X]] yo <{param}> bar',
            new ListValue([
                'foo [X] yo ',
                new ParameterValue('param'),
                ' bar',
            ]),
        ];
        yield '[Array] simple string array' => [
            '[@user->name, @group->getName()]',
            [
                new FixturePropertyValue(
                    new FixtureReferenceValue('user'),
                    'name'
                ),
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('group'),
                    new FunctionCallValue('getName')
                ),
            ],
        ];

        // Optional
        yield '[Optional] nominal' => [
            '80%? Y',
            new OptionalValue(
                '80',
                'Y'
            ),
        ];
        yield '[Optional] with negative number' => [
            '-50%? Y',
            new ListValue([
                '-',
                new OptionalValue(
                    '50',
                    'Y'
                )
            ]),
        ];
        yield '[Optional] with float' => [
            '0.5%? Y',
            new OptionalValue(
                '0.5',
                'Y'
            ),
        ];
        yield '[Optional] with <X>' => [
            '<{dummy}>%? Y',
            new OptionalValue(
                new ParameterValue('dummy'),
                'Y'
            ),
        ];
        yield '[Optional] complete' => [
            '80%? Y: Z',
            new OptionalValue(
                '80',
                'Y',
                'Z'
            ),
        ];
        yield '[Optional] complete with negative number' => [
            '-50%? Y: Z',
            new ListValue([
                '-',
                new OptionalValue(
                    '50',
                    'Y',
                    'Z'
                ),
            ]),
        ];
        yield '[Optional] complete with float' => [
            '0.5%? Y: Z',
            new OptionalValue(
                '0.5',
                'Y',
                'Z'
            )
        ];
        yield '[Optional] complete with <X>' => [
            '<{dummy}>%? Y: Z',
            new OptionalValue(
                new ParameterValue('dummy'),
                'Y',
                'Z'
            ),
        ];
        yield '[Optional] nominal with left member' => [
            'foo 80%? Y',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '80',
                    'Y'
                )
            ]),
        ];
        yield '[Optional] with negative number and left member' => [
            'foo -50%? Y',
            new ListValue([
                'foo -',
                new OptionalValue(
                    '50',
                    'Y'
                )
            ]),
        ];
        yield '[Optional] with float and left member' => [
            'foo 0.5%? Y',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '0.5',
                    'Y'
                )
            ]),
        ];
        yield '[Optional] with <X> and left member' => [
            'foo <{dummy}>%? Y',
            new ListValue([
                'foo ',
                new OptionalValue(
                    new ParameterValue('dummy'),
                    'Y'
                )
            ]),
        ];
        yield '[Optional] complete with left member' => [
            'foo 80%? Y: Z',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '80',
                    'Y',
                    'Z'
                )
            ]),
        ];
        yield '[Optional] complete with negative number and left member' => [
            'foo -50%? Y: Z',
            new ListValue([
                'foo -',
                new OptionalValue(
                    '50',
                    'Y',
                    'Z'
                )
            ]),
        ];
        yield '[Optional] complete with float and left member' => [
            'foo 0.5%? Y: Z',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '0.5',
                    'Y',
                    'Z'
                )
            ]),
        ];
        yield '[Optional] complete with <X> and left member' => [
            'foo <{dummy}>%? Y: Z',
            new ListValue([
                'foo ',
                new OptionalValue(
                    new ParameterValue('dummy'),
                    'Y',
                    'Z'
                )
            ]),
        ];
        yield '[Optional] without members' => [
            '80%? ',
            null,
        ];
        yield '[Optional] without members 2' => [
            '80%?',
            null,
        ];
        yield '[Optional] without first member but with second' => [
            '80%? :Z',
            null,
        ];
        yield '[Optional] with first member containing a string' => [
            '80%? foo bar',
            new OptionalValue(
                '80',
                'foo bar'
            ),
        ];
        yield '[Optional] with first member containing a space and second member' => [
            '80%? foo bar: baz',
            new OptionalValue(
                '80',
                'foo bar',
                'baz'
            ),
        ];
        yield '[Optional] with first member containing a space and second member too' => [
            '80%? foo bar: baz faz',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo bar',
                    'baz'
                ),
                ' faz',
            ]),
        ];
        yield '[Optional] with second member containing a space' => [
            '80%? foo: bar baz',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo',
                    'bar'
                ),
                ' baz',
            ]),
        ];
        yield '[Optional] with second member without the space after semicolon' => [
            '80%? foo:bar baz',
            null,
        ];
        yield '[Optional] without space after quantifier' => [
            '80%?foo bar',
            null,
        ];
        yield '[Optional] without space after quantifier with second member' => [
            '80%?foo: bar baz',
            null,
        ];
        yield '[Optional] surrounded with params' => [
            'foo 80%? <{dummy}>: <another()> baz',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '80',
                    new ParameterValue('dummy'),
                    new FunctionCallValue('another')
                ),
                ' baz',
            ]),
        ];
        yield '[Optional] surrounded with params and nested' => [
            '<foo()> -80%? <dum10%? y: z my>: <<another>> <baz()>',
            null,
        ];

        // References
        yield '[Reference] empty reference' => [
            '@',
            null,
        ];
        yield '[Reference] empty escaped reference' => [
            '@@',
            '@',
        ];
        yield '[Reference] empty reference with second member' => [
            '@ foo',
            null,
        ];
        yield '[Reference] escaped empty reference with second member' => [
            '@@ foo',
            '@ foo',
        ];
        yield '[Reference] alone with strings' => [
            '@user0',
            new FixtureReferenceValue('user0'),
        ];
        yield '[Reference] escaped alone with strings' => [
            '@@user0',
            '@user0',
        ];
        yield '[Reference] left with strings' => [
            'foo @user0',
            new ListValue([
                'foo ',
                new FixtureReferenceValue('user0'),
            ]),
        ];
        yield '[Reference] right with strings' => [
            '@user0 bar',
            new ListValue([
                new FixtureReferenceValue('user0'),
                ' bar',
            ]),
        ];
        yield '[Reference] alone with prop' => [
            '@user0->username',
            new FixturePropertyValue(
                new FixtureReferenceValue('user0'),
                'username'
            ),
        ];
        yield '[Reference] left with prop' => [
            'foo @user0->username',
            new ListValue([
                'foo ',
                new FixturePropertyValue(
                    new FixtureReferenceValue('user0'),
                    'username'
                ),
            ]),
        ];
        yield '[Reference] right with prop' => [
            '@user0->username bar',
            new ListValue([
                new FixturePropertyValue(
                    new FixtureReferenceValue('user0'),
                    'username'
                ),
                ' bar',
            ]),
        ];
        yield '[Reference] successive prop' => [
            '@user0->username->value',
            null,
        ];
        yield '[Reference] surrounded successive prop' => [
            'foo @user0->username->value bar',
            null,
        ];
        yield '[Reference] with nested' => [
            '@user0@user1',
            new ListValue([
                new FixtureReferenceValue('user0'),
                new FixtureReferenceValue('user1'),
            ]),
        ];
        yield '[Reference] with nested surrounded' => [
            'foo @user0@user1 bar',
            new ListValue([
                'foo ',
                new FixtureReferenceValue('user0'),
                new FixtureReferenceValue('user1'),
                ' bar',

            ]),
        ];
        yield '[Reference] nominal range' => [
            '@user{1..2}',
            new ChoiceListValue([
                new FixtureReferenceValue('user1'),
                new FixtureReferenceValue('user2'),
            ]),
        ];
        yield '[Reference] surrounded range' => [
            'foo @user{1..2} bar',
            new ListValue([
                'foo ',
                new ChoiceListValue([
                    new FixtureReferenceValue('user1'),
                    new FixtureReferenceValue('user2'),
                ]),
                ' bar',
            ]),
        ];
        yield '[Reference] successive' => [
            '@user{1..2}@group{3..4}',
            new ListValue([
                new ChoiceListValue([
                    new FixtureReferenceValue('user1'),
                    new FixtureReferenceValue('user2'),
                ]),
                new ChoiceListValue([
                    new FixtureReferenceValue('group3'),
                    new FixtureReferenceValue('group4'),
                ]),
            ]),
        ];
        yield '[Reference] range-prop (1)' => [
            '@user->username{1..2}',
            null,
        ];
        yield '[Reference] range-prop (2)' => [
            '@user{1..2}->username',
            null,
        ];
        yield '[Reference] range-method (1)' => [
            '@user->getUserName(){1..2}',
            null,
        ];
        yield '[Reference] range-method (2)' => [
            '@user{1..2}->getUserName()',
            null,
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
            'foo @user0->username@user1->name bar',
            new ListValue([
                'foo ',
                new FixturePropertyValue(
                    new FixtureReferenceValue('user0'),
                    'username'
                ),
                new FixturePropertyValue(
                    new FixtureReferenceValue('user1'),
                    'name'
                ),
                ' bar',
            ]),
        ];
        yield '[Reference] alone with function' => [
            '@user0->getUserName()',
            new FixtureMethodCallValue(
                new FixtureReferenceValue('user0'),
                new FunctionCallValue('getUserName')
            ),
        ];
        yield '[Reference] function surrounded' => [
            'foo @user0->getUserName() bar',
            new ListValue([
                'foo ',
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user0'),
                    new FunctionCallValue('getUserName')
                ),
                ' bar',
            ]),
        ];
        yield '[Reference] alone with function with arguments' => [
            '@user0->getUserName($username)',
            new FixtureMethodCallValue(
                new FixtureReferenceValue('user0'),
                new FunctionCallValue('getUserName'),
                [
                    new VariableValue('username'),
                ]
            ),
        ];
        yield '[Reference] alone with function with arguments containing nested reference' => [
            '@user0->getUserName($username, @group->getName($foo))',
            null,
        ];
        yield '[Reference] alone with fluent function' => [
            '@user0->getUserName()->getName()',
            null,
        ];
        yield '[Reference] surrounded with fluent function' => [
            'foo @user0->getUserName()->getName() bar',
            null,
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
                new Token('@user{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] surrounded list' => [
            'foo @user0{alice, bob} bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] reference function' => [
            '@user0<current()>',
            [
                new Token('@user{@lice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] reference function with args' => [
            '@user0<f($foo, $bar)>',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user{@lice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] function nested' => [
            '@user0->getUserName()@user1->getName()',
            new ListValue([
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user0'),
                    new FunctionCallValue('getUserName')
                ),
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user1'),
                    new FunctionCallValue('getName')
                ),
            ]),
        ];
        yield '[Reference] function nested surrounded' => [
            'foo @user0->getUserName()@user1->getName() bar',
            new ListValue([
                'foo ',
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user0'),
                    new FunctionCallValue('getUserName')
                ),
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user1'),
                    new FunctionCallValue('getName')
                ),
                ' bar',
            ]),
        ];
        yield '[Reference] function nested with function' => [
            '@user0->@user1->getUsername()',
            null,
        ];
        yield '[Reference] function nested with function surrounded' => [
            'foo @user0->@user1->getUsername() bar',
            null,
        ];

        // Variables
        yield '[Variable] empty variable' => [
            '$',
            null,
        ];
        yield '[Variable] empty variable with second member' => [
            '$ foo',
            null,
        ];
        yield '[Variable] alone' => [
            '$username',
            new VariableValue('username'),
        ];
        yield '[Variable] left' => [
            'foo $username',
            new ListValue([
                'foo ',
                new VariableValue('username'),
            ]),
        ];
        yield '[Variable] right' => [
            '$username bar',
            new ListValue([
                new VariableValue('username'),
                ' bar',
            ]),
        ];
        yield '[Variable] successive' => [
            '$foo$bar',
            new ListValue([
                new VariableValue('foo'),
                new VariableValue('bar'),
            ]),
        ];
        yield '[Variable] successive surrounded' => [
            'faz $foo$bar baz',
            new ListValue([
                'faz ',
                new VariableValue('foo'),
                new VariableValue('bar'),
                ' baz',
            ]),
        ];
        yield '[Variable] empty escaped variable' => [
            '$$',
            '$',
        ];
        yield '[Variable] escaped empty variable with second member' => [
            '$$ foo',
            '$ foo',
        ];
        yield '[Variable] alone with strings' => [
            '$$username',
            '$username',
        ];
    }

    //TODO: @user->username->fluent
    //TODO: @user->getUsername()->fluent()
    //TODO: @user*...
}
