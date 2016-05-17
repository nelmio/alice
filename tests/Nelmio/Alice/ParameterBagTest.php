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
    public function test_create_parameters_bag()
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
     * @expectedException \Nelmio\Alice\Exception\ParameterNotFound
     * @expectedExceptionMessage No parameter with the key "foo" found.
     */
    public function test_throw_exception_when_parameter_not_found()
    {
        $bag = new ParameterBag([]);
        $bag->get('foo');
    }

    public function test_bag_is_immutable()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bagClone = clone $bag;
        $newBag = $bag->with(['ping' => 'pong']);

        $this->assertEquals($bagClone, $bag);
        $this->assertNotSame($newBag, $bag);
    }

    public function test_adding_parameters_does_not_override_existing_ones()
    {
        $bag = (new ParameterBag([]))
            ->with([
                'foo' => 'bar',
                'ping' => 'pong',
            ])
            ->with([
                'ping' => 'boo',
                'he' => 'ho',
            ])
        ;

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertEquals('pong', $bag->get('ping'));
        $this->assertEquals('ho', $bag->get('he'));

        $this->assertBagSize(3, $bag);
    }

    private function assertBagSize(int $size, ParameterBag $bag)
    {
        $reflectionObject = new \ReflectionObject($bag);
        $paramReflection = $reflectionObject->getProperty('parameters');
        $paramReflection->setAccessible(true);

        $this->assertCount($size, $paramReflection->getValue($bag));
    }
}
