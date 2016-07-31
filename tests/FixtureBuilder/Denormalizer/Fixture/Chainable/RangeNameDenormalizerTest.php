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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\RangeNameDenormalizer
 */
class RangeNameDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(RangeNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsDenormalizerAware()
    {
        $this->assertTrue(is_a(RangeNameDenormalizer::class, FixtureDenormalizerAwareInterface::class, true));
    }

    public function testCanBeInstantiatedWithADenormalizer()
    {
        new RangeNameDenormalizer(new FakeFixtureDenormalizer());
    }

    public function testCanBeInstantiatedWithoutADenormalizer()
    {
        new RangeNameDenormalizer();
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new RangeNameDenormalizer();
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer::denormalizeTemporaryFixture" to be called only if it has a denormalizer.
     */
    public function testCannotDenormalizerIfHasNoDenormalizer()
    {
        $denormalizer = new RangeNameDenormalizer();
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user{1..10}', [], new FlagBag(''));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage As a chainable denormalizer, "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\RangeNameDenormalizer::buildRange" should be called only if "::canDenormalize() returns true. Got false instead.
     */
    public function testCannotDenormalizeFixtureIfDoesNotSupportIt()
    {
        $reference = 'user0';

        $decoratedDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize(Argument::cetera())
            ->will(
                function ($args) {
                    return (new FixtureBag())
                        ->with(new SimpleFixture($args[2], 'Dummy', SpecificationBagFactory::create()))
                        ;
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new RangeNameDenormalizer($decoratedDenormalizer);

        // Hypothesis check
        $this->assertFalse($denormalizer->canDenormalize($reference));

        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', $reference, [], new FlagBag(''));
    }

    public function testDenormalizeRangeToBuildFixtures()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{1..2}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');
        $expected = (new FixtureBag())
            ->with(new SimpleFixture('user_1', $className, SpecificationBagFactory::create()))
            ->with(new SimpleFixture('user_2', $className, SpecificationBagFactory::create()))
        ;

        $denormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $temporaryId = null;
        $denormalizerProphecy
            ->denormalize(
                $fixtures,
                $className,
                Argument::that(
                    function ($args) use (&$temporaryId) {
                        $temporaryId = $args[0];

                        return true;
                    }
                ),
                $specs,
                $flags
            )
            ->will(
                function ($args) use ($className, $specs) {
                    return (new FixtureBag())
                        ->with(new SimpleFixture($args[2], $className, SpecificationBagFactory::create()))
                        ;
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $denormalizer = new RangeNameDenormalizer($denormalizer);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);
        $this->stringContains('temporary_id', $temporaryId);

        $denormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizationKeepsFlagsInIds()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{1..2} (dummy_flag)';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');
        $expected = (new FixtureBag())
            ->with(new SimpleFixture('user_1 (dummy_flag)', $className, SpecificationBagFactory::create()))
            ->with(new SimpleFixture('user_2 (dummy_flag)', $className, SpecificationBagFactory::create()))
        ;

        $denormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $denormalizerProphecy
            ->denormalize(Argument::cetera())
            ->will(
                function ($args) use ($className, $specs) {
                    return (new FixtureBag())
                        ->with(new SimpleFixture($args[2], $className, SpecificationBagFactory::create()))
                        ;
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $denormalizer = new RangeNameDenormalizer($denormalizer);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);
    }
}
