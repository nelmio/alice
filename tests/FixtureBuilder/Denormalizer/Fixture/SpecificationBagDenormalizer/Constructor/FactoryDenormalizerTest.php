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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use PHPUnit\Framework\TestCase;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\FakeArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer
 */
class FactoryDenormalizerTest extends TestCase
{
    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new FactoryDenormalizer(
            new FakeArgumentsDenormalizer()
        );
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException
     * @expectedExceptionMessage Cannot denormalize the given factory.
     */
    public function testCannotDenormalizeEmptyFactory()
    {
        $factory = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeArgumentsDenormalizer()
        );

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException
     * @expectedExceptionMessage Cannot denormalize the given factory.
     */
    public function testCannotDenormalizeFactoryWithMultipleNames()
    {
        $factory = [
            'foo' => [],
            'bar' => [],
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeArgumentsDenormalizer()
        );

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException
     * @expectedExceptionMessage Cannot denormalize the given factory.
     */
    public function testCannotDenormalizeFactoryWithNoFactoryName()
    {
        $factory = [
            'foo' => 'bar',
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $denormalizer = new FactoryDenormalizer(
            new FakeArgumentsDenormalizer()
        );

        $denormalizer->denormalize($fixture, $flagParser, $factory);
    }

    public function testDenormalizesWithArgumentsConstructorAsSimpleConstructor()
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

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn(['argument values'])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Alice\Entity\User'),
            'create',
            ['argument values']
        );

        $denormalizer = new FactoryDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $factory);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeStaticFactoriesConstructor()
    {
        $constructor = [
            'Nelmio\Entity\UserFactory::create' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $arguments)
            ->willReturn($arguments)
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Entity\UserFactory'),
            'create',
            $arguments
        );

        $denormalizer = new FactoryDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeNonStaticFactoryConstructor()
    {
        $constructor = [
            '@nelmio.entity.user_factory::create' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $arguments)
            ->willReturn($arguments)
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new InstantiatedReference('nelmio.entity.user_factory'),
            'create',
            $arguments
        );

        $denormalizer = new FactoryDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid constructor method "@foo::bar::baz".
     */
    public function testThrowsExceptionIfInvalidConstructor()
    {
        $constructor = [
            '@foo::bar::baz' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();
        $argumentsDenormalizer = new FakeArgumentsDenormalizer();

        $denormalizer = new FactoryDenormalizer($argumentsDenormalizer);

        $denormalizer->denormalize($fixture, $flagParser, $constructor);
    }
}
