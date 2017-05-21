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

/**
 * @covers \Nelmio\Alice\Definition\Object\SimpleObject
 */
class SimpleObjectTest extends TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->propRefl = (new \ReflectionClass(SimpleObject::class))->getProperty('instance');
        $this->propRefl->setAccessible(true);
    }

    public function testIsAnObject()
    {
        $this->assertTrue(is_a(SimpleObject::class, ObjectInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = 'user0';
        $instance = new \stdClass();

        $object = new SimpleObject($reference, $instance);

        $this->assertEquals($reference, $object->getId());
        $this->assertEquals($instance, $object->getInstance());
    }

    public function testIsNotImmutable()
    {
        $reference = 'user0';
        $instance = new \stdClass();

        $object = new SimpleObject($reference, $instance);

        // Mutate injected values
        $instance->foo = 'bar';

        // Mutate returned values
        $object->getInstance()->ping = 'pong';

        $expected = StdClassFactory::create(['foo' => 'bar', 'ping' => 'pong']);
        $actual = $object->getInstance();

        $this->assertEquals($expected, $actual);
    }

    public function testNamedConstructor()
    {
        $reference = 'user0';
        $instance = StdClassFactory::create(['original' => true]);
        $originalInstance = clone $instance;
        $object = new SimpleObject($reference, $instance);

        $newInstance = StdClassFactory::create(['original' => false]);
        $originalNewInstance = clone $newInstance;
        $newObject = $object->withInstance($newInstance);

        $this->assertEquals(new SimpleObject($reference, $originalInstance), $object);
        $this->assertEquals(new SimpleObject($reference, $originalNewInstance), $newObject);
    }

    /**
     * @dataProvider provideInvalidInstances
     *
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^Expected instance argument to be an object. Got ".+?" instead\.$/
     */
    public function testThrowsAnErrorIfInstanceIsNotAnObject($instance)
    {
        new SimpleObject('user0', $instance);
    }

    public function provideInvalidInstances()
    {
        return [
            'null' => [null],
            'string' => ['string value'],
            'int' => [10],
            'float' => [1.01],
        ];
    }
}
