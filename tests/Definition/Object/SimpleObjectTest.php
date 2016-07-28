<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Object;

use Nelmio\Alice\ObjectInterface;

/**
 * @covers Nelmio\Alice\Definition\Object\SimpleObject
 */
class SimpleObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $this->propRefl = (new \ReflectionClass(SimpleObject::class))->getProperty('instance');
        $this->propRefl->setAccessible(true);
    }

    public function testIsAnObject()
    {
        $this->assertTrue(is_a(SimpleObject::class, ObjectInterface::class, true));
    }

    public function testAccessors()
    {
        $reference = 'user0';
        $instance = new \stdClass();

        $object = new SimpleObject($reference, $instance);

        $this->assertEquals($reference, $object->getReference());
        $this->assertEquals($instance, $object->getInstance());
    }

    public function testIsImmutable()
    {
        $reference = 'user0';
        $instance = new \stdClass();

        $object = new SimpleObject($reference, $instance);

        $this->assertNotSame($object->getInstance(), $object->getInstance());
    }

    /**
     * @dataProvider provideInvalidInstances
     *
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^Expected instance argument to be an object, got ".+?" instead\.$/
     */
    public function testThrowAnErrorIfInstanceIsNotAnObject($instance)
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

    public function testIsDeepClonable()
    {
        $reference = 'user0';
        $instance = new \stdClass();

        $object = new SimpleObject($reference, $instance);
        $clone = clone $object;

        $this->assertInstanceOf(SimpleObject::class, $clone);
        $this->assertEquals($object, $clone);

        $this->assertNotSameInstace($object, $clone);
    }

    private function assertNotSameInstace(SimpleObject $object1, SimpleObject $object2)
    {
        $this->assertNotSame(
            $this->propRefl->getValue($object1),
            $this->propRefl->getValue($object2)
        );
    }
}
