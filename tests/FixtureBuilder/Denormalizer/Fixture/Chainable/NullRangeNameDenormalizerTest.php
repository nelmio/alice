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
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(NullRangeNameDenormalizer::class)]
final class NullRangeNameDenormalizerTest extends ChainableDenormalizerTestCase
{
    protected function setUp(): void
    {
        $this->denormalizer = new NullRangeNameDenormalizer();
    }

    public function testIsAChainableDenormalizer(): void
    {
        self::assertTrue(is_a(NullRangeNameDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(NullRangeNameDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesListToBuildFixtures(): void
    {
        $className = 'Nelmio\Alice\Entity\User';
        $fixtures = $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            $className,
                            SpecificationBagFactory::create(),
                            'alice',
                        ),
                        new FlagBag('user1'),
                    ),
                ),
            )
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user2',
                            $className,
                            SpecificationBagFactory::create(),
                            'bob',
                        ),
                        new FlagBag('user2'),
                    ),
                ),
            );
        $reference = 'user{1..2}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $denormalizer = new NullListNameDenormalizer();
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        self::assertSame($expected, $actual);
    }

    public function testDenormalizesListWithStepToBuildFixtures(): void
    {
        $className = 'Nelmio\Alice\Entity\User';
        $fixtures = $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            $className,
                            SpecificationBagFactory::create(),
                            'alice',
                        ),
                        new FlagBag('user1'),
                    ),
                ),
            );
        $reference = 'user{1..2, 2}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $denormalizer = new NullListNameDenormalizer();
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        self::assertSame($expected, $actual);
    }

    #[DataProvider('provideSimpleFixtures', false)]
    public function testCanBuildSimpleFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideListFixtures', false)]
    public function testCanBuildListFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideMalformedListFixtures', false)]
    public function testCanBuildMalformedListFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideSegmentFixtures', false)]
    public function testCanBuildSegmentFixtures($name): void
    {
        $this->assertCanBuild($name);
    }

    #[DataProvider('provideMalformedSegmentFixtures', false)]
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
