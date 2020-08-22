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

        static::assertTrue($bag->has('foo'));
        static::assertTrue($bag->has('ping'));

        static::assertFalse($bag->has('bar'));
        static::assertFalse($bag->has('pong'));

        static::assertEquals('bar', $bag->get('foo'));
        static::assertEquals('pong', $bag->get('ping'));

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

        static::assertEquals(
            new ParameterBag([
                'foo' => new stdClass(),
            ]),
            $bag
        );
    }

    /**
     * @depends \Nelmio\Alice\ParameterTest::testIsImmutable
     */
    public function testWithersReturnNewModifiedObject(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $newBag = $bag->with(new Parameter('ping', 'pong'));

        static::assertEquals(new ParameterBag(['foo' => 'bar']), $bag);
        static::assertEquals(new ParameterBag(['foo' => 'bar', 'ping' => 'pong']), $newBag);
    }

    public function testIfTwoParametersWithTheSameKeyAreAddedThenTheNewerOneWillBeDiscarded(): void
    {
        $bag = (new ParameterBag([
                'foo' => 'bar',
                'ping' => 'pong',
            ]))
            ->with(new Parameter('ping', 'boo'))
            ->with(new Parameter('he', 'ho'))
        ;

        static::assertEquals('bar', $bag->get('foo'));
        static::assertEquals('pong', $bag->get('ping'));
        static::assertEquals('ho', $bag->get('he'));

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

        static::assertSame($params, $traversed);
    }

    public function testIsCountable(): void
    {
        $bag = new ParameterBag();
        static::assertCount(0, $bag);

        $bag = $bag
            ->with(new Parameter('foo', 'bar'))
            ->with(new Parameter('ping', 'pong'))
        ;
        static::assertCount(2, $bag);
    }

    public function testCanRemoveElements(): void
    {
        $bag = (new ParameterBag(['foo' => 'bar']))->without('foo')->without('foo');

        static::assertEquals(new ParameterBag(), $bag);
    }

    private function assertBagSize(int $size, ParameterBag $bag): void
    {
        $reflectionObject = new ReflectionObject($bag);
        $paramReflection = $reflectionObject->getProperty('parameters');
        $paramReflection->setAccessible(true);

        static::assertCount($size, $paramReflection->getValue($bag));
    }

    public function testToArray(): void
    {
        $bag = new ParameterBag();

        static::assertEquals([], $bag->toArray());

        $bag = new ParameterBag([
            'foo' => 'bar',
            'baz' => $std = new stdClass(),
        ]);

        static::assertEquals(
            [
                'foo' => 'bar',
                'baz' => $std,
            ],
            $bag->toArray()
        );
    }
}
