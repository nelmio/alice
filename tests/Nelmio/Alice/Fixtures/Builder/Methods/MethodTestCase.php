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

use Nelmio\Alice\Fixtures\Builder\BuilderProviderTrait;
use PHPUnit\Framework\TestCase;

abstract class MethodTestCase extends TestCase
{
    use BuilderProviderTrait;

    /**
     * @var MethodInterface
     */
    protected $method;

    public function testIsABuilderMethod()
    {
        $this->assertInstanceOf('Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface', $this->method);
    }

    abstract public function testCanBuildSimpleFixtures($name);

    abstract public function testCanBuildListFixtures($name);

    abstract public function testCanBuildMalformedListFixtures($name);

    abstract public function testCanBuildSegmentFixtures($name);

    abstract public function testCanBuildDeprecatedSegmentFixtures($name);

    abstract public function testCanBuildMalformedSegmentFixtures($name);

    abstract public function testBuildSimpleFixtures($name, $expected);

    abstract public function testBuildListFixtures($name, $expected);

    abstract public function testBuildMalformedListFixtures($name, $expected);

    abstract public function testBuildSegmentFixtures($name, $expected);

    abstract public function testBuildDeprecatedSegmentFixtures($name, $expected);

    abstract public function testBuildMalformedSegmentFixtures($name, $expected);

    /**
     * @param string $name Reference name
     */
    public function assertCanBuild($name)
    {
        $actual = $this->method->canBuild($name);

        $this->assertTrue($actual);
    }

    /**
     * @param string $name Reference name
     */
    public function assertCannotBuild($name)
    {
        $actual = $this->method->canBuild($name);

        $this->assertFalse($actual);
    }

    public function assertBuiltResultIsTheSame($name, $expected)
    {
        $this->assertTrue($this->method->canBuild($name));
        $actual = $this->method->build('Dummy', $name, []);

        if (is_array($expected)) {
            $this->assertTrue(is_array($actual));
            $this->assertCount(count($expected), $actual);
        } else {
            $this->assertNull($actual);
        }
        $this->assertEquals($expected, $actual, null, 0.0, 10, true);
    }

    public function markAsInvalidCase()
    {
        $this->assertTrue(true, 'Invalid scenario.');
    }
}
