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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use InvalidArgumentException;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\FakeArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer
 * @internal
 */
class FunctionDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FunctionDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesASimpleMethodCall(): void
    {
        $fixture = new FakeFixture();

        $method = 'setLocation';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn($parsedArguments = [new stdClass()]);
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall('setLocation', $parsedArguments);

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        self::assertEquals($expected, $actual);

        $argumentsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeStaticFactoriesConstructor(): void
    {
        $method = 'Nelmio\Entity\UserFactory::create';
        $unparsedArguments = [
            '<latitude()>',
            '1 (unique)' => '<longitude()>',
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn($parsedArguments = [new stdClass()]);
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Entity\UserFactory'),
            'create',
            $parsedArguments,
        );

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        self::assertEquals($expected, $actual);
    }

    public function testCanDenormalizeNonStaticFactoryConstructor(): void
    {
        $method = '@nelmio.entity.user_factory::create';
        $unparsedArguments = [
            '<latitude()>',
            '1 (unique)' => '<longitude()>',
        ];

        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn($parsedArguments = [new stdClass()]);
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new InstantiatedReference('nelmio.entity.user_factory'),
            'create',
            $parsedArguments,
        );

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        self::assertEquals($expected, $actual);
    }

    public function testThrowsExceptionIfInvalidConstructor(): void
    {
        $method = 'Invalid::method::reference';
        $unparsedArguments = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();
        $argumentsDenormalizer = new FakeArgumentsDenormalizer();

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid constructor method "Invalid::method::reference".');

        $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);
    }
}
