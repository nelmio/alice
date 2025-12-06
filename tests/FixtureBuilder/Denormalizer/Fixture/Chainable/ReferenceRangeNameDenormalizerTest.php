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
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException;
use Nelmio\Alice\Throwable\Exception\FixtureNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(ReferenceRangeNameDenormalizer::class)]
final class ReferenceRangeNameDenormalizerTest extends ChainableDenormalizerTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->denormalizer = new ReferenceRangeNameDenormalizer(
            new DummySpecificationBagDenormalizer(),
            new DummyFlagParser(),
        );
    }

    public function testIsAChainableDenormalizer(): void
    {
        self::assertTrue(is_a(ReferenceRangeNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ReferenceRangeNameDenormalizer::class))->isCloneable());
    }

    public function testCannotDenormalizeFixtureIfHasNoFlagParser(): void
    {
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $this->prophesize(SpecificationsDenormalizerInterface::class)->reveal();

        $denormalizer = new ReferenceRangeNameDenormalizer($specsDenormalizer);

        $this->expectException(FlagParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer::denormalize" to be called only if it has a flag parser.');

        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user_{@account}', [], new FlagBag(''));
    }

    public function testTemplateFlagsAreProperlyParsed(): void
    {
        $valueForCurrent = new TemplatingFixture(
            new SimpleFixtureWithFlags(
                new SimpleFixture(
                    'userDetails',
                    'Nelmio\Alice\Entity\UserDetails',
                    new SpecificationBag(null, new PropertyBag(), new MethodCallBag()),
                ),
                new FlagBag('userDetails'),
            ),
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
            ->willReturn($parsedFlags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        $expectedSpecs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $specsDenormalizerProphecy
            ->denormalize(Argument::type(SimpleFixture::class), $flagParser, $specs)
            ->willReturn($expectedSpecs);
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
                        $valueForCurrent,
                    ),
                    $parsedFlags,
                ),
            ),
        );

        self::assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testWildcardIsProperlyParsed(): void
    {
        $userDetails1 = new TemplatingFixture(
            new SimpleFixtureWithFlags(
                new SimpleFixture(
                    'userDetails1',
                    'Nelmio\Alice\Entity\UserDetails',
                    new SpecificationBag(null, new PropertyBag(), new MethodCallBag()),
                ),
                new FlagBag('userDetails1'),
            ),
        );

        $userDetails2 = new TemplatingFixture(
            new SimpleFixtureWithFlags(
                new SimpleFixture(
                    'userDetails2',
                    'Nelmio\Alice\Entity\UserDetails',
                    new SpecificationBag(null, new PropertyBag(), new MethodCallBag()),
                ),
                new FlagBag('userDetails2'),
            ),
        );

        $fixtures = (new FixtureBag())->with($userDetails1)->with($userDetails2);
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{@userDetails*} (extends timestamp)';
        $fixtureName1 = 'user_userDetails1';
        $fixtureName2 = 'user_userDetails2';
        $specs = [];
        $flags = new FlagBag('');
        $parsedFlags = (new FlagBag('user_{@userDetails}'))->withFlag(new ExtendFlag(new FixtureReference('timestamp')));
        $fixtureFlags1 = (new FlagBag('user_userDetails1'))->withFlag(new ExtendFlag(new FixtureReference('timestamp')));
        $fixtureFlags2 = (new FlagBag('user_userDetails2'))->withFlag(new ExtendFlag(new FixtureReference('timestamp')));

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($reference)
            ->willReturn($parsedFlags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        $expectedSpecs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $specsDenormalizerProphecy
            ->denormalize(Argument::type(SimpleFixture::class), $flagParser, $specs)
            ->willReturn($expectedSpecs);
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $specsDenormalizerProphecy->reveal();

        $denormalizer = (new ReferenceRangeNameDenormalizer($specsDenormalizer))->withFlagParser($flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $expected = $fixtures->with(
            new TemplatingFixture(
                new SimpleFixtureWithFlags(
                    new SimpleFixture(
                        $fixtureName1,
                        $className,
                        $expectedSpecs,
                        $userDetails1,
                    ),
                    $fixtureFlags1,
                ),
            ),
        )->with(
            new TemplatingFixture(
                new SimpleFixtureWithFlags(
                    new SimpleFixture(
                        $fixtureName2,
                        $className,
                        $expectedSpecs,
                        $userDetails2,
                    ),
                    $fixtureFlags2,
                ),
            ),
        );

        self::assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testWildcardWithNoMatchesThrowsFixtureNotFoundException(): void
    {
        $fixtures = (new FixtureBag());
        $className = 'Nelmio\Alice\Entity\User';
        $reference = 'user_{@userDetails*} (extends timestamp)';
        $specs = [];
        $flags = new FlagBag('');
        $parsedFlags = (new FlagBag('user_{@userDetails}'))->withFlag(new ExtendFlag(new FixtureReference('timestamp')));

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($reference)
            ->willReturn($parsedFlags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $specsDenormalizerProphecy->reveal();

        $denormalizer = (new ReferenceRangeNameDenormalizer($specsDenormalizer))->withFlagParser($flagParser);

        $this->expectException(FixtureNotFoundException::class);
        $this->expectExceptionMessage('Could not find fixtures matching wildcard "userDetails*".');

        $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(0);
    }

    #[DataProvider('provideSimpleFixtures')]
    public function testCanBuildSimpleFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideListFixtures')]
    public function testCanBuildListFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideMalformedListFixtures')]
    public function testCanBuildMalformedListFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideSegmentFixtures')]
    public function testCanBuildSegmentFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideMalformedSegmentFixtures')]
    public function testCanBuildMalformedSegmentFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideSimpleFixtures')]
    public function testBuildSimpleFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideListFixtures')]
    public function testBuildListFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideMalformedListFixtures')]
    public function testBuildMalformedListFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideSegmentFixtures')]
    public function testBuildSegmentFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideMalformedSegmentFixtures')]
    public function testBuildMalformedSegmentFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }
}
