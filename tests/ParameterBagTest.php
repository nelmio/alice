<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

/**
 * @covers Nelmio\Alice\ParameterBag
 */
class ParameterBagTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndConsumeParametersBag()
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
     * @expectedException \Nelmio\Alice\Exception\ParameterNotFoundException
     * @expectedExceptionMessage Could not find the parameter "foo".
     */
    public function testThrowExceptionWhenParameterNotFound()
    {
        $bag = new ParameterBag();
        $bag->get('foo');
    }

    public function testIsImmutable()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bagClone = clone $bag;
        $newBag = $bag->with(new Parameter('ping', 'pong'));

        $this->assertEquals($bagClone, $bag);
        $this->assertNotEquals($newBag, $bag);
        $this->assertNotSame($newBag, $bag);

        $anotherBag = $bag->without('foo');
        $this->assertNotSame($anotherBag, $bag);
    }

    public function testAddingParametersDoesNotOverrideExistingOnes()
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
        $this->assertEquals(0, count($bag));
        
        $bag = $bag
            ->with(new Parameter('foo', 'bar'))
            ->with(new Parameter('ping', 'pong'))
        ;
        $this->assertEquals(2, count($bag));
    }

    public function testCanRemoveElements()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $this->assertTrue($bag->has('foo'));

        $bag = $bag->without('foo');
        $this->assertFalse($bag->has('foo'));

        $bag->without('foo');
        $this->assertTrue(true);
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

        $this->assertSame([], $bag->toArray());

        $bag = new ParameterBag([
            'foo' => 'bar',
            'baz' => $std = new \stdClass(),
        ]);

        $this->assertSame(
            [
                'foo' => 'bar',
                'baz' => $std,
            ],
            $bag->toArray()
        );
    }
}
