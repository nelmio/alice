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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer
 */
class LLegacyConstructorDenormalizerTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(LegacyConstructorDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesConstructorWithTheDecoratedFactoryDenormalizer()
    {
        $constructor = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $constructorDenormalizer = new FakeConstructorDenormalizer();

        $factoryDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $factoryDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $constructor)
            ->willReturn(
                $expected = new FakeMethodCall()
            )
        ;
        /** @var ConstructorDenormalizerInterface $factoryDenormalizer */
        $factoryDenormalizer = $factoryDenormalizerProphecy->reveal();

        $denormalizer = new LegacyConstructorDenormalizer($constructorDenormalizer, $factoryDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertSame($expected, $actual);
    }

    public function testDenormalizesConstructorWithTheDecoratedConstructorDenormalizerIfCannotDenormalizeWithTheFactoryDenormalizer()
    {
        $constructor = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $constructor)
            ->willReturn($expected = new FakeMethodCall())
        ;
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $factoryDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $factoryDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $constructor)
            ->willThrow(UnexpectedValueException::class)
        ;
        /** @var ConstructorDenormalizerInterface $factoryDenormalizer */
        $factoryDenormalizer = $factoryDenormalizerProphecy->reveal();

        $denormalizer = new LegacyConstructorDenormalizer($constructorDenormalizer, $factoryDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertSame($expected, $actual);
    }
}
