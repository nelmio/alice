<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Exception\FixtureNotFoundException;

/**
 * @covers Nelmio\Alice\FixtureBag
 */
class FixtureBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $propRelf = (new \ReflectionClass(FixtureBag::class))->getProperty('fixtures');
        $propRelf->setAccessible(true);

        $this->propRefl = $propRelf;
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $bag = (new FixtureBag())->with($fixture);

        $this->assertTrue($bag->has('foo'));
        $this->assertFalse($bag->has('bar'));

        $this->assertEquals($fixture, $bag->get('foo'));
        try {
            $bag->get('bar');
            $this->fail('Expected exception to be thrown.');
        } catch (FixtureNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the fixture "bar".',
                $exception->getMessage()
            );
        }
    }

    public function testMutatorsAreImmutable()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $bag = new FixtureBag();
        $newBag = $bag->with($fixture);

        $this->assertInstanceOf(FixtureBag::class, $newBag);
        $this->assertNotSame($newBag, $bag);

        $this->assertSameFixtures([], $bag);
        $this->assertSameFixtures(
            [
                'foo' => $fixture,
            ],
            $newBag
        );

        $fixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
    }

    public function testAddingSameFixtureOverridesTheOldEntry()
    {
        $fixtureProphecy1 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy1->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixtureProphecy1->reveal();

        $fixtureProphecy2 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy2->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixtureProphecy2->reveal();

        $bag = (new FixtureBag())
            ->with($fixture1)
            ->with($fixture2)
        ;

        $this->assertNotSameFixtures(
            [
                'foo' => $fixture1,
            ],
            $bag
        );
        $this->assertSameFixtures(
            [
                'foo' => $fixture2,
            ],
            $bag
        );
    }

    public function testMergeBags()
    {
        $fixtureProphecy1 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy1->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixtureProphecy1->reveal();

        $fixtureProphecy2 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy2->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixtureProphecy2->reveal();

        $fixtureProphecy3 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy3->getId()->willReturn('bar');
        /** @var FixtureInterface $fixture3 */
        $fixture3 = $fixtureProphecy3->reveal();

        $bag1 = (new FixtureBag())->with($fixture1);
        $bag2 = (new FixtureBag())
            ->with($fixture2)
            ->with($fixture3)
        ;
        $bag3 = $bag1->mergeWith($bag2);

        $this->assertInstanceOf(FixtureBag::class, $bag2);
        $this->assertSameFixtures(
            [
                'foo' => $fixture1,
            ],
            $bag1
        );
        $this->assertSameFixtures(
            [
                'foo' => $fixture2,
                'bar' => $fixture3,
            ],
            $bag2
        );
        $this->assertSameFixtures(
            [
                'foo' => $fixture2,
                'bar' => $fixture3,
            ],
            $bag3
        );
    }

    public function testIsIterable()
    {
        $fixtureProphecy1 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy1->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixtureProphecy1->reveal();

        $fixtureProphecy2 = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy2->getId()->willReturn('foo');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixtureProphecy2->reveal();

        $bag = (new FixtureBag())
            ->with($fixture1)
            ->with($fixture2)
        ;

        $fixtures = [];
        foreach ($bag as $key => $value) {
            $fixtures[$key] = $value;
        }

        $this->assertSame($fixtures, array_values($this->propRefl->getValue($bag)));
    }

    private function assertSameFixtures(array $expected, FixtureBag $actual)
    {
        $this->assertSame($expected, $this->propRefl->getValue($actual));
    }

    private function assertNotSameFixtures(array $expected, FixtureBag $actual)
    {
        $this->assertNotSame($expected, $this->propRefl->getValue($actual));
    }
}
