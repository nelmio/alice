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
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler
 */
class OptionalFlagHandlerTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(OptionalFlagHandler::class))->isCloneable());
    }

    public function testCreatesAnOptionalCallIfFlagIsAnOptionalFlagIs()
    {
        $call = new FakeMethodCall();

        $flag = new OptionalFlag(50);

        $handler = new OptionalFlagHandler();

        $expected = new OptionalMethodCall($call, $flag);

        $actual = $handler->handleMethodFlags($call, $flag);

        $this->assertEquals($expected, $actual);
    }

    public function testLeavesTheFunctionUnchangedIfFlagIsNotAnOptionalFlag()
    {
        $call = new FakeMethodCall();

        $flag = new DummyFlag();

        $handler = new OptionalFlagHandler();

        $expected = $call;

        $actual = $handler->handleMethodFlags($call, $flag);

        $this->assertSame($expected, $actual);
    }
}
