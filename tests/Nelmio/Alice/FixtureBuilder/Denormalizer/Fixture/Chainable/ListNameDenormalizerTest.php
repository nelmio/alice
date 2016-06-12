<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer
 */
class ListNameDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(ListNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsDenormalizerAware()
    {
        $this->assertTrue(is_a(ListNameDenormalizer::class, FixtureDenormalizerAwareInterface::class, true));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Expected method
     *                           "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer::denormalize"
     *                           to be called only if it has a denormalizer.
     */
    public function testCannotDenormalizerIfHasNoDenormalizer()
    {
        $denormalizer = new ListNameDenormalizer();
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Entity\User', 'user{alice, bob}', [], new FlagBag(''));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage As a chainable denormalizer,
     *                           "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer::getReferences"
     *                           should be called only if "::canDenormalize() returns true. Got false instead.
     */
    public function testCannotDenormalizeIfDoesNotSupportIt()
    {
        $reference = 'user0';
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $this->prophesize(FixtureDenormalizerInterface::class)->reveal();

        $denormalizer = (new ListNameDenormalizer())->with($decoratedDenormalizer);
        // Hypothesis check
        $this->assertFalse($denormalizer->canDenormalize($reference));

        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Entity\User', $reference, [], new FlagBag(''));
    }

    public function testDenormalize()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user_{alice, bob}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $fixture1Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture1Prophecy->getId()->willReturn('f1');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixture1Prophecy->reveal();

        $fixture2Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture2Prophecy->getId()->willReturn('f2');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixture2Prophecy->reveal();

        $denormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $denormalizerProphecy
            ->denormalize($fixtures, $className, 'user_alice', $specs, $flags)
            ->willReturn((new FixtureBag())->with($fixture1))
        ;
        $newFixtures = $fixtures->with($fixture1);
        $denormalizerProphecy
            ->denormalize($newFixtures, $className, 'user_bob', $specs, $flags)
            ->willReturn((new FixtureBag())->with($fixture2))
        ;
        $expected = $newFixtures->with($fixture2);
        /** @var FixtureDenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $denormalizer = (new ListNameDenormalizer())->with($denormalizer);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);

        $fixture1Prophecy->getId()->shouldHaveBeenCalledTimes(3);
        $fixture2Prophecy->getId()->shouldHaveBeenCalledTimes(3);
        $denormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testIsDeepClonable()
    {
        $denormalizer = (new ListNameDenormalizer())->with(new FakeFixtureDenormalizer());
        $clone = clone $denormalizer;

        $this->assertEquals($denormalizer, $clone);
        $this->assertNotSame($denormalizer, $clone);
    }

    public function testConserveReferenceFlags()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user_{alice, bob} (template)';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $fixture1Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture1Prophecy->getId()->willReturn('f1');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixture1Prophecy->reveal();

        $fixture2Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture2Prophecy->getId()->willReturn('f2');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixture2Prophecy->reveal();

        $denormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $denormalizerProphecy
            ->denormalize($fixtures, $className, 'user_alice (template)', $specs, $flags)
            ->willReturn((new FixtureBag())->with($fixture1))
        ;
        $newFixtures = $fixtures->with($fixture1);
        $denormalizerProphecy
            ->denormalize($newFixtures, $className, 'user_bob (template)', $specs, $flags)
            ->willReturn((new FixtureBag())->with($fixture2))
        ;
        $expected = $newFixtures->with($fixture2);
        /** @var FixtureDenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $denormalizer = (new ListNameDenormalizer())->with($denormalizer);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);

        $fixture1Prophecy->getId()->shouldHaveBeenCalledTimes(3);
        $fixture2Prophecy->getId()->shouldHaveBeenCalledTimes(3);
        $denormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
