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
    public function testCreateInvalidParameter()
    {
        $this->markTestIncomplete('TODO');
    }
    
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

    public function testIsDeepClonable()
    {
        $parameter = new Parameter('foo', null);
        $newParameter = clone $parameter;

        $this->assertInstanceOf(Parameter::class, $newParameter);
        $this->assertNotSame($parameter, $newParameter);

        $parameter = new Parameter('foo', new \stdClass());
        $newParameter = clone $parameter;

        $this->assertInstanceOf(Parameter::class, $newParameter);
        $this->assertNotSame($parameter, $newParameter);
        $this->assertNotSameValue($parameter, $newParameter);

        $parameter = new Parameter('foo', function () {});
        $newParameter = clone $parameter;

        $this->assertInstanceOf(Parameter::class, $newParameter);
        $this->assertNotSame($parameter, $newParameter);
        $this->assertNotSameValue($parameter, $newParameter);
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

    private function assertNotSameValue(Parameter $firstParameter, Parameter $secondParameter)
    {
        $this->assertNotSame(
            $this->getValue($firstParameter),
            $this->getValue($secondParameter)
        );
    }

    private function getValue(Parameter $parameter)
    {
        $reflectionObject = new \ReflectionObject($parameter);
        $propertyReflection = $reflectionObject->getProperty('value');
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($parameter);
    }
}
