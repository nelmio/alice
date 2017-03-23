<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Fixtures\ParameterBag;
use Nelmio\Alice\Instances\Processor\Processable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Instances\Processor\Methods\Parameterized
 */
class ParameterizedTest extends TestCase
{
    /**
     * @var Parameterized
     */
    private $method;

    public function setUp()
    {
        $this->method = new Parameterized(new ParameterBag());
    }

    public function testIsAProcessorMethod()
    {
        $this->assertInstanceOf('Nelmio\Alice\Instances\Processor\Methods\MethodInterface', $this->method);
    }

    /**
     * @dataProvider provideProcessables
     */
    public function testCanProcess($processable, $expected)
    {
        $actual = $this->method->canProcess($processable);

        $this->assertEquals($expected, $actual);
    }

    public function testProcessSimpleParameter()
    {
        $parameters = new ParameterBag([
            'foo' => 'bar',
        ]);
        $method = new Parameterized($parameters);

        $processable = new Processable('<{foo}>');
        $expected = 'bar';

        $method->canProcess($processable);
        $actual = $method->process($processable, []);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowExceptionIfNoParameterKeyFound()
    {
        $parameters = new ParameterBag([]);
        $method = new Parameterized($parameters);

        $processable = new Processable('<{}>');
        $method->canProcess($processable);
        $method->process($processable, []);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowExceptionIfParameterNotFound()
    {
        $parameters = new ParameterBag([]);
        $method = new Parameterized($parameters);

        $processable = new Processable('<{foo}>');
        $method->canProcess($processable);
        $method->process($processable, []);
    }

    public function provideProcessables()
    {
        return [
            'regular' => [
                new Processable('<{foo}>'),
                true,
            ],
            'empty' => [
                new Processable('<{}>'),
                true,
            ],
            'composite' => [
                new Processable('<{<{part1}> <{part2}>}>'),
                true,
            ],
            'successive' => [
                new Processable('<{foo}> <{bar}>'),
                true,
            ],
            'dynamic' => [
                new Processable('<{username_<current()>}>'),
                true,
            ],

            'regular string' => [
                new Processable('hello!'),
                false,
            ],
            'string with function' => [
                new Processable('<current()>'),
                false,
            ],
        ];
    }
}
