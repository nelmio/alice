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
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;

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

    public function testMergeTwoBags()
    {
        $callA1 = new SimpleMethodCall('setUsername', []);
        $callA2 = new SimpleMethodCall('setOwner', []);

        $callB1 = new SimpleMethodCall('setUsername', []);
        $callB2 = new SimpleMethodCall('setMail', []);

        $bagA = (new MethodCallBag())
            ->with($callA1)
            ->with($callA2)
        ;
        $bagB = (new MethodCallBag())
            ->with($callB1)
            ->with($callB2)
        ;

        $bag = $bagA->mergeWith($bagB);

        $this->assertInstanceOf(MethodCallBag::class, $bag);
        $this->assertSame(
            [
                'setUsername' => $callA1,
                'setOwner' => $callA2,
            ],
            $this->propRefl->getValue($bagA)
        );
        $this->assertSame(
            [
                'setUsername' => $callB1,
                'setMail' => $callB2,
            ],
            $this->propRefl->getValue($bagB)
        );
        $this->assertSame(
            [
                'setUsername' => $callB1,
                'setOwner' => $callA2,
                'setMail' => $callB2,
            ],
            $this->propRefl->getValue($bag)
        );
    }
}
