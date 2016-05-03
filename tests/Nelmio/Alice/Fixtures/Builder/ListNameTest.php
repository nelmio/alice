<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\Builder\Methods\ListName;
use Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use PhpUnit\Assert;

/**
 * @covers Nelmio\Alice\Fixtures\Builder\Methods\ListName
 */
class ListNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListName
     */
    private $method;

    public function setUp()
    {
        $this->method = new ListName();
    }

    public function test_is_a_builder_method()
    {
        Assert::assertIsA(MethodInterface::class, ListName::class);
    }

    /**
     * @dataProvider provideFixtureSet
     */
    public function test_can_build_fixture($name, $expected)
    {
        $this->assertEquals($expected, $this->method->canBuild($name));
    }

    /**
     * @dataProvider provideData
     */
    public function test_build_fixture($class, $name, $specs, $expected)
    {
        $this->assertTrue($this->method->canBuild($name));
        $actual = $this->method->build($class, $name, $specs);

        $this->assertEquals($expected, $actual, null, 0.0, 15, true);
    }

    public function provideFixtureSet()
    {
        return [
            'nominal' => ['user_{alice, bob, foo bar}', true],
            'with extend' => ['user_{alice, bob, foo bar} (extends something)', true],
            'with template' => ['user_{alice, bob, foo bar} (template)', true],
            'with extend and template' => ['user_{alice, bob, foo bar} (template)', true],

            'one member finishing by a comma' => ['user_{alice,}', false],
            'two members finishing by a comma' => ['user_{alice, bob,}', false],
        ];
    }

    public function provideData()
    {
        $class = 'Dummy';
        $specs= [];

        yield [
            $class,
            'user_{alice, bob, foo bar}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice',
                    $specs,
                    'alice'
                ),
                new Fixture(
                    $class,
                    'user_bob',
                    $specs,
                    'bob'
                ),
                new Fixture(
                    $class,
                    'user_foo bar',
                    $specs,
                    'foo bar'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice, bob} (template)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice (template)',
                    $specs,
                    'alice'
                ),
                new Fixture(
                    $class,
                    'user_bob (template)',
                    $specs,
                    'bob'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice, bob} (extends something)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice (extends something)',
                    $specs,
                    'alice'
                ),
                new Fixture(
                    $class,
                    'user_bob (extends something)',
                    $specs,
                    'bob'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice} (template, extends something)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice (template, extends something)',
                    $specs,
                    'alice'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice    ,    bob    } (extends something)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice (extends something)',
                    $specs,
                    'alice'
                ),
                new Fixture(
                    $class,
                    'user_bob (extends something)',
                    $specs,
                    'bob'
                ),
            ]
        ];
    }
}
