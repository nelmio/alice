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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Entity\DummyWithMethods;
use Nelmio\Alice\Entity\EmptyDummy;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Nelmio\Alice\Generator\NamedArgumentsResolver
 */
class NamedArgumentsResolverTest extends TestCase
{
    /**
     * @dataProvider provideResolveArgumentsCases
     */
    public function testResolveArguments(string $className, string $methodName, array $argument, array $expectedResult): void
    {
        $resolver = new NamedArgumentsResolver();

        static::assertSame(
            $expectedResult,
            $resolver->resolveArguments($argument, $className, $methodName)
        );
    }

    public function provideResolveArgumentsCases()
    {
        yield 'constructor: no named arguments' => [
            DummyWithMethods::class,
            '__construct',
            [
                'value 1',
                'value 2',
            ],
            [
                'value 1',
                'value 2',
            ],
        ];

        yield 'constructor: named arguments' => [
            DummyWithMethods::class,
            '__construct',
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'constructor: named arguments in wrong order' => [
            DummyWithMethods::class,
            '__construct',
            [
                'foo2' => 'value 2',
                'foo1' => 'value 1',
            ],
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'constructor: mix of anonymous and named arguments' => [
            DummyWithMethods::class,
            '__construct',
            [
                'value 1',
                'foo2' => 'value 2',
            ],
            [
                'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'constructor: mix of anonymous and named arguments in wrong order' => [
            DummyWithMethods::class,
            '__construct',
            [
                'foo2' => 'value 2',
                'value 1',
            ],
            [
                'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'static factory: no named arguments' => [
            DummyWithMethods::class,
            'create',
            [
                'value 1',
                'value 2',
            ],
            [
                'value 1',
                'value 2',
            ],
        ];

        yield 'static factory: named arguments' => [
            DummyWithMethods::class,
            'create',
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'static factory: named arguments in wrong order' => [
            DummyWithMethods::class,
            'create',
            [
                'foo2' => 'value 2',
                'foo1' => 'value 1',
            ],
            [
                'foo1' => 'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'static factory: mix of anonymous and named arguments' => [
            DummyWithMethods::class,
            'create',
            [
                'value 1',
                'foo2' => 'value 2',
            ],
            [
                'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'static factory: mix of anonymous and named arguments in wrong order' => [
            DummyWithMethods::class,
            'create',
            [
                'foo2' => 'value 2',
                'value 1',
            ],
            [
                'value 1',
                'foo2' => 'value 2',
            ],
        ];

        yield 'method call: no named arguments' => [
            DummyWithMethods::class,
            'bar',
            [
                'value 1',
                'value 2',
            ],
            [
                'value 1',
                'value 2',
            ],
        ];

        yield 'method call: named arguments' => [
            DummyWithMethods::class,
            'bar',
            [
                'bar1' => 'value 1',
                'bar2' => 'value 2',
            ],
            [
                'bar1' => 'value 1',
                'bar2' => 'value 2',
            ],
        ];

        yield 'method call: named arguments in wrong order' => [
            DummyWithMethods::class,
            'bar',
            [
                'bar2' => 'value 2',
                'bar1' => 'value 1',
            ],
            [
                'bar1' => 'value 1',
                'bar2' => 'value 2',
            ],
        ];

        yield 'method call: mix of anonymous and named arguments' => [
            DummyWithMethods::class,
            'bar',
            [
                'value 1',
                'bar2' => 'value 2',
            ],
            [
                'value 1',
                'bar2' => 'value 2',
            ],
        ];

        yield 'method call: mix of anonymous and named arguments in wrong order' => [
            DummyWithMethods::class,
            'bar',
            [
                'bar2' => 'value 2',
                'value 1',
            ],
            [
                'value 1',
                'bar2' => 'value 2',
            ],
        ];

        yield 'with variadic argument' => [
            DummyWithMethods::class,
            'methodWithVariadic',
            [
                'baz2' => 'value 2',
                'value 1',
                'value 4',
                'baz3' => 'value 3',
                'value 5',
            ],
            [
                'value 1',
                'baz2' => 'value 2',
                'value 4',
                'value 3',
                'value 5',
            ],
        ];

        yield 'with missing arguments that have default values' => [
            DummyWithMethods::class,
            'methodWithDefaultValues',
            [
                'baz2' => 'value 2',
            ],
            [
                'value 1',
                'baz2' => 'value 2',
            ],
        ];

        yield 'with method that does not exist' => [
            EmptyDummy::class,
            '__construct',
            [
                'unknown1' => 'value 1',
                'unknown2' => 'value 2',
            ],
            [
                'unknown1' => 'value 1',
                'unknown2' => 'value 2',
            ],
        ];

        yield 'with numeric string keys' => [
            DummyWithMethods::class,
            '__construct',
            [
                '0' => 'value 1',
                '1' => 'value 2',
            ],
            [
                '0' => 'value 1',
                '1' => 'value 2',
            ],
        ];

        yield 'with null passed to an argument that has nullable type' => [
            DummyWithMethods::class,
            'methodWithNullables',
            [
                'bar1' => 'hello',
                'bar2' => null,
            ],
            [
                'bar1' => 'hello',
                'bar2' => null,
            ],
        ];
    }

    public function testThrowsExceptionWhenResolvingUnknownArguments(): void
    {
        $resolver = new NamedArgumentsResolver();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown arguments for Nelmio\Alice\Entity\DummyWithMethods::bar(): $unknown1, $unknown2.');

        $resolver->resolveArguments([
            'bar1' => 'value 1',
            'bar2' => 'value 2',
            'unknown1' => 'value 3',
            'unknown2' => 'value 4',
        ], DummyWithMethods::class, 'bar');
    }

    public function testThrowsExceptionWhenMissingArgumentsDontHaveDefaultValues(): void
    {
        $resolver = new NamedArgumentsResolver();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Argument $bar1 of Nelmio\Alice\Entity\DummyWithMethods::bar() is not passed a value and does not define a default one.');

        $resolver->resolveArguments([], DummyWithMethods::class, 'bar');
    }
}
