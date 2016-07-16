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

use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;

/**
 * @covers Nelmio\Alice\Lexer\SimpleLexer
 */
class SimpleLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = (new NativeLoader())->getBuiltInLexer();
    }

    public function testIsALexer()
    {
        $this->assertTrue(is_a(SimpleLexer::class, LexerInterface::class, true));
    }

    /**
     * @dataProvider provideValues
     */
    public function testLexValues(string $value, $expected)
    {
        try {
            $actual = $this->lexer->lex($value);
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
            [],
        ];
        
        yield 'regular string value' => [
            'dummy',
            [
                new Token('dummy', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];

        // Parameters or functions
        yield '[X] parameter alone' => [
            '<{dummy_param}>',
            [
                new Token('<{dummy_param}>', new TokenType(TokenType::PARAMETER_TYPE)),
            ],
        ];
        yield '[X] function alone' => [
            '<function()>',
            [
                new Token('<function()>', new TokenType(TokenType::FUNCTION_TYPE)),
            ],
        ];
        yield '[X] identity alone' => [
            '<(function())>',
            [
                new Token('<(function())>', new TokenType(TokenType::IDENTITY_TYPE)),
            ],
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
            [
                new Token('<<escaped_value>>', new TokenType(TokenType::ESCAPED_PARAMETER_TYPE)),
            ],
        ];
        yield '[X] parameter, function, identity and escaped' => [
            '<{param}><function()><(echo("hello"))><<escaped_value>>',
            [
                new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token('<function()><(echo("hello"))>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token('<<escaped_value>>', new TokenType(TokenType::ESCAPED_PARAMETER_TYPE)),
            ],
        ];
        yield '[X] nested' => [
            '<{value_<{nested_param}>}>',
            [
                new Token('<{value_<{nested_param}>}>', new TokenType(TokenType::PARAMETER_TYPE)),
            ],
        ];
        yield '[X] nested escape' => [
            '<{value_<<{nested_param}>>}>',
            [
                new Token('<{value_<<{nested_param}>>}>', new TokenType(TokenType::PARAMETER_TYPE)),
            ],
        ];
        yield '[X] surrounded' => [
            'foo <function()> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<function()>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[X] surrounded - escaped' => [
            'foo <<escaped_value>> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<<escaped_value>>', new TokenType(TokenType::ESCAPED_PARAMETER_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[X] surrounded - nested' => [
            'foo <{value_<{nested_param}>}> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<{value_<{nested_param}>}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[X] surrounded - nested escape' => [
            'foo <{value_<<{nested_param}>>}> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<{value_<<{nested_param}>>}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
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
            '[[X]]',
            [
                new Token('[[X]]', new TokenType(TokenType::ESCAPED_ARRAY)),
            ],
        ];
        yield '[Array] malformed escaped array 1' => [
            '[[X]',
            [
                new Token('[[X]', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
            ],
        ];
        yield '[Array] malformed escaped array 1' => [
            '[X]]',
            [
                new Token('[X]', new TokenType(TokenType::STRING_ARRAY)),
                new Token(']', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Array] surrounded escaped array' => [
            'foo [[X]] bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('[[X]]', new TokenType(TokenType::ESCAPED_ARRAY)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Array] surrounded escaped array with param' => [
            'foo [[X]] yo <{param}> bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('[[X]]', new TokenType(TokenType::ESCAPED_ARRAY)),
                new Token(' yo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Array] simple string array' => [
            '[@user*->name, @group->name]',
            [
                new Token('[@user*->name, @group->name]', new TokenType(TokenType::STRING_ARRAY)),
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
                new Token('80%? ', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] without members 2' => [
            '80%?',
            [
                new Token('80%?', new TokenType(TokenType::STRING_TYPE)),
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
                new Token('80%?foo bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] without space after quantifier with second member' => [
            '80%?foo: bar baz',
            [
                new Token('80%?foo: bar baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] surrounded with params' => [
            'foo 80%? <dummy>: <another> baz',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('80%? <dummy>: <another>', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' baz', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Optional] surrounded with params and nested' => [
            '<foo()> -80%? <dum10%? y: z my>: <<another>> <baz()>',
            [
                new Token('<foo()>', new TokenType(TokenType::FUNCTION_TYPE)),
                new Token(' -', new TokenType(TokenType::STRING_TYPE)),
                new Token('80%? <dum10%? y: z', new TokenType(TokenType::OPTIONAL_TYPE)),
                new Token(' my>: ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<<another>>', new TokenType(TokenType::ESCAPED_PARAMETER_TYPE)),
                new Token(' ', new TokenType(TokenType::STRING_TYPE)),
                new Token('<baz()>', new TokenType(TokenType::FUNCTION_TYPE)),
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
            '@@',
            [
                new Token('@@', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] empty reference with second member' => [
            '@ foo',
            [
                new Token('@ foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] escaped empty reference with second member' => [
            '@@ foo',
            [
                new Token('@@ foo', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] alone with strings' => [
            '@user0',
            [
                new Token('@user0', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] escaped alone with strings' => [
            '@@user0',
            [
                new Token('@@user0', new TokenType(TokenType::ESCAPED_REFERENCE_TYPE)),
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
        yield '[Reference] with nested with prop' => [
            '@user0->@user1',
            [
                new Token('@user0->', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token('@user1', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] with nested with prop surrounded' => [
            'foo @user0->@user1 bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token('@user1', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] with successive with prop surrounded' => [
            'foo @user0->username@user1->name bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
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
        yield '[Reference] function surrounded' => [
            'foo @user0->getUserName() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] function nested' => [
            '@user0->getUserName()@user1->getName()',
            [
                new Token('@user0->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token('@user1->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] function nested surrounded' => [
            'foo @user0->getUserName()@user1->getName() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->getUserName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token('@user1->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
                new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
            ],
        ];
        yield '[Reference] function nested with function' => [
            '@user0->@user1->getUsername()',
            [
                new Token('@user0->', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token('@user1->getUsername()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
            ],
        ];
        yield '[Reference] function nested with function surrounded' => [
            'foo @user0->@user1->getUsername() bar',
            [
                new Token('foo ', new TokenType(TokenType::STRING_TYPE)),
                new Token('@user0->', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE)),
                new Token('@user1->getUsername()', new TokenType(TokenType::METHOD_REFERENCE_TYPE)),
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
    }
}
