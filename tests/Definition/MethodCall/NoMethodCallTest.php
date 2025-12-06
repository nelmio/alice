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

namespace Nelmio\Alice\Definition\MethodCall;

use LogicException;
use Nelmio\Alice\Definition\MethodCallInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(NoMethodCall::class)]
final class NoMethodCallTest extends TestCase
{
    public function testIsAMethodCall(): void
    {
        self::assertTrue(is_a(NoMethodCall::class, MethodCallInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $call = new NoMethodCall();

        self::assertEquals('none', $call->__toString());
    }

    public function testCannotCreateNewInstanceWithNewArguments(): void
    {
        $call = new NoMethodCall();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::withArguments()" should not be called.');

        $call->withArguments();
    }

    public function testCannotGetCaller(): void
    {
        $call = new NoMethodCall();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getCaller()" should not be called.');

        $call->getCaller();
    }

    public function testCannotGetMethod(): void
    {
        $call = new NoMethodCall();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getMethod()" should not be called.');

        $call->getMethod();
    }

    public function testCannotGetArguments(): void
    {
        $call = new NoMethodCall();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getArguments()" should not be called.');

        $call->getArguments();
    }
}
