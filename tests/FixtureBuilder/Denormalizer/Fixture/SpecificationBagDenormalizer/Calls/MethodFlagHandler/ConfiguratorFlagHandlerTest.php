<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Flag\ConfiguratorFlag;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\MethodCall\ConfiguratorMethodCall;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler
 */
class ConfiguratorFlagHandlerTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ConfiguratorFlagHandler::class))->isCloneable());
    }

    public function testCreatesAnOptionalCallIfFlagIsAnOptionalFlagIs()
    {
        $call = new FakeMethodCall();

        $handler = new ConfiguratorFlagHandler();

        $expected = new ConfiguratorMethodCall($call);

        $actual = $handler->handleMethodFlags($call, new ConfiguratorFlag());

        $this->assertEquals($expected, $actual);
    }

    public function testLeavesTheFunctionUnchangedIfFlagIsNotAnOptionalFlag()
    {
        $call = new FakeMethodCall();

        $flag = new DummyFlag();

        $handler = new ConfiguratorFlagHandler();

        $expected = $call;

        $actual = $handler->handleMethodFlags($call, $flag);

        $this->assertSame($expected, $actual);
    }
}
