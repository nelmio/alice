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
 * @covers Nelmio\Alice\Parameter
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValues
     */
    public function testAccessors($value)
    {
        $parameter = new Parameter('foo', $value);

        $this->assertEquals('foo', $parameter->getKey());
        $this->assertEquals($value, $parameter->getValue());
    }

    public function testIsImmutable()
    {
        $parameter = new Parameter('foo', new \stdClass());

        $this->assertNotSame($parameter->getValue(), $parameter->getValue());
    }

    public function testImmutableMutator()
    {
        $parameter = new Parameter('foo', 'bar');
        $newParam = $parameter->withValue('rab');

        $this->assertNotSame($newParam, $parameter);
        $this->assertEquals('bar', $parameter->getValue());
        $this->assertEquals('rab', $newParam->getValue());
    }

    public function provideValues()
    {
        return [
            'boolean' => [true],
            'integer' => [10],
            'float' => [.5],
            'string' => ['foo'],
            'null' => [null],
            'object' => [new \stdClass()],
            'closure' => [function () {}],
            'array' => [[new \stdClass()]],
        ];
    }
}
