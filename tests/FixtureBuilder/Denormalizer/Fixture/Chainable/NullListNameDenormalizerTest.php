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
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer
 */
class NullListNameDenormalizerTest extends ChainableDenormalizerTest
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->denormalizer = new NullListNameDenormalizer();
    }

    public function testIsACollectionDenormalizer()
    {
        $this->assertTrue(is_a(NullListNameDenormalizer::class, CollectionDenormalizer::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(NullListNameDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesListToBuildFixtures()
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
                            'alice'
                        ),
                        new FlagBag('user_alice')
                    )
                )
            )
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user_bob',
                            $className,
                            SpecificationBagFactory::create(),
                            'bob'
                        ),
                        new FlagBag('user_bob')
                    )
                )
            )
        ;
        $reference = 'user_{alice, bob}';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $denormalizer = new NullListNameDenormalizer();
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $this->assertSame($expected, $actual);
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
