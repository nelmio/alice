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

use LogicException;
use Nelmio\Alice\Definition\Value\FakeObject;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\ObjectInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\Object\CompleteObject
 */
class CompleteObjectTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAnObject(): void
    {
        static::assertTrue(is_a(CompleteObject::class, ObjectInterface::class, true));
    }

    public function testDecoratesAnObject(): void
    {
        $decoratedObjectProphecy = $this->prophesize(ObjectInterface::class);
        $decoratedObjectProphecy->getId()->willReturn('dummy');
        $decoratedObjectProphecy->getInstance()->willReturn(new stdClass());
        /** @var ObjectInterface $decoratedObject */
        $decoratedObject = $decoratedObjectProphecy->reveal();

        $object = new CompleteObject($decoratedObject);

        static::assertEquals('dummy', $object->getId());
        $decoratedObjectProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedObjectProphecy->getInstance()->shouldHaveBeenCalledTimes(0);

        static::assertEquals(new stdClass(), $object->getInstance());
        $decoratedObjectProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedObjectProphecy->getInstance()->shouldHaveBeenCalledTimes(1);
    }

    public function testDelegatesImmutabilityToTheDecoratedObject(): void
    {
        // Case where the decorated object is mutable
        $decoratedObject = new SimpleObject('dummy', $instance = new stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        static::assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'fao' => 'bor',
                'faz' => 'boz',
            ]),
            $object->getInstance()
        );
        static::assertEquals(
            $object->getInstance(),
            $clone->getInstance()
        );


        // Case where the decorated object is partially immutable: cloning does create a new instance
        $decoratedObject = new ImmutableByCloneObject('dummy', $instance = new stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        static::assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'fao' => 'bor',
            ]),
            $object->getInstance()
        );
        static::assertEquals(
            StdClassFactory::create([
                'foo' => 'bar',
                'foz' => 'baz',
                'faz' => 'boz',
            ]),
            $clone->getInstance()
        );


        // Case where the decorated object is truly immutable
        $decoratedObject = new ImmutableObject('dummy', $instance = new stdClass());

        $object = new CompleteObject($decoratedObject);
        $instance->foo = 'bar';
        $object->getInstance()->foz = 'baz';

        $clone = clone $object;
        $instance->fao = 'bor';
        $clone->getInstance()->faz = 'boz';

        static::assertEquals(new stdClass(), $object->getInstance());
        static::assertEquals(new stdClass(), $clone->getInstance());
    }

    public function testCannotCreateANewInstance(): void
    {
        $object = new CompleteObject(new FakeObject());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create a new object from a complete object.');

        $object->withInstance(new stdClass());
    }
}
