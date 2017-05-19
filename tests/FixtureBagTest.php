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

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\MutableFixture;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Throwable\Exception\FixtureNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\FixtureBag
 */
class FixtureBagTest extends TestCase
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
        $propRelf = (new \ReflectionClass(FixtureBag::class))->getProperty('fixtures');
        $propRelf->setAccessible(true);

        $this->propRefl = $propRelf;
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $fixture = new DummyFixture('foo');
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

    public function testIsImmutable()
    {
        $fixture = new MutableFixture('foo', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());
        $bag = (new FixtureBag())->with($fixture);

        // Mutate injected fixture
        $fixture->setSpecs(SpecificationBagFactory::create(new FakeMethodCall()));

        // Mutate retrieved fixture
        $bag->get('foo')->setSpecs(SpecificationBagFactory::create(new NoMethodCall()));

        $this->assertEquals(
            (new FixtureBag())
                ->with(new MutableFixture('foo', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create())),
            $bag
        );
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $fixture = new DummyFixture('foo');

        $bag = new FixtureBag();
        $newBag = $bag->with($fixture);
        $newBagEmptied = $newBag->without($fixture);

        $this->assertInstanceOf(FixtureBag::class, $newBag);
        $this->assertNotSame($newBag, $bag);

        $this->assertEquals(new FixtureBag(), $bag);
        $this->assertSameFixtures(
            [
                'foo' => $fixture,
            ],
            $newBag
        );
        $this->assertEquals(new FixtureBag(), $newBagEmptied);
    }

    public function testIfTwoFixturesWithTheSameIdIsAddedThenTheFirstOneWillBeOverridden()
    {
        $fixture1 = new DummyFixture('foo');
        $fixture2 = new MutableFixture('foo', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());

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

    public function testMergeBagsWillReturnANewInstanceWithTheMergedFixtures()
    {
        $fixture1 = new DummyFixture('foo');
        $fixture2 = new MutableFixture('foo', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());
        $fixture3 = new DummyFixture('bar');

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
        $fixture1 = new DummyFixture('foo');
        $fixture2 = new DummyFixture('bar');

        $bag = (new FixtureBag())
            ->with($fixture1)
            ->with($fixture2)
        ;

        $fixtures = [];
        foreach ($bag as $key => $value) {
            $fixtures[$key] = $value;
        }

        $this->assertSame($fixtures, $this->propRefl->getValue($bag));
    }

    public function testToArray()
    {
        $fixture1 = new DummyFixture('foo');
        $fixture2 = new DummyFixture('bar');

        $bag = (new FixtureBag())
            ->with($fixture1)
            ->with($fixture2)
        ;

        $this->assertEquals(
            [
                'foo' => $fixture1,
                'bar' => $fixture2,
            ],
            $bag->toArray()
        );
    }

    private function assertSameFixtures(array $expected, FixtureBag $actual)
    {
        $this->assertEquals($expected, $this->propRefl->getValue($actual));
    }

    private function assertNotSameFixtures(array $expected, FixtureBag $actual)
    {
        $this->assertNotEquals($expected, $this->propRefl->getValue($actual));
    }
}
