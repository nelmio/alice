<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\PartsResolver
 */
class PartsResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValueResolver()
    {
        $this->assertTrue(is_a(PartsResolver::class, ValueResolverInterface::class, true));
    }

    /**
     * @dataProvider provideValues
     */
    public function testResolveValues($value, array $expected = null)
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $set = new ResolvedFixtureSet(
            new ParameterBag(),
            new FixtureBag(),
            new ObjectBag()
        );

        $scope = [];

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        if ($expected === null) {
            $expectedSet = new ResolvedValueWithFixtureSet($value, $set);

            $decoratedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
            $resolver = new PartsResolver($decoratedResolver);
            $actual = $resolver->resolve($value, $fixture, $set, $scope);

            return $this->assertEquals($expectedSet, $actual);
        }

        $max = 'abcdefghijklmn';
        foreach ($expected as $index => $expectedValue) {
            $decoratedResolverProphecy
                ->resolve($expectedValue, $fixture, $set, $scope)
                ->willReturn(new ResolvedValueWithFixtureSet($max[$index], $set))
            ;
        }

        $resolver = new PartsResolver($decoratedResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $nbrOfExpected = count($expected);
        $expectedSet = new ResolvedValueWithFixtureSet(
            substr($max, 0, $nbrOfExpected),
            $set
        );

        $this->assertEquals($expectedSet, $actual);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes($nbrOfExpected);
    }

    /**
     * @link https://github.com/nelmio/alice/issues/377
     */
    public function provideValues()
    {
        // simple values
        yield 'null value' => [
            1,
            null,
        ];
        yield 'int value' => [
            1,
            null,
        ];
        yield 'float value' => [
            1.1,
            null,
        ];
        yield 'regular string value' => [
            'dummy',
            null,
        ];

        // Parameters or functions
        yield '[X] alone' => [
            '<string_value>',
            [
                '<string_value>',
            ],
        ];
        yield '[X] escaped' => [
            '<<escaped_value>>',
            [
                '<<escaped_value>>',
            ],
        ];
        yield '[X] nested' => [
            '<value_<{nested_param}>>',
            [
                '<value_<{nested_param}>>',
            ],
        ];
        yield '[X] nested escape' => [
            '<value_<<{nested_param}>>>',
            [
                '<value_<<{nested_param}>>>',
            ],
        ];
        yield '[X] surrounded' => [
            'foo <string_value> bar',
            [
                'foo ' ,
                '<string_value>',
                ' bar',
            ],
        ];
        yield '[X] surrounded - escaped' => [
            'foo <<escaped_value>> bar',
            [
                'foo ' ,
                '<<escaped_value>>',
                ' bar',
            ],
        ];
        yield '[X] surrounded - nested' => [
            'foo <value_<{nested_param}>> bar',
            [
                'foo ' ,
                '<value_<{nested_param}>>',
                ' bar',
            ],
        ];
        yield '[X] surrounded - nested escape' => [
            'foo <value_<<{nested_param}>>> bar',
            [
                'foo ' ,
                '<value_<<{nested_param}>>>',
                ' bar',
            ],
        ];

        // Arrays
        yield '[Array] array of strings' => [
            [
                'foo',
                'bar',
                'baz',
            ],
            [
                [
                    'foo',
                    'bar',
                    'baz',
                ],
            ],
        ];
        yield '[Array] nominal string array' => [
            '10x @user',
            [
                '10x @user',
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
                '10x @user bar',
            ],
        ];
        yield '[Array] string array with P1' => [
            '<dummy>x 50x <hello>',
            [
                '<dummy>x 50x <hello>',
            ],
        ];
        yield '[Array] string array with string array' => [
            '10x [@user*->name, @group->name]',
            [
                '10x [@user*->name, @group->name]',
            ],
        ];
        yield '[Array] escaped array' => [
            '[[X]]',
            [
                '[[X]]',
            ],
        ];
        yield '[Array] surrounded escaped array' => [
            'foo [[X]] bar',
            [
                'foo ',
                '[[X]]',
                ' bar',
            ],
        ];
        yield '[Array] surrounded escaped array with param' => [
            'foo [[X]] <param> bar',
            [
                'foo ',
                '[[X]]',
                ' ',
                '<param>',
                ' bar',
            ],
        ];
        yield '[Array] simple string array' => [
            '[@user*->name, @group->name]',
            [
                '[@user*->name, @group->name]',
            ],
        ];
//
//        // Optional
//        yield '[Optional] nominal' => [
//            '80%? Y',
//            [
//                '80%? Y',
//            ],
//        ];
//        yield '[Optional] with negative number' => [
//            '-50%? Y',
//            [
//                '-',
//                '-50%? Y',
//            ],
//        ];
//        yield '[Optional] with float' => [
//            '0.5%? Y',
//            [
//                '0.5%? Y',
//            ],
//        ];
//        yield '[Optional] with <X>' => [
//            '<dummy>%? Y',
//            [
//                '<dummy>%? Y',
//            ],
//        ];
//        yield '[Optional] complete' => [
//            '80%? Y: Z',
//            [
//                '80%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with negative number' => [
//            '-50%? Y: Z',
//            [
//                '-',
//                '-50%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with float' => [
//            '0.5%? Y: Z',
//            [
//                '0.5%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with <X>' => [
//            '<dummy>%? Y: Z',
//            [
//                '<dummy>%? Y: Z',
//            ],
//        ];
//        yield '[Optional] nominal with left member' => [
//            'foo 80%? Y',
//            [
//                'foo ',
//                '80%? Y',
//            ],
//        ];
//        yield '[Optional] with negative number and left member' => [
//            'foo -50%? Y',
//            [
//                'foo -',
//                '-50%? Y',
//            ],
//        ];
//        yield '[Optional] with float and left member' => [
//            'foo 0.5%? Y',
//            [
//                'foo ',
//                '0.5%? Y',
//            ],
//        ];
//        yield '[Optional] with <X> and left member' => [
//            'foo <dummy>%? Y',
//            [
//                'foo ',
//                '<dummy>%? Y',
//            ],
//        ];
//        yield '[Optional] complete with left member' => [
//            'foo 80%? Y: Z',
//            [
//                'foo ',
//                '80%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with negative number and left member' => [
//            'foo -50%? Y: Z',
//            [
//                'foo -',
//                '-50%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with float and left member' => [
//            'foo 0.5%? Y: Z',
//            [
//                'foo ',
//                '0.5%? Y: Z',
//            ],
//        ];
//        yield '[Optional] complete with <X> and left member' => [
//            'foo <dummy>%? Y: Z',
//            [
//                'foo ',
//                '<dummy>%? Y: Z',
//            ],
//        ];
//        yield '[Optional] without members' => [
//            '80%? ',
//            [
//                '80%? ',
//            ],
//        ];
//        yield '[Optional] without members 2' => [
//            '80%?',
//            [
//                '80%?',
//            ],
//        ];
//        yield '[Optional] without first member but with second' => [
//            '80%? :Z',
//            [
//                '80%? :Z',
//            ],
//        ];
//        yield '[Optional] with first member containing a string' => [
//            '80%? foo bar',
//            [
//                '80%? foo bar',
//            ],
//        ];
//        yield '[Optional] with first member containing a space and second member' => [
//            '80%? foo bar: baz',
//            [
//                '80%? foo bar: baz',
//            ],
//        ];
//        yield '[Optional] with first member containing a space and second member too' => [
//            '80%? foo bar: baz faz',
//            [
//                '80%? foo bar: baz faz',
//            ],
//        ];
//        yield '[Optional] with second member containing a space' => [
//            '80%? foo: bar baz',
//            [
//                '80%? foo: bar',
//                ' baz',
//            ],
//        ];
//        yield '[Optional] with second member without the space after semicolon' => [
//            '80%? foo:bar baz',
//            [
//                '80%? foo:bar baz',
//            ],
//        ];
//        yield '[Optional] without space after quantifier' => [
//            '80%?foo bar',
//            [
//                '80%?foo bar',
//            ],
//        ];
//        yield '[Optional] without space after quantifier with second member' => [
//            '80%?foo: bar baz',
//            [
//                '80%?foo: bar baz',
//            ],
//        ];
//        yield '[Optional] surrounded with params' => [
//            'foo 80%? <dummy>: <another> baz',
//            [
//                'foo ',
//                '80%? <dummy>: <another>',
//                'baz',
//            ],
//        ];
//        yield '[Optional] surrounded with params and nested' => [
//            '<foo> -80%? <dum10%? y: z my>: <<another>> <baz>',
//            [
//                '<foo> -',
//                '80%? <dum10%? y: z my>: <<another>>',
//                '<baz>',
//            ],
//        ];
//
//        // P4
//        yield '[P4] alone' => [
//            '100%? Y',
//            [
//                '100%? Y',
//            ],
//        ];
//        yield '[P4] alone with negative number' => [
//            '-100%? Y',
//            [
//                '-',
//                '100%? Y',
//            ],
//        ];
//        yield '[P4] alone with negative number' => [
//            '-100%? Y',
//            [
//                '-',
//                '100%? Y',
//            ],
//        ];
//
//        // References
//        yield '[Reference] alone with strings' => [
//            '@user0',
//            [
//                '@user0',
//            ],
//        ];
//        yield '[Reference] left with strings' => [
//            'foo @user0',
//            [
//                'foo ',
//                '@user0',
//            ],
//        ];
//        yield '[Reference] right with strings' => [
//            '@user0 bar',
//            [
//                '@user0',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] alone with prop' => [
//            '@user0->username',
//            [
//                '@user0->username',
//            ],
//        ];
//        yield '[Reference] left with prop' => [
//            'foo @user0->username',
//            [
//                'foo ',
//                '@user0->username',
//            ],
//        ];
//        yield '[Reference] right with prop' => [
//            '@user0->username bar',
//            [
//                '@user0->username',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] with nested' => [
//            '@user0@user1',
//            [
//                '@user0',
//                '@user1',
//            ],
//        ];
//        yield '[Reference] with nested surrounded' => [
//            'foo @user0@user1 bar',
//            [
//                'foo ',
//                '@user0',
//                '@user1',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] with nested with prop' => [
//            '@user0->@user1',
//            [
//                '@user0->@user1',
//            ],
//        ];
//        yield '[Reference] with nested with prop surrounded' => [
//            'foo @user0->@user1 bar',
//            [
//                'foo @user0->@user1 bar',
//            ],
//        ];
//        yield '[Reference] with successive with prop surrounded' => [
//            'foo @user0->username@user1->name bar',
//            [
//                'foo ',
//                '@user0->username',
//                '@user1->name',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] alone with function' => [
//            '@user0->getUserName()',
//            [
//                '@user0->getUserName()',
//            ],
//        ];
//        yield '[Reference] function surrounded' => [
//            'foo @user0->getUserName() bar',
//            [
//                ' foo',
//                '@user0->getUserName()',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] function nested' => [
//            '@user0->getUserName()@user1->getName()',
//            [
//                '@user0->getUserName()',
//                '@user1->getName()',
//            ],
//        ];
//        yield '[Reference] function nested surrounded' => [
//            'foo @user0->getUserName()@user1->getName() bar',
//            [
//                'foo ',
//                '@user0->getUserName()',
//                '@user1->getName()',
//                ' bar',
//            ],
//        ];
//        yield '[Reference] function nested with function' => [
//            '@user0->@user1->getUsername()',
//            [
//                '@user0->@user1->getUsername()',
//            ],
//        ];
//        yield '[Reference] function nested with function surrounded' => [
//            'foo @user0->@user1->getUsername() bar',
//            [
//                'foo @user0->@user1->getUsername() bar',
//            ],
//        ];
//
//        yield '[Variable] alone' => [
//            '$username',
//            [
//                '$username',
//            ],
//        ];
//        yield '[Variable] left' => [
//            'foo $username',
//            [
//                'foo ',
//                '$username',
//            ],
//        ];
//        yield '[Variable] right' => [
//            '$username bar',
//            [
//                '$username',
//                ' bar',
//            ],
//        ];
    }
}
