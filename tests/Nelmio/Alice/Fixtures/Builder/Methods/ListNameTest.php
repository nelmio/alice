<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;

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

    public function testIsABuilderMethod()
    {
        $this->assertInstanceOf('Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface', $this->method);
    }

    /**
     * @dataProvider provideFixtureSet
     */
    public function testCanBuildFixture($name, $expected)
    {
        $actual = $this->method->canBuild($name);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideData
     */
    public function testBuildFixture($class, $name, $specs, $expected)
    {
        $this->assertTrue($this->method->canBuild($name));
        $actual = $this->method->build($class, $name, $specs);

        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    public function provideFixtureSet()
    {
        return [
            'nominal' => ['user_{alice, bob}', true],
            'nominal with extend flag' => ['user_{alice, bob} (extends something)', true],
            'nominal with template flag' => ['user_{alice, bob} (template)', true],
            'nominal with extend and template flags' => ['user_{alice, bob} (extends something, template)', true],
            'with invalid member name in list' => ['user_{_, _}', true],

            'with spaces at the beginning' => ['user_{  alice, bob}', false],
            'with spaces before comma' => ['user_{alice  , bob}', false],
            'with spaces after comma' => ['user_{alice,   bob}', false],
            'with spaces before ending curly brace' => ['user_{alice, bob  }', false],
            'with one comma at the end' => ['user_{alice, bob,}', false],
            'with one comma at the beginning' => ['user_{, alice, bob}', false],

            'without curly braces' => ['user0', false],

            'with only one dot' => ['user_{alice, bob}', false],
            'with no upper bound' => ['user_{0..}', false],
            'with only one member' => ['user_{alice}', false],
        ];
    }

    public function provideData()
    {
        $return = [];

        $class = 'Dummy';
        $specs= [];

        $return['nominal'] = [
            $class,
            'user_{alice, bob}',
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
            ]
        ];

        $return['with template'] = [
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

        $return['with special characters'] = [
            $class,
            'user_{., /}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_.',
                    $specs,
                    '.'
                ),
                new Fixture(
                    $class,
                    'user_/',
                    $specs,
                    '/'
                ),
            ]
        ];

        return $return;
    }
}
