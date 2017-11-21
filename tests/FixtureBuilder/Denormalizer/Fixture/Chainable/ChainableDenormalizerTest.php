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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureFactory;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ReferenceProviderTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

abstract class ChainableDenormalizerTest extends TestCase
{
    use ReferenceProviderTrait;

    /**
     * @var ChainableFixtureDenormalizerInterface
     */
    protected $denormalizer;

    public function testIsABuilderMethod()
    {
        $this->assertInstanceOf(ChainableFixtureDenormalizerInterface::class, $this->denormalizer);
    }

    abstract public function testCanBuildSimpleFixtures($name);

    abstract public function testCanBuildListFixtures($name);

    abstract public function testCanBuildMalformedListFixtures($name);

    abstract public function testCanBuildSegmentFixtures($name);

    abstract public function testCanBuildMalformedSegmentFixtures($name);

    abstract public function testBuildSimpleFixtures($name, $expected);

    abstract public function testBuildListFixtures($name, $expected);

    abstract public function testBuildMalformedListFixtures($name, $expected);

    abstract public function testBuildSegmentFixtures($name, $expected);

    abstract public function testBuildMalformedSegmentFixtures($name, $expected);

    public function assertCanBuild(string $fixtureId)
    {
        $actual = $this->denormalizer->canDenormalize($fixtureId);

        $this->assertTrue($actual);
    }

    public function assertCannotBuild(string $fixtureId)
    {
        $actual = $this->denormalizer->canDenormalize($fixtureId);

        $this->assertFalse($actual);
    }

    /**
     * @param array|null $expected
     */
    public function assertBuiltResultIsTheSame(string $fixtureId, array $expected)
    {
        $this->assertTrue($this->denormalizer->canDenormalize($fixtureId));
        $actual = $this->denormalizer->denormalize(
            new FixtureBag(),
            'Dummy',
            $fixtureId,
            [],
            new FlagBag('')
        );

        $expectedFixtures = new FixtureBag();
        foreach ($expected as $item) {
            $expectedFixtures = $expectedFixtures->with($item);
        }

        $this->assertEquals($expectedFixtures, $actual);
    }

    public function markAsInvalidCase()
    {
        $this->assertTrue(true, 'Invalid scenario');
    }

    public function createDummyDenormalizer(): FixtureDenormalizerInterface
    {
        $decoratedDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize(Argument::cetera())
            ->will(
                function ($args) {
                    return $args[0]->with(FixtureFactory::create($args[2], ''));
                }
            )
        ;

        return $decoratedDenormalizerProphecy->reveal();
    }
}
