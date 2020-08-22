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
use stdClass;

/**
 * @covers \Nelmio\Alice\Parameter
 */
class ParameterTest extends TestCase
{
    /**
     * @dataProvider provideValues
     */
    public function testAccessors($value): void
    {
        $parameter = new Parameter('foo', $value);

        static::assertEquals('foo', $parameter->getKey());
        static::assertEquals($value, $parameter->getValue());
    }

    public function testIsImmutable(): void
    {
        $parameter = new Parameter('foo', [$std = new stdClass()]);

        // Mutate injected object
        $std->foo = 'bar';

        // Mutate retrieved object
        $parameter->getValue()[0]->foo = 'baz';

        static::assertEquals(new Parameter('foo', [new stdClass()]), $parameter);
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $parameter = new Parameter('foo', 'bar');
        $newParam = $parameter->withValue('rab');

        static::assertNotSame($newParam, $parameter);
        static::assertEquals('bar', $parameter->getValue());
        static::assertEquals('rab', $newParam->getValue());
    }

    public function provideValues()
    {
        return [
            'boolean' => [true],
            'integer' => [10],
            'float' => [.5],
            'string' => ['foo'],
            'null' => [null],
            'object' => [new stdClass()],
            'closure' => [function (): void {
            }],
            'array' => [[new stdClass()]],
        ];
    }
}
