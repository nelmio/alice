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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\ParameterBag
 */
class ParameterBagTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $parameters = [
            'foo' => 'bar',
            'ping' => 'pong',
        ];

        $bag = new ParameterBag($parameters);

        $this->assertTrue($bag->has('foo'));
        $this->assertTrue($bag->has('ping'));

        $this->assertFalse($bag->has('bar'));
        $this->assertFalse($bag->has('pong'));

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertEquals('pong', $bag->get('ping'));

        $this->assertBagSize(2, $bag);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\ParameterNotFoundException
     * @expectedExceptionMessage Could not find the parameter "foo".
     */
    public function testThrowsAnExceptionWhenATryingToGetAnInexistingParameter()
    {
        $bag = new ParameterBag();
        $bag->get('foo');
    }

    public function testIsImmutable()
    {
        $bag = new ParameterBag([
            'foo' => $std = new \stdClass(),
        ]);

        // Mutate injected object
        $std->foo = 'bar';

        // Mutate retrieved object
        $bag->get('foo')->foo = 'baz';

        $this->assertEquals(
            new ParameterBag([
                'foo' => new \stdClass(),
            ]),
            $bag
        );
    }

    /**
     * @depends Nelmio\Alice\ParameterTest::testIsImmutable
     */
    public function testWithersReturnNewModifiedObject()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $newBag = $bag->with(new Parameter('ping', 'pong'));

        $this->assertEquals(new ParameterBag(['foo' => 'bar']), $bag);
        $this->assertEquals(new ParameterBag(['foo' => 'bar', 'ping' => 'pong']), $newBag);
    }

    public function testIfTwoParametersWithTheSameKeyAreAddedThenTheNewerOneWillBeDiscarded()
    {
        $bag = (new ParameterBag([
                'foo' => 'bar',
                'ping' => 'pong',
            ]))
            ->with(new Parameter('ping', 'boo'))
            ->with(new Parameter('he', 'ho'))
        ;

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertEquals('pong', $bag->get('ping'));
        $this->assertEquals('ho', $bag->get('he'));

        $this->assertBagSize(3, $bag);
    }

    public function testIsTraversable()
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

        $this->assertSame($params, $traversed);
    }

    public function testIsCountable()
    {
        $bag = new ParameterBag();
        $this->assertCount(0, $bag);

        $bag = $bag
            ->with(new Parameter('foo', 'bar'))
            ->with(new Parameter('ping', 'pong'))
        ;
        $this->assertCount(2, $bag);
    }

    public function testCanRemoveElements()
    {
        $bag = (new ParameterBag(['foo' => 'bar']))->without('foo')->without('foo');

        $this->assertEquals(new ParameterBag(), $bag);
    }

    private function assertBagSize(int $size, ParameterBag $bag)
    {
        $reflectionObject = new \ReflectionObject($bag);
        $paramReflection = $reflectionObject->getProperty('parameters');
        $paramReflection->setAccessible(true);

        $this->assertCount($size, $paramReflection->getValue($bag));
    }

    public function testToArray()
    {
        $bag = new ParameterBag();

        $this->assertEquals([], $bag->toArray());

        $bag = new ParameterBag([
            'foo' => 'bar',
            'baz' => $std = new \stdClass(),
        ]);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'baz' => $std,
            ],
            $bag->toArray()
        );
    }
}
