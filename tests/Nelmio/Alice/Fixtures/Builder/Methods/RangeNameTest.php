<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;

/**
 * @covers Nelmio\Alice\Fixtures\Builder\Methods\RangeName
 */
class RangeNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RangeName
     */
    private $method;

    public function setUp()
    {
        $this->method = new RangeName();
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
        $this->assertEquals($expected, $this->method->canBuild($name));
    }

    /**
     * @dataProvider provideLegacyFixtureSet
     * @group legacy
     */
    public function testCanBuildFixtureWithLegacySyntax($name, $expected)
    {
        $this->assertEquals($expected, $this->method->canBuild($name));
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
            'nominal' => ['user_{0..10}', true],
            'with extend' => ['user_{0..10} (extends something)', true],
            'with template' => ['user_{0..10} (template)', true],
            'with extend and template' => ['user_{0..10} (template)', true],

            'with only one dot' => ['user_{0.10}', false],
            'with no upper bound' => ['user_{0..}', false],
        ];
    }

    public function provideLegacyFixtureSet()
    {
        $data = $this->provideFixtureSet();
        $data = array_merge(
            $data,
            [
                'with deprecated range operator' => ['user_{0...10} (template)', true],
                'with more than 3 dots' => ['user_{0....10}', true],
            ]
        );

        return $data;
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
