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

/**
 * @covers \Nelmio\Alice\Fixtures\Builder\Methods\ListName
 */
class ListNameTest extends MethodTestCase
{
    public function setUp()
    {
        $this->method = new ListName();
    }

    /**
     * @dataProvider provideSimpleFixtures
     */
    public function testCanBuildSimpleFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideListFixtures
     */
    public function testCanBuildListFixtures($name)
    {
        $this->assertCanBuild($name);
    }

    /**
     * @dataProvider provideMalformedListFixtures
     * @group legacy
     */
    public function testCanBuildMalformedListFixtures($name)
    {
        $this->assertCanBuild($name);
    }

    /**
     * @dataProvider provideSegmentFixtures
     */
    public function testCanBuildSegmentFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideDeprecatedSegmentFixtures
     * @group legacy
     */
    public function testCanBuildDeprecatedSegmentFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideMalformedSegmentFixtures
     * @group legacy
     */
    public function testCanBuildMalformedSegmentFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideSimpleFixtures
     */
    public function testBuildSimpleFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideListFixtures
     */
    public function testBuildListFixtures($name, $expected)
    {
        $this->assertBuiltResultIsTheSame($name, $expected);
    }

    /**
     * @dataProvider provideMalformedListFixtures
     * @group legacy
     */
    public function testBuildMalformedListFixtures($name, $expected)
    {
        $this->assertBuiltResultIsTheSame($name, $expected);
    }

    /**
     * @dataProvider provideSegmentFixtures
     */
    public function testBuildSegmentFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideDeprecatedSegmentFixtures
     * @group legacy
     */
    public function testBuildDeprecatedSegmentFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideMalformedSegmentFixtures
     * @group legacy
     */
    public function testBuildMalformedSegmentFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }
}
