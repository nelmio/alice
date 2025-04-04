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
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer
 * @internal
 */
class NullListNameDenormalizerTest extends ChainableDenormalizerTestCase
{
    protected function setUp(): void
    {
        $this->denormalizer = new NullListNameDenormalizer();
    }

    public function testIsACollectionDenormalizer(): void
    {
        self::assertTrue(is_a(NullListNameDenormalizer::class, CollectionDenormalizer::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(NullListNameDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesListToBuildFixtures(): void
    {
        $className = 'Nelmio\Alice\Entity\User';
        $fixtures = $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user_alice',
                            $className,
                            SpecificationBagFactory::create(),
                            'alice',
                        ),
                        new FlagBag('user_alice'),
                    ),
                ),
            )
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user_bob',
                            $className,
                            SpecificationBagFactory::create(),
                            'bob',
                        ),
                        new FlagBag('user_bob'),
                    ),
                ),
            );
        $reference = 'user_{alice, bob}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $denormalizer = new NullListNameDenormalizer();
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        self::assertSame($expected, $actual);
    }

    #[DataProvider('provideSimpleFixtures')]
    public function testCanBuildSimpleFixtures(mixed $name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideListFixtures')]
    public function testCanBuildListFixtures(mixed $name): void
    {
        $this->assertCanBuild($name);
    }

    #[DataProvider('provideMalformedListFixtures')]
    public function testCanBuildMalformedListFixtures(mixed $name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideSegmentFixtures')]
    public function testCanBuildSegmentFixtures(mixed $name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideMalformedSegmentFixtures')]
    public function testCanBuildMalformedSegmentFixtures(mixed $name): void
    {
        $this->assertCannotBuild($name);
    }

    #[DataProvider('provideSimpleFixtures')]
    public function testBuildSimpleFixtures(mixed $name, mixed $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideListFixtures')]
    public function testBuildListFixtures(mixed $name, mixed $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideMalformedListFixtures')]
    public function testBuildMalformedListFixtures(mixed $name, mixed $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideSegmentFixtures')]
    public function testBuildSegmentFixtures(mixed $name, mixed $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[DataProvider('provideMalformedSegmentFixtures')]
    public function testBuildMalformedSegmentFixtures(mixed $name, mixed $expected): void
    {
        $this->markAsInvalidCase();
    }
}
