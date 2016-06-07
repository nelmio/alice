<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;

/**
 * @covers Nelmio\Alice\Definition\MethodCallBag
 */
class MethodCallBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $refl = new \ReflectionClass(MethodCallBag::class);
        $propRefl = $refl->getProperty('methodCalls');
        $propRefl->setAccessible(true);

        $this->propRefl = $propRefl;
    }

    public function testImmutableMutator()
    {
        $methodCall1 = new DummyMethodCall('mc1');
        $methodCall2 = new DummyMethodCall('mc2');
        $methodCall3 = new DummyMethodCall('mc2');

        $bag = new MethodCallBag();
        $bag1 = $bag->with($methodCall1);
        $bag2 = $bag1->with($methodCall2);
        $bag3 = $bag2->with($methodCall3);

        $this->assertInstanceOf(MethodCallBag::class, $bag1);
        $this->assertNotSame($bag, $bag1);

        $this->assertSame(
            [],
            $this->propRefl->getValue($bag)
        );
        $this->assertSame(
            [
                'mc1' => $methodCall1,
            ],
            $this->propRefl->getValue($bag1)
        );
        $this->assertSame(
            [
                'mc1' => $methodCall1,
                'mc2' => $methodCall2,
            ],
            $this->propRefl->getValue($bag2)
        );
        $this->assertSame(
            [
                'mc1' => $methodCall1,
                'mc2' => $methodCall3,
            ],
            $this->propRefl->getValue($bag3)
        );
    }
}
