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
 * @covers Nelmio\Alice\Fixtures\Builder\Chainable\RangeNameBuilder
 */
class RangeNameBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RangeNameBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new RangeNameBuilder();
    }

    public function test_is_a_chainable_builder()
    {
        Assert::assertIsA(ChainableBuilderInterface::class, RangeNameBuilder::class);
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
            'nominal' => ['user_{0..10}', true],
            'with extend' => ['user_{0..10} (extends something)', true],
            'with template' => ['user_{0..10} (template)', true],
            'with extend and template' => ['user_{0..10} (template)', true],

            'with only one dot' => ['user_{0.10}', false],
            'with more than two dots' => ['user_{0...10}', false],
            'with space after dot' => ['user_{0.. 10}', false],
            'with space before dot' => ['user_{0 ..10}', false],
            'without ending range' => ['user_{0..}', false],
            'without ending range and dots' => ['user_{0}', false],
            'without starting range and dots' => ['user_{..10}', false],
        ];
    }

    public function provideData()
    {
        $class = 'Dummy';
        $specs= [];

        yield [
            $class,
            'user_{0..1}',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_0',
                    $specs,
                    'user_{0..1}',
                    '0'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_1',
                    $specs,
                    'user_{0..1}',
                    '1'
                ),
            ]
        ];

        yield [
            $class,
            'user_{0..1} (template)',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_0',
                    $specs,
                    'user_{0..1} (template)',
                    '0'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_1',
                    $specs,
                    'user_{0..1} (template)',
                    '1'
                ),
            ]
        ];

        yield [
            $class,
            'user_{1..0}',
            $specs,
            [
                new RangedFixtureDefinition(
                    $class,
                    'user_0',
                    $specs,
                    'user_{1..0}',
                    '0'
                ),
                new RangedFixtureDefinition(
                    $class,
                    'user_1',
                    $specs,
                    'user_{1..0}',
                    '1'
                ),
            ]
        ];
    }
}
