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

use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\DummyFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer
 */
class ListNameDenormalizerTest extends ChainableDenormalizerTest
{
    public function setUp()
    {
        $this->denormalizer = new ListNameDenormalizer($this->createDummyDenormalizer(), new DummyFlagParser());
    }

    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(ListNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new ListNameDenormalizer();
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\AbstractChainableDenormalizer::denormalizeTemporaryFixture" to be called only if it has a denormalizer.
     */
    public function testCannotDenormalizeIfHasNoDenormalizer()
    {
        $denormalizer = new ListNameDenormalizer(null, new DummyFlagParser());
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user{alice, bob}', [], new FlagBag(''));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer::denormalize" to be called only if it has a flag parser.
     */
    public function testCannotDenormalizeIfHasNoFlagParser()
    {
        $denormalizer = new ListNameDenormalizer(new FakeFixtureDenormalizer());
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user{alice, bob}', [], new FlagBag(''));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage As a chainable denormalizer, "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer::buildIds" should be called only if "::canDenormalize() returns true. Got false instead.
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

        $denormalizer = new ListNameDenormalizer($decoratedDenormalizer, new DummyFlagParser());
        // Hypothesis check
        $this->assertFalse($denormalizer->canDenormalize($reference));

        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', $reference, [], new FlagBag(''));
    }

    public function testDenormalizesListToBuildFixtures()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{alice, bob}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('user_{alice, bob}')->willReturn(new FlagBag('user_{alice, bob}'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

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
                new FlagBag('user_{alice, bob}')
            )
            ->will(
                function ($args) use ($className, $specs) {
                    return (new FixtureBag())
                        ->with(new SimpleFixture($args[2], $className, SpecificationBagFactory::create()))
                    ;
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture('user_alice', $className, SpecificationBagFactory::create()),
                        new FlagBag('user_alice')
                    )
                )
            )
            ->with(
                new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture('user_bob', $className, SpecificationBagFactory::create()),
                        new FlagBag('user_bob')
                    )
                )
            )
        ;

        $denormalizer = new ListNameDenormalizer($decoratedDenormalizer, $flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);
        $this->stringContains('temporary_id', $temporaryId);

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testFixtureFlagsAreParsedToTheDecoratedDenormalizer()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{alice, bob} (dummy_flag)';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = (new FlagBag(''))->withFlag(new ElementFlag('injected_flag'));

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse('user_{alice, bob} (dummy_flag)')
            ->willReturn(
                (new FlagBag('user_{alice, bob}'))->withFlag(new DummyFlag())
            );
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $decoratedDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
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
                Argument::that(
                    function ($arg) {
                        $flagBagKey = $arg->getKey();

                        \PHPUnit_Framework_Assert::assertEquals(
                            (new FlagBag($flagBagKey))
                                ->withFlag(new DummyFlag())
                                ->withFlag(new ElementFlag('injected_flag')),
                            $arg
                        );

                        return true;
                    }
                )
            )
            ->will(
                function ($args) use ($className, $specs) {
                    return (new FixtureBag())->with(
                        new TemplatingFixture(
                            new FixtureWithFlags(
                                new SimpleFixture($args[2], $className, SpecificationBagFactory::create()),
                                (new FlagBag($args[2]))
                                    ->withFlag(new DummyFlag())
                                    ->withFlag(new ElementFlag('injected_flag'))
                            )
                        )
                    );
                }
            )
        ;
        /** @var FixtureDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture('user_alice', $className, SpecificationBagFactory::create()),
                        (new FlagBag('user_alice'))
                            ->withFlag(new DummyFlag())
                            ->withFlag(new ElementFlag('injected_flag'))
                    )
                )
            )
            ->with(
                new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture('user_bob', $className, SpecificationBagFactory::create()),
                        (new FlagBag('user_bob'))
                            ->withFlag(new DummyFlag())
                            ->withFlag(new ElementFlag('injected_flag'))
                    )
                )
            )
        ;

        $denormalizer = new ListNameDenormalizer($decoratedDenormalizer, $flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideSimpleFixtures
     */
    public function testCanBuildSimpleFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideListFixtures
     */
    public function testCanBuildListFixtures($name)
    {
        $this->assertCanBuild($name);
    }

    /**
     * @dataProvider provideMalformedListFixtures
     */
    public function testCanBuildMalformedListFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideSegmentFixtures
     */
    public function testCanBuildSegmentFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideMalformedSegmentFixtures
     */
    public function testCanBuildMalformedSegmentFixtures($name)
    {
        $this->assertCannotBuild($name);
    }

    /**
     * @dataProvider provideSimpleFixtures
     */
    public function testBuildSimpleFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideListFixtures
     */
    public function testBuildListFixtures($name, $expected)
    {
        $this->assertBuiltResultIsTheSame($name, $expected);
    }

    /**
     * @dataProvider provideMalformedListFixtures
     */
    public function testBuildMalformedListFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideSegmentFixtures
     */
    public function testBuildSegmentFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }

    /**
     * @dataProvider provideMalformedSegmentFixtures
     */
    public function testBuildMalformedSegmentFixtures($name, $expected)
    {
        $this->markAsInvalidCase();
    }
}
