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

namespace Nelmio\Alice\Definition;

use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Property::class)]
final class PropertyTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $property = 'username';
        $value = new stdClass();
        $definition = new Property($property, $value);

        self::assertEquals($property, $definition->getName());
        self::assertEquals($value, $definition->getValue());
    }

    public function testIsMutable(): void
    {
        $value = new stdClass();
        $definition = new Property('username', $value);

        // Mutate injected value
        $value->foo = 'bar';

        // Mutate returned value
        $definition->getValue()->ping = 'pong';

        $expected = StdClassFactory::create(['foo' => 'bar', 'ping' => 'pong']);
        $actual = $definition->getValue();

        self::assertEquals($expected, $actual);
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $name = 'username';
        $definition = new Property($name, 'foo');
        $newDefinition = $definition->withValue(new stdClass());

        self::assertEquals(
            new Property($name, 'foo'),
            $definition,
        );
        self::assertEquals(
            new Property($name, new stdClass()),
            $newDefinition,
        );
    }
}
