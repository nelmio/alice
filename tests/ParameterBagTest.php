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

namespace Nelmio\Alice;

use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

/**
 * @covers \Nelmio\Alice\ParameterBag
 * @internal
 */
class ParameterBagTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $parameters = [
            'foo' => 'bar',
            'ping' => 'pong',
        ];

        $bag = new ParameterBag($parameters);

        self::assertTrue($bag->has('foo'));
        self::assertTrue($bag->has('ping'));

        self::assertFalse($bag->has('bar'));
        self::assertFalse($bag->has('pong'));

        self::assertEquals('bar', $bag->get('foo'));
        self::assertEquals('pong', $bag->get('ping'));

        $this->assertBagSize(2, $bag);
    }

    public function testThrowsAnExceptionWhenATryingToGetAnInexistingParameter(): void
    {
        $this->expectException(ParameterNotFoundException::class);
        $this->expectExceptionMessage('Could not find the parameter "foo".');

        $bag = new ParameterBag();
        $bag->get('foo');
    }

    public function testIsImmutable(): void
    {
        $bag = new ParameterBag([
            'foo' => $std = new stdClass(),
        ]);

        // Mutate injected object
        $std->foo = 'bar';

        // Mutate retrieved object
        $bag->get('foo')->foo = 'baz';

        self::assertEquals(
            new ParameterBag([
                'foo' => new stdClass(),
            ]),
            $bag,
        );
    }

    /**
     * @depends \Nelmio\Alice\ParameterTest::testIsImmutable
     */
    public function testWithersReturnNewModifiedObject(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $newBag = $bag->with(new Parameter('ping', 'pong'));

        self::assertEquals(new ParameterBag(['foo' => 'bar']), $bag);
        self::assertEquals(new ParameterBag(['foo' => 'bar', 'ping' => 'pong']), $newBag);
    }

    public function testIfTwoParametersWithTheSameKeyAreAddedThenTheNewerOneWillBeDiscarded(): void
    {
        $bag = (new ParameterBag([
            'foo' => 'bar',
            'ping' => 'pong',
        ]))
            ->with(new Parameter('ping', 'boo'))
            ->with(new Parameter('he', 'ho'));

        self::assertEquals('bar', $bag->get('foo'));
        self::assertEquals('pong', $bag->get('ping'));
        self::assertEquals('ho', $bag->get('he'));

        $this->assertBagSize(3, $bag);
    }

    public function testIsTraversable(): void
    {
        $params = [
            'foo' => 'bar',
            'ping' => 'pong',
        ];

        $bag = new ParameterBag($params);

        $traversed = [];
        foreach ($bag as $key => $param) {
            $traversed[$key] = $param;
        }

        self::assertSame($params, $traversed);
    }

    public function testIsCountable(): void
    {
        $bag = new ParameterBag();
        self::assertCount(0, $bag);

        $bag = $bag
            ->with(new Parameter('foo', 'bar'))
            ->with(new Parameter('ping', 'pong'));
        self::assertCount(2, $bag);
    }

    public function testCanRemoveElements(): void
    {
        $bag = (new ParameterBag(['foo' => 'bar']))->without('foo')->without('foo');

        self::assertEquals(new ParameterBag(), $bag);
    }

    private function assertBagSize(int $size, ParameterBag $bag): void
    {
        $reflectionObject = new ReflectionObject($bag);
        $paramReflection = $reflectionObject->getProperty('parameters');
        $paramReflection->setAccessible(true);

        self::assertCount($size, $paramReflection->getValue($bag));
    }

    public function testToArray(): void
    {
        $bag = new ParameterBag();

        self::assertEquals([], $bag->toArray());

        $bag = new ParameterBag([
            'foo' => 'bar',
            'baz' => $std = new stdClass(),
        ]);

        self::assertEquals(
            [
                'foo' => 'bar',
                'baz' => $std,
            ],
            $bag->toArray(),
        );
    }
}
