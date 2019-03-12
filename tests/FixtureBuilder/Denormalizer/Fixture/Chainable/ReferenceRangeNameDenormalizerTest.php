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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\DummySpecificationBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\DummyFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer
 */
class ReferenceRangeNameDenormalizerTest extends ChainableDenormalizerTest
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->denormalizer = new ReferenceRangeNameDenormalizer(
            new DummySpecificationBagDenormalizer(),
            new DummyFlagParser()
        );
    }

    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(ReferenceRangeNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ReferenceRangeNameDenormalizer::class))->isCloneable());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer::denormalize" to be called only if it has a flag parser.
     */
    public function testCannotDenormalizeFixtureIfHasNoFlagParser()
    {
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $this->prophesize(SpecificationsDenormalizerInterface::class)->reveal();

        $denormalizer = new ReferenceRangeNameDenormalizer($specsDenormalizer);
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user_{@account}', [], new FlagBag(''));
    }

    public function testTemplateFlagsAreProperlyParsed()
    {
        $valueForCurrent = new TemplatingFixture(
            new SimpleFixtureWithFlags(
                new SimpleFixture(
                    'userDetails',
                    'Nelmio\Alice\Entity\UserDetails',
                    new SpecificationBag(null, new PropertyBag(), new MethodCallBag())
                ),
                new FlagBag('userDetails')
            )
        );

        $fixtures = (new FixtureBag())->with($valueForCurrent);
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{@userDetails} (extends timestamp)';
        $fixtureName = 'user_userDetails';
        $specs = [];
        $flags = new FlagBag('');
        $parsedFlags = (new FlagBag($fixtureName))->withFlag(new ExtendFlag(new FixtureReference('timestamp')));

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($reference)
            ->willReturn($parsedFlags)
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        $expectedSpecs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $specsDenormalizerProphecy
            ->denormalize(Argument::type(SimpleFixture::class), $flagParser, $specs)
            ->willReturn($expectedSpecs)
        ;
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $specsDenormalizerProphecy->reveal();

        $denormalizer = (new ReferenceRangeNameDenormalizer($specsDenormalizer))->withFlagParser($flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $expected = $fixtures->with(
            new TemplatingFixture(
                new SimpleFixtureWithFlags(
                    new SimpleFixture(
                        $fixtureName,
                        $className,
                        $expectedSpecs,
                        $valueForCurrent
                    ),
                    $parsedFlags
                )
            )
        );

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
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
        $this->assertCannotBuild($name);
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
        $this->markAsInvalidCase();
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
