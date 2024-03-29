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

namespace Nelmio\Alice\Definition\Object;

use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\ObjectInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\Object\SimpleObject
 * @internal
 */
class SimpleObjectTest extends TestCase
{
    /**
     * @var ReflectionProperty
     */
    private $propRefl;

    protected function setUp(): void
    {
        $this->propRefl = (new ReflectionClass(SimpleObject::class))->getProperty('instance');
        $this->propRefl->setAccessible(true);
    }

    public function testIsAnObject(): void
    {
        self::assertTrue(is_a(SimpleObject::class, ObjectInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = 'user0';
        $instance = new stdClass();

        $object = new SimpleObject($reference, $instance);

        self::assertEquals($reference, $object->getId());
        self::assertEquals($instance, $object->getInstance());
    }

    public function testIsNotImmutable(): void
    {
        $reference = 'user0';
        $instance = new stdClass();

        $object = new SimpleObject($reference, $instance);

        // Mutate injected values
        $instance->foo = 'bar';

        // Mutate returned values
        $object->getInstance()->ping = 'pong';

        $expected = StdClassFactory::create(['foo' => 'bar', 'ping' => 'pong']);
        $actual = $object->getInstance();

        self::assertEquals($expected, $actual);
    }

    public function testNamedConstructor(): void
    {
        $reference = 'user0';
        $instance = StdClassFactory::create(['original' => true]);
        $originalInstance = clone $instance;
        $object = new SimpleObject($reference, $instance);

        $newInstance = StdClassFactory::create(['original' => false]);
        $originalNewInstance = clone $newInstance;
        $newObject = $object->withInstance($newInstance);

        self::assertEquals(new SimpleObject($reference, $originalInstance), $object);
        self::assertEquals(new SimpleObject($reference, $originalNewInstance), $newObject);
    }
}
