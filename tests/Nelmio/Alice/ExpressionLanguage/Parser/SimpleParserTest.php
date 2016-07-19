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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;
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
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        $tokenParserProphecy->parse($token2)->willReturn('parsed_bar');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parsedValue = $parser->parse($value);

        $this->assertEquals(
            new ListValue([
                'parsed_foo',
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
        try {
            $actual = $this->parser->parse($value);
            if (null === $expected) {
                $this->fail(
                    sprintf(
                        'Expected exception to be thrown for "%s".',
                        $value
                    )
                );
            }
        } catch (ParseException $exception) {
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

        // Parameters or functions
        yield '[X] parameter alone' => [
            '<{dummy_param}>',
            new ParameterValue('dummy_param'),
        ];
        yield '[X] function alone' => [
            '<function()>',
            new FunctionCallValue('function'),
        ];
        yield '[X] identity alone' => [
            '<(function())>',
            new FunctionCallValue('identity', ['function()']),
        ];
        yield '[X] malformed alone 1' => [
            '<function(>',
            null,
        ];
        yield '[X] malformed alone 2' => [
            '<function>',
            null,
        ];
        yield '[X] escaped' => [
            '<<escaped_value>>',
            '<escaped_value>',
        ];
        yield '[X] parameter, function, identity and escaped' => [
            '<{param}><function()><(echo("hello"))><<escaped_value>>',
            new ListValue([
                new ParameterValue('param'),
                new FunctionCallValue('function'),
                new FunctionCallValue('identity', ['echo("hello")']),
                '<escaped_value>',
            ]),
        ];
        yield '[X] nested' => [
            '<{value_<{nested_param}>}>',
            new ParameterValue(
                new ListValue([
                    'value_',
                    new ParameterValue('nested_param'),
                ])
            ),
        ];
        yield '[X] nested escape' => [
            '<{value_<<{nested_param}>>}>',
            new ListValue([
                new ParameterValue(
                    new ListValue([
                        'value_',
                        '<nested_param>',
                    ])
                ),
            ]),
        ];
        yield '[X] surrounded' => [
            'foo <function()> bar',
            new ListValue([
                'foo ',
                new FunctionCallValue('function'),
                ' bar',
            ]),
        ];
        yield '[X] surrounded - escaped' => [
            'foo <<escaped_value>> bar',
            new ListValue([
                'foo ',
                '<escaped_value>',
                ' bar',
            ]),
        ];
        yield '[X] surrounded - nested' => [
            'foo <{value_<{nested_param}>}> bar',
            new ListValue([
                'foo ',
                new ParameterValue(
                    new ListValue([
                        'value_',
                        new ParameterValue('nested_param'),
                    ])
                ),
                ' bar',
            ]),
        ];
        yield '[X] surrounded - nested escape' => [
            'foo <{value_<<{nested_param}>>}> bar',
            new ListValue([
                'foo ',
                new ParameterValue(
                    new ListValue([
                        'value_',
                        '<nested_param>',
                    ])
                ),
                ' bar',
            ]),
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
            new ListValue([
                new DynamicArrayValue(
                    '10',
                    new FixtureReferenceValue('user')
                ),
                ' bar',
            ]),
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
            new ListValue([
                '[X]'
            ]),
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
                [
                    'X]'
                ]
            ]),
        ];
        yield '[Array] surrounded escaped array' => [
            'foo [[X]] bar',
            new ListValue([
                'foo ',
                '[X]',
                ' bar',
            ]),
        ];
        yield '[Array] surrounded escaped array with param' => [
            'foo [[X]] yo <{param}> bar',
            new ListValue([
                'foo ',
                '[X]',
                ' yo ',
                new ParameterValue('param'),
                ' bar',
            ]),
        ];
        yield '[Array] simple string array' => [
            '[@user->name, @group->getName()]',
            new ListValue([
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
            ]),
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
                    '80',
                    'Y',
                    'Z'
                ),
            ]),
        ];
        yield '[Optional] complete with float' => [
            '0.5%? Y: Z',
            new ListValue([
                new OptionalValue(
                    '0.5',
                    'Y',
                    'Z'
                )
            ]),
        ];
        yield '[Optional] complete with <X>' => [
            '<{dummy}>%? Y: Z',
            new ListValue([
                new OptionalValue(
                    new ParameterValue('dummy'),
                    'Y',
                    'Z'
                )
            ]),
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
            '80%? ',
        ];
        yield '[Optional] without members 2' => [
            '80%?',
            '80%?',
        ];
        yield '[Optional] without first member but with second' => [
            '80%? :Z',
            null,
        ];
        yield '[Optional] with first member containing a string' => [
            '80%? foo bar',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo bar'
                )
            ]),
        ];
        yield '[Optional] with first member containing a space and second member' => [
            '80%? foo bar: baz',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo bar',
                    'baz'
                )
            ]),
        ];
        yield '[Optional] with first member containing a space and second member too' => [
            '80%? foo bar: baz faz',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo bar',
                    'baz faz'
                )
            ]),
        ];
        yield '[Optional] with second member containing a space' => [
            '80%? foo: bar baz',
            new ListValue([
                new OptionalValue(
                    '80',
                    'foo',
                    'bar baz'
                )
            ]),
        ];
        yield '[Optional] with second member without the space after semicolon' => [
            '80%? foo:bar baz',
            null,
        ];
        yield '[Optional] without space after quantifier' => [
            '80%?foo bar',
            '80%?foo bar',
        ];
        yield '[Optional] without space after quantifier with second member' => [
            '80%?foo: bar baz',
            '80%?foo: bar baz',
        ];
        yield '[Optional] surrounded with params' => [
            'foo 80%? <{dummy}>: <another()> baz',
            new ListValue([
                'foo ',
                new OptionalValue(
                    '80%',
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
            '@',
        ];
        yield '[Reference] empty escaped reference' => [
            '@@',
            '@',
        ];
        yield '[Reference] empty reference with second member' => [
            '@ foo',
            '@ foo',
        ];
        yield '[Reference] escaped empty reference with second member' => [
            '@@ foo',
            '@@ foo',
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
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
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
            new ListValue([
                new FixturePropertyValue(
                    new FixtureReferenceValue('user0'),
                    'username'
                ),
            ]),
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
            new ListValue([
                new FixtureMethodCallValue(
                    new FixtureReferenceValue('user0'),
                    new FunctionCallValue('getUserName')
                ),
            ]),
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
            '$',
        ];
        yield '[Variable] empty variable with second member' => [
            '$ foo',
            '$ foo',
        ];
        yield '[Variable] alone' => [
            '$username',
            new ListValue([
                new VariableValue('username'),
            ]),
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
        yield '[Variable] empty escaped variable' => [
            '$$',
            '$',
        ];
        yield '[Variable] empty variable with second member' => [
            '$ foo',
            '$ foo',
        ];
        yield '[Variable] escaped empty variable with second member' => [
            '$$ foo',
            '$$ foo',
        ];
        yield '[Variable] alone with strings' => [
            '$$username',
            '$username',
        ];

        yield '[String] combine string tokens' => [
            'foo $$',
            'foo $$',
        ];
    }
}
