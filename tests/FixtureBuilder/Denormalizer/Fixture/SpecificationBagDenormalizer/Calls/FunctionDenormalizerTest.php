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

use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use PHPUnit\Framework\TestCase;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\FakeArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer
 */
class FunctionDenormalizerTest extends TestCase
{
    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new FunctionDenormalizer(new FakeArgumentsDenormalizer());
    }

    public function testDenormalizesASimpleMethodCall()
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
            ->willReturn($parsedArguments = [new \stdClass()])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall('setLocation', $parsedArguments);

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        $this->assertEquals($expected, $actual);



        $argumentsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeStaticFactoriesConstructor()
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
            ->willReturn($parsedArguments = [new \stdClass()])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Entity\UserFactory'),
            'create',
            $parsedArguments
        );

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeNonStaticFactoryConstructor()
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
            ->willReturn($parsedArguments = [new \stdClass()])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new MethodCallWithReference(
            new InstantiatedReference('nelmio.entity.user_factory'),
            'create',
            $parsedArguments
        );

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid constructor method "Invalid::method::reference".
     */
    public function testThrowsExceptionIfInvalidConstructor()
    {
        $method = 'Invalid::method::reference';
        $unparsedArguments = [];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();
        $argumentsDenormalizer = new FakeArgumentsDenormalizer();

        $denormalizer = new FunctionDenormalizer($argumentsDenormalizer);

        $denormalizer->denormalize($fixture, $flagParser, $method, $unparsedArguments);
    }
}

