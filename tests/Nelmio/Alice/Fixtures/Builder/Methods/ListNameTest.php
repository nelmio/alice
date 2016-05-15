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

    /**
     * @dataProvider provideLegacyData
     * @group legacy
     */
    public function testBuildFixtureWithLegacySyntax($class, $name, $specs, $expected)
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

            'with spaces at the beginning' => ['user_{  alice, bob}', false],
            'with spaces before comma' => ['user_{alice  , bob}', false],
            'with spaces after comma' => ['user_{alice,   bob}', false],
            'with spaces before ending curly brace' => ['user_{alice, bob  }', false],
            'with one comma at the end' => ['user_{alice, bob,}', false],
            'with one comma at the beginning' => ['user_{, alice, bob}', false],

            'without curly braces' => ['user0', false],

            'with only one dot' => ['user_{alice, bob}', false],
            'with no upper bound' => ['user_{0..}', false],
        ];
    }

    public function provideData()
    {
        $return = [];

        $class = 'Dummy';
        $specs= [];

        $return['nominal'] = [
            $class,
            'user_{0..2}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_0',
                    $specs,
                    '0'
                ),
                new Fixture(
                    $class,
                    'user_1',
                    $specs,
                    '1'
                ),
                new Fixture(
                    $class,
                    'user_2',
                    $specs,
                    '2'
                ),
            ]
        ];

        $return['with template'] = [
            $class,
            'user_{0..2} (template)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_0 (template)',
                    $specs,
                    '0'
                ),
                new Fixture(
                    $class,
                    'user_1 (template)',
                    $specs,
                    '1'
                ),
                new Fixture(
                    $class,
                    'user_2 (template)',
                    $specs,
                    '2'
                ),
            ]
        ];

        $return['with extends'] = [
            $class,
            'user_{0..2} (extends something)',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_0 (extends something)',
                    $specs,
                    '0'
                ),
                new Fixture(
                    $class,
                    'user_1 (extends something)',
                    $specs,
                    '1'
                ),
                new Fixture(
                    $class,
                    'user_2 (extends something)',
                    $specs,
                    '2'
                ),
            ]
        ];

        return $return;
    }

    public function provideLegacyData()
    {
        $return = [];

        $class = 'Dummy';
        $specs= [];

        $return['with 3 dots'] = [
            $class,
            'user_{0...2}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_0',
                    $specs,
                    '0'
                ),
                new Fixture(
                    $class,
                    'user_1',
                    $specs,
                    '1'
                ),
            ]
        ];

        $return['with more than 3 dots'] = [
            $class,
            'user_{0....2}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_0',
                    $specs,
                    '0'
                ),
                new Fixture(
                    $class,
                    'user_1',
                    $specs,
                    '1'
                ),
            ]
        ];

        return $return;
    }
}
