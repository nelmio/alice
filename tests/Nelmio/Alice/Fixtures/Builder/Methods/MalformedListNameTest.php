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
 * @covers Nelmio\Alice\Fixtures\Builder\Methods\MalformedListName
 */
class MalformedListNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MalformedListName
     */
    private $method;

    public function setUp()
    {
        $this->method = new MalformedListName();
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
     * @group legacy
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
            'nominal' => ['user_{alice, bob}', false],
            'nominal with extend flag' => ['user_{alice, bob} (extends something)', false],
            'nominal with template flag' => ['user_{alice, bob} (template)', false],
            'nominal with extend and template flags' => ['user_{alice, bob} (extends something, template)', false],
            'with invalid member name in list' => ['user_{_, _}', false],

            'with spaces at the beginning' => ['user_{  alice, bob}', true],
            'with spaces before comma' => ['user_{alice  , bob}', true],
            'with spaces after comma' => ['user_{alice,   bob}', true],
            'with spaces before ending curly brace' => ['user_{alice, bob  }', true],
            'with one comma at the end' => ['user_{alice, bob,}', true],
            'with one comma at the beginning' => ['user_{, alice, bob}', true],
            'with only one member' => ['user_{alice}', true],
            'with only one member and a dot' => ['user_{alice.alias}', true],
            'with only one member two dots' => ['user_{alice.deep.alias}', true],

            'without curly braces' => ['user0', false],
            'non-list ranged fixture' => ['user_{0..10}', false],
        ];
    }

    public function provideData()
    {
        $return = [];

        $class = 'Dummy';
        $specs= [];

        $aliceBob = [
            'with spaces at the beginning' => 'user_{  alice, bob}',
            'with spaces before comma' => 'user_{alice  , bob}',
            'with spaces after comma' => 'user_{alice,   bob}',
            'with spaces before ending curly brace' => 'user_{alice, bob  }',
            'with one comma at the end' => 'user_{alice, bob,}',
            'with one comma at the beginning' => 'user_{, alice, bob}',
            'with only one member' => 'user_{alice}',
        ];
        foreach ($aliceBob as $title => $name) {
            $return[$title] = [
                $class,
                $name,
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
        }

        $return['with only one member'] = [
            $class,
            'user_{alice}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice',
                    $specs,
                    'alice'
                ),
            ]
        ];

        $return['with only one member two dots'] = [
            $class,
            'user_{alice.deep.alias}',
            $specs,
            [
                new Fixture(
                    $class,
                    'user_alice.deep.alias',
                    $specs,
                    'alice.deep.alias'
                ),
            ]
        ];

        $return['with flags'] = [
            $class,
            'user_{ alice , bob } (template)',
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

        return $return;
    }
}
