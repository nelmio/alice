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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Parameter::class)]
final class ParameterTest extends TestCase
{
    #[DataProvider('provideValues')]
    public function testAccessors($value): void
    {
        $parameter = new Parameter('foo', $value);

        self::assertEquals('foo', $parameter->getKey());
        self::assertEquals($value, $parameter->getValue());
    }

    public function testIsImmutable(): void
    {
        $parameter = new Parameter('foo', [$std = new stdClass()]);

        // Mutate injected object
        $std->foo = 'bar';

        // Mutate retrieved object
        $parameter->getValue()[0]->foo = 'baz';

        self::assertEquals(new Parameter('foo', [new stdClass()]), $parameter);
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $parameter = new Parameter('foo', 'bar');
        $newParam = $parameter->withValue('rab');

        self::assertNotSame($newParam, $parameter);
        self::assertEquals('bar', $parameter->getValue());
        self::assertEquals('rab', $newParam->getValue());
    }

    public static function provideValues(): iterable
    {
        return [
            'boolean' => [true],
            'integer' => [10],
            'float' => [.5],
            'string' => ['foo'],
            'null' => [null],
            'object' => [new stdClass()],
            'array' => [[new stdClass()]],
        ];
    }
}
