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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer
 */
class AbstractChainableDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(AbstractChainableDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsDenormalizerAware()
    {
        $this->assertTrue(is_a(AbstractChainableDenormalizer::class, FixtureDenormalizerAwareInterface::class, true));
    }

    public function testCanBeInstantiatedWithADenormalizer()
    {
        new FakeAbstractChainableDenormalizer(new FakeFixtureDenormalizer());
    }

    public function testCanBeInstantiatedWithoutADenormalizer()
    {
        new FakeAbstractChainableDenormalizer();
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FakeAbstractChainableDenormalizer();
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $denormalizer = new FakeAbstractChainableDenormalizer();
        $newDenormalizer = $denormalizer->withFixtureDenormalizer(new FakeFixtureDenormalizer());

        $this->assertEquals(new FakeAbstractChainableDenormalizer(), $denormalizer);
        $this->assertEquals(new FakeAbstractChainableDenormalizer(new FakeFixtureDenormalizer()), $newDenormalizer);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer::denormalizeTemporaryFixture" to be called only if it has a denormalizer.
     */
    public function testCannotDenormalizerIfHasNoDenormalizer()
    {
        $denormalizer = new FakeAbstractChainableDenormalizer();
        $denormalizer->denormalizeTemporaryFixture(new FixtureBag(), 'Dummy', [], new FlagBag(''));
    }

    public function testDenormalizeTemporaryFixturesReturnsTemporaryFixtureAndSet()
    {
        $fixtures = (new FixtureBag())->with(new DummyFixture('foo'));
        $className = 'Nelmio\Alice\Entity\User';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('f');

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
                function ($args) use ($fixtures, $className, $specs) {
                    return $fixtures
                        ->with(
                            new SimpleFixture($args[2], $className, SpecificationBagFactory::create())
                        )
                        ->with(
                            new SimpleFixture('bar', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()))
                        )
                    ;
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $denormalizer = new FakeAbstractChainableDenormalizer($denormalizer);
        $actual = $denormalizer->denormalizeTemporaryFixture($fixtures, $className, $specs, $flags);

        $expected = [
            new SimpleFixture($actual[0]->getId(), $className, SpecificationBagFactory::create()),
            $fixtures->with(
                new SimpleFixture('bar', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()))
            ),
        ];

        $this->assertEquals($expected, $actual);
        $this->stringContains('temporary_id', $temporaryId);

        $denormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
