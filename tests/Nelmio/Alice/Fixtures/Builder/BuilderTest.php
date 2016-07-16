<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\Loader;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Fixtures\Builder\Builder
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    use BuilderProviderTrait;

    const USER = 'Nelmio\Alice\support\models\User';

    /**
     * @var Builder
     */
    private $builder;

    public function setUp()
    {
        $loader = new Loader();

        $loaderReflection = new \ReflectionObject($loader);
        $builderReflection = $loaderReflection->getProperty('builder');
        $builderReflection->setAccessible(true);

        $this->builder = $builderReflection->getValue($loader);
    }

    public function testCanCreateBuilder()
    {
        new Builder([]);

        $method1Prophecy = $this->prophesize('Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface');
        $method1Prophecy->canBuild(Argument::any())->shouldNotBeCalled();
        /** @var MethodInterface $method1 */
        $method1 = $method1Prophecy->reveal();

        $method2Prophecy = $this->prophesize('Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface');
        $method2Prophecy->canBuild(Argument::any())->shouldNotBeCalled();
        /** @var MethodInterface $method2 */
        $method2 = $method2Prophecy->reveal();

        new Builder([$method1, $method2]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowExeptionIfMethodsAreNotMethods()
    {
        new Builder([new \stdClass()]);
    }

    public function testAddBuilder()
    {
        $builder = new Builder([]);;
        $builder->addBuilder(new CustomMethod);

        $fixtures = $builder->build(self::USER, 'spec dumped', ['thisShould' => 'be gone']);
        $this->assertEmpty($fixtures[0]->getProperties());
    }

    /**
     * @dataProvider provideSimpleFixtures
     */
    public function testBuildSimpleFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @dataProvider provideListFixtures
     */
    public function testBuildListFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @dataProvider provideMalformedListFixtures
     * @group legacy
     */
    public function testBuildMalformedListFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @dataProvider provideSegmentFixtures
     */
    public function testBuildSegmentFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @dataProvider provideDeprecatedSegmentFixtures
     * @group legacy
     */
    public function testBuildDeprecatedSegmentFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @dataProvider provideMalformedSegmentFixtures
     * @group legacy
     */
    public function testBuildMalformedSegmentFixtures($name, $expected)
    {
        $actual = $this->builder->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    /**
     * @group legacy
     */
    public function testReturnsNullWhenCannotBuildAFixture()
    {
        $builder = new Builder([]);
        $builder->build('Dummy', 'dummy', []);
    }
}

class CustomMethod implements MethodInterface
{
    public function canBuild($name)
    {
        return $name == 'spec dumped';
    }

    public function build($class, $name, array $spec)
    {
        return [new Fixture($class, $name, [], null)];
    }
}
