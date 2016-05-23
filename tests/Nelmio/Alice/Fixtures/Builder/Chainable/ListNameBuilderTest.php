<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Chainable;

use Nelmio\Alice\Fixtures\Builder\ChainableBuilderInterface;
use Nelmio\Alice\Fixtures\RangedFixtureDefinition;
use PhpUnit\Assert;

/**
 * @covers Nelmio\Alice\Fixtures\Builder\Chainable\ListNameBuilder
 */
class ListNameBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListNameBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new ListNameBuilder();
    }

    public function test_is_a_chainable_builder()
    {
        Assert::assertIsA(ChainableBuilderInterface::class, ListNameBuilder::class);
    }

    /**
     * @dataProvider provideFixtureSet
     */
    public function test_can_build_fixture($name, $expected)
    {
        $this->assertEquals($expected, $this->builder->canBuild($name));
    }

    /**
     * @dataProvider provideData
     */
    public function test_build_fixture($class, $name, $specs, $expected)
    {
        $this->assertTrue($this->builder->canBuild($name));
        $actual = $this->builder->build($class, $name, $specs);

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
                new RangedFixtureDefinition(
                    $class,
                    'user_alice',
                    $specs,
                    'user_{alice, bob, foo bar}',
                    'alice'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_bob',
                    $specs,
                    'user_{alice, bob, foo bar}',
                    'bob'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_foo bar',
                    $specs,
                    'user_{alice, bob, foo bar}',
                    'foo bar'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice, bob} (template)',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_alice',
                    $specs,
                    'user_{alice, bob} (template)',
                    'alice'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_bob',
                    $specs,
                    'user_{alice, bob} (template)',
                    'bob'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice, bob} (extends something)',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_alice',
                    $specs,
                    'user_{alice, bob} (extends something)',
                    'alice'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_bob',
                    $specs,
                    'user_{alice, bob} (extends something)',
                    'bob'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice} (template, extends something)',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_alice',
                    $specs,
                    'user_{alice} (template, extends something)',
                    'alice'
                ),
            ]
        ];

        yield [
            $class,
            'user_{alice    ,    bob    } (extends something)',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_alice',
                    $specs,
                    'user_{alice    ,    bob    } (extends something)',
                    'alice'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_bob',
                    $specs,
                    'user_{alice    ,    bob    } (extends something)',
                    'bob'
                ),
            ]
        ];
    }
}
