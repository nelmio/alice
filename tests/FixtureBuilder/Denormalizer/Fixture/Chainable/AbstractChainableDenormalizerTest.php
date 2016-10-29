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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ElementWithToStringFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer
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

    public function testIsFlagParserAware()
    {
        $this->assertTrue(is_a(AbstractChainableDenormalizer::class, FlagParserAwareInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new DummyAbstractChainableDenormalizer();
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $denormalizer = new DummyAbstractChainableDenormalizer();
        $newDenormalizer = $denormalizer->withFixtureDenormalizer(new FakeFixtureDenormalizer());

        $this->assertEquals(new DummyAbstractChainableDenormalizer(), $denormalizer);
        $this->assertEquals(new DummyAbstractChainableDenormalizer(new FakeFixtureDenormalizer()), $newDenormalizer);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer::denormalize" to be called only if it has a denormalizer.
     */
    public function testCannotDenormalizeIfHasNoDenormalizer()
    {
        $denormalizer = new DummyAbstractChainableDenormalizer();
        $denormalizer->denormalize(new FixtureBag(), 'Dummy', 'dummy', [], new FlagBag(''));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer::denormalize" to be called only if it has a flag parser.
     */
    public function testCannotDenormalizeIfHasNoFlagParser()
    {
        $denormalizer = new DummyAbstractChainableDenormalizer(new FakeFixtureDenormalizer());
        $denormalizer->denormalize(new FixtureBag(), 'Dummy', 'dummy', [], new FlagBag(''));
    }

    public function testDenormalizeTemporaryFixturesReturnsTemporaryFixtureAndSet()
    {
        $fixtures = (new FixtureBag())->with(new DummyFixture('foo'));
        $className = 'Nelmio\Alice\Entity\User';
        $id = 'unparsed_user_id';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('unparsed_user_id')->willReturn(new FlagBag('user'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $mergedFlags = new FlagBag('user');

        $decoratedDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $temporaryId = null;
        $decoratedDenormalizerProphecy
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
                $mergedFlags
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
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user',
                            $className,
                            SpecificationBagFactory::create(),
                            'resu'
                        ),
                        $mergedFlags
                    )
                )
            )
            ->with(new DummyFixture('foo'))
            ->with(
                new SimpleFixture('bar', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()))
            )
        ;

        $denormalizer = new DummyAbstractChainableDenormalizer($decoratedDenormalizer, $flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $id, $specs, $flags);

        $this->assertEquals($expected, $actual);
        $this->stringContains('temporary_id', $temporaryId);

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testTheFixtureFlagsWillPrevailOnTheInjectedOnes()
    {
        $fixtures = (new FixtureBag())->with(new DummyFixture('foo'));
        $className = 'Nelmio\Alice\Entity\User';
        $id = 'unparsed_user_id';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = (new FlagBag('injected'))
            ->withFlag(new DummyFlag())
            ->withFlag(new ElementWithToStringFlag('foo', 'injected_flag'))
        ;

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse('unparsed_user_id')
            ->willReturn(
                (new FlagBag('user'))
                    ->withFlag(new ElementFlag('foz'))
                    ->withFlag(new ElementWithToStringFlag('bar', 'injected_flag'))
            )
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $mergedFlags = (new FlagBag('user'))
            ->withFlag(new ElementFlag('foz'))
            ->withFlag(new ElementWithToStringFlag('bar', 'injected_flag'))
            ->withFlag(new DummyFlag())
        ;

        $decoratedDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize(
                $fixtures,
                $className,
                Argument::any(),
                $specs,
                $mergedFlags
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
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new DummyAbstractChainableDenormalizer($decoratedDenormalizer, $flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $id, $specs, $flags);

        $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user',
                            $className,
                            SpecificationBagFactory::create(),
                            'resu'
                        ),
                        $mergedFlags
                    )
                )
            )
            ->with(new DummyFixture('foo'))
            ->with(
                new SimpleFixture('bar', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()))
            )
        ;

        $this->assertEquals($expected, $actual);
    }
}
