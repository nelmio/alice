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
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\SimpleConstructorDenormalizer
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorWithCallerDenormalizer
 */
class ConstructorWithCallerDenormalizerTest extends TestCase
{
    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argsDenormalizer = new FakeArgumentsDenormalizer()),
            $argsDenormalizer
        );
    }

    public function testDenormalizesEmptyConstructorAsSimpleConstructor()
    {
        $constructor = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $constructor)
            ->willReturn($constructor)
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall(
            '__construct',
            []
        );

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizesWithArgumentsConstructorAsSimpleConstructor()
    {
        $constructor = [
            '0 (unique)' => '<latitude()>',
            '1 (unique)' => '<longitude()>',
            '2' => '<random()>',
            1000,
        ];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $constructor)
            ->willReturn($constructor)
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall(
            '__construct',
            $constructor
        );

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeSelfStaticFactoriesConstructor()
    {
        $constructor = [
            'create' => $arguments = [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Alice\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $arguments)
            ->willReturn($arguments)
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Alice\Entity\User'),
            'create',
            $arguments
        );

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
        $actual = $denormalizer->denormalize($fixture, $flagParser, $constructor);

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

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
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

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
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

        $denormalizer = new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer($argumentsDenormalizer),
            $argumentsDenormalizer
        );
        $denormalizer->denormalize($fixture, $flagParser, $constructor);
    }
}
