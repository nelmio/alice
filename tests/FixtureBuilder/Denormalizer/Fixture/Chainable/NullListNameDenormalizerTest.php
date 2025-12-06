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
use ReflectionClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(NullListNameDenormalizer::class)]
final class NullListNameDenormalizerTest extends ChainableDenormalizerTestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideSimpleFixtures')]
    public function testCanBuildSimpleFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideListFixtures')]
    public function testCanBuildListFixtures($name): void
    {
        $this->assertCanBuild($name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMalformedListFixtures')]
    public function testCanBuildMalformedListFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideSegmentFixtures')]
    public function testCanBuildSegmentFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMalformedSegmentFixtures')]
    public function testCanBuildMalformedSegmentFixtures($name): void
    {
        $this->assertCannotBuild($name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideSimpleFixtures')]
    public function testBuildSimpleFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideListFixtures')]
    public function testBuildListFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMalformedListFixtures')]
    public function testBuildMalformedListFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideSegmentFixtures')]
    public function testBuildSegmentFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMalformedSegmentFixtures')]
    public function testBuildMalformedSegmentFixtures($name, $expected): void
    {
        $this->markAsInvalidCase();
    }
}
