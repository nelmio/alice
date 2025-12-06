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
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FakeCallsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer
 * @internal
 */
final class FactoryDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FactoryDenormalizer::class))->isCloneable());
    }

    public function testCannotDenormalizeEmptyFactory(): void
    {
        $factory = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeCallsDenormalizer(),
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not denormalize the given factory.');

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    public function testCannotDenormalizeFactoryWithMultipleNames(): void
    {
        $factory = [
            'foo' => [],
            'bar' => [],
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeCallsDenormalizer(),
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not denormalize the given factory.');

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    public function testCannotDenormalizeFactoryWithNoFactoryName(): void
    {
        $factory = [
            'foo' => 'bar',
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeCallsDenormalizer(),
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not denormalize the given factory.');

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    public function testCanDenormalizeASimpleFactory(): void
    {
        $factory = [
            'create' => $unparsedArguments = [
                '0 (unique)' => '<latitude()>',
                '1 (unique)' => '<longitude()>',
                '2' => '<random()>',
                1000,
            ],
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Alice\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParser = new FakeFlagParser();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize(
                $fixture,
                $flagParser,
                'Nelmio\Alice\Entity\User::create',
                $unparsedArguments,
            )
            ->willReturn(
                $expected = new FakeMethodCall(),
            );
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $denormalizer = new FactoryDenormalizer($callsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $factory);

        self::assertEquals($expected, $actual);
    }

    public function testCanDenormalizeAStaticFactory(): void
    {
        $constructor = [
            'Nelmio\Entity\UserFactory::create' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ],
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize(
                $fixture,
                $flagParser,
                'Nelmio\Entity\UserFactory::create',
                $arguments,
            )
            ->willReturn(
                $expected = new FakeMethodCall(),
            );
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $denormalizer = new FactoryDenormalizer($callsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        self::assertEquals($expected, $actual);
    }

    public function testCanDenormalizeANonStaticFactory(): void
    {
        $constructor = [
            '@nelmio.entity.user_factory::create' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ],
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize(
                $fixture,
                $flagParser,
                '@nelmio.entity.user_factory::create',
                $arguments,
            )
            ->willReturn(
                $expected = new FakeMethodCall(),
            );
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $denormalizer = new FactoryDenormalizer($callsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        self::assertEquals($expected, $actual);
    }
}
