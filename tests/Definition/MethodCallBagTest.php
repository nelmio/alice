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

namespace Nelmio\Alice\Definition;

use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \Nelmio\Alice\Definition\MethodCallBag
 * @internal
 */
final class MethodCallBagTest extends TestCase
{
    /**
     * @var ReflectionProperty
     */
    private $propRefl;

    protected function setUp(): void
    {
        $refl = new ReflectionClass(MethodCallBag::class);
        $propRefl = $refl->getProperty('methodCalls');

        $this->propRefl = $propRefl;
    }

    public function testAddingACallCreatesANewBagWithTheAddedInstance(): void
    {
        $methodCall1 = new DummyMethodCall('mc1');
        $methodCall2 = new DummyMethodCall('mc2');

        $bag = new MethodCallBag();
        $bag1 = $bag->with($methodCall1);
        $bag2 = $bag1->with($methodCall2);

        self::assertInstanceOf(MethodCallBag::class, $bag1);
        self::assertNotSame($bag, $bag1);

        self::assertSame(
            [],
            $this->propRefl->getValue($bag),
        );
        self::assertSame(
            [
                $methodCall1,
            ],
            $this->propRefl->getValue($bag1),
        );
        self::assertSame(
            [
                $methodCall1,
                $methodCall2,
            ],
            $this->propRefl->getValue($bag2),
        );
    }

    /**
     * @testdox When calls overlaps, they are stacked
     */
    public function testStackCalls(): void
    {
        $methodCall1 = new DummyMethodCall('mc1');
        $methodCall2 = new DummyMethodCall('mc1');

        $bag1 = (new MethodCallBag())->with($methodCall1);
        $bag2 = $bag1->with($methodCall2);

        self::assertSame(
            [
                $methodCall1,
            ],
            $this->propRefl->getValue($bag1),
        );
        self::assertSame(
            [
                $methodCall1,
                $methodCall2,
            ],
            $this->propRefl->getValue($bag2),
        );
    }

    /**
     * @testdox Can merge two bags. When calls overlaps, they are stacked.
     */
    public function testMergeTwoBags(): void
    {
        $callA1 = new SimpleMethodCall('setUsername', []);
        $callA2 = new SimpleMethodCall('setOwner', []);

        $callB1 = new SimpleMethodCall('setUsername', []);
        $callB2 = new SimpleMethodCall('setMail', []);

        $bagA = (new MethodCallBag())
            ->with($callA1)
            ->with($callA2);
        $bagB = (new MethodCallBag())
            ->with($callB1)
            ->with($callB2);

        $bag = $bagA->mergeWith($bagB);

        self::assertInstanceOf(MethodCallBag::class, $bag);
        self::assertSame(
            [
                $callA1,
                $callA2,
            ],
            $this->propRefl->getValue($bagA),
        );
        self::assertSame(
            [
                $callB1,
                $callB2,
            ],
            $this->propRefl->getValue($bagB),
        );
        self::assertSame(
            [
                $callB1,
                $callB2,
                $callA1,
                $callA2,
            ],
            $this->propRefl->getValue($bag),
        );
    }

    public function testIsEmpty(): void
    {
        $bag = new MethodCallBag();
        self::assertTrue($bag->isEmpty());

        $bag = $bag->with(new FakeMethodCall());
        self::assertFalse($bag->isEmpty());
    }
}
