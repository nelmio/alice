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
/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Object;

use Nelmio\Alice\Definition\Value\FakeObject;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\ObjectInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Object\CompleteObject
 */
class CompleteObjectTest extends TestCase
{
    public function testIsAnObject()
    {
        $this->assertTrue(is_a(CompleteObject::class, ObjectInterface::class, true));
    }

    public function testDecoratesAnObject()
    {
        $decoratedObjectProphecy = $this->prophesize(ObjectInterface::class);
        $decoratedObjectProphecy->getId()->willReturn('dummy');
        $decoratedObjectProphecy->getInstance()->willReturn(new \stdClass());
        /** @var ObjectInterface $decoratedObject */
        $decoratedObject = $decoratedObjectProphecy->reveal();

        $object = new CompleteObject($decoratedObject);

        $this->assertEquals('dummy', $object->getId());
        $decoratedObjectProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedObjectProphecy->getInstance()->shouldHaveBeenCalledTimes(0);

        $this->assertEquals(new \stdClass(), $object->getInstance());
        $decoratedObjectProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedObjectProphecy->getInstance()->shouldHaveBeenCalledTimes(1);
    }

    public function testDelegatesImmutabilityToTheDecoratedObject()
    {
        // Case where the decorated object is mutable
        $decoratedObject = new SimpleObject('dummy', $instance = new \stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        $this->assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'fao' => 'bor',
                'faz' => 'boz',
            ]),
            $object->getInstance()
        );
        $this->assertEquals(
            $object->getInstance(),
            $clone->getInstance()
        );


        // Case where the decorated object is partially immutable: cloning does create a new instance
        $decoratedObject = new ImmutableByCloneObject('dummy', $instance = new \stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        $this->assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'fao' => 'bor',
            ]),
            $object->getInstance()
        );
        $this->assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'faz' => 'boz',
            ]),
            $clone->getInstance()
        );


        // Case where the decorated object is truly immutable
        $decoratedObject = new ImmutableObject('dummy', $instance = new \stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        $this->assertEquals(new \stdClass(), $object->getInstance());
        $this->assertEquals(new \stdClass(), $clone->getInstance());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot create a new object from a complete object.
     */
    public function testCannotCreateANewInstance()
    {
        $object = new CompleteObject(new FakeObject());

        $object->withInstance(null);
    }
}
