<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\ExpressionLanguage\Parser\DummyParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FakeCallsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FakeConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\FakePropertyDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer
 */
class SimpleSpecificationsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsNotClonable()
    {
        clone new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
    }

    public function testCanDenormalizeEmptySpecs()
    {
        $specs = [];
        $flagParser = new FakeFlagParser();

        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            new MethodCallBag()
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeConstructor()
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $construct = [
                '<latitude()>'
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $construct)
            ->willReturn($constructor = new FakeMethodCall())
        ;
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            new PropertyBag(),
            new MethodCallBag()
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);

        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeTheNoConstructor()
    {
        $specs = [
            '__construct' => false,
        ];

        $expected = new SpecificationBag(
            new NoMethodCall(),
            new PropertyBag(),
            new MethodCallBag()
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture, new FakeFlagParser(), $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testCanDenormalizeProperties()
    {
        $fixture = new FakeFixture();
        $specs = [
            'username' => '<name()>',
            'name' => 'bob',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('username')->willReturn($usernameFlags = new FlagBag('parsed_username'));
        $flagParserProphecy->parse('name')->willReturn($nameFlags = new FlagBag('parsed_name'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $propertyDenormalizerProphecy = $this->prophesize(PropertyDenormalizerInterface::class);
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_username', '<name()>', $usernameFlags)
            ->willReturn($usernameProp = new Property('username', '<name()>'))
        ;
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_name', 'bob', $nameFlags)
            ->willReturn($nameProp = new Property('name', 'bob'))
        ;
        /** @var PropertyDenormalizerInterface $propertyDenormalizer */
        $propertyDenormalizer = $propertyDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            null,
            (new PropertyBag())
                ->with($usernameProp)
                ->with($nameProp)
            ,
            new MethodCallBag()
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), $propertyDenormalizer, new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
        $propertyDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testCanDenormalizeCalls()
    {
        $fixture = new FakeFixture();
        $specs = [
            '__calls' => [
                [
                    'setLocation' => $setLocationArgs = [
                        '<latitude()>',
                        '<longitude()>',
                    ],
                ],
            ],
        ];
        $flagParser = new FakeFlagParser();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, 'setLocation', $setLocationArgs)
            ->willReturn($call = new NoMethodCall())
        ;
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            (new MethodCallBag())->with($call)
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), $callsDenormalizer);
        $actual = $denormalizer->denormalize(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);

        $callsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeCompleteSpecs()
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $construct = [
                '<latitude()>'
            ],
            'username' => '<name()>',
            'name' => 'bob',
            '__calls' => [
                [
                    'setLocation' => $setLocationArgs = [
                        '<latitude()>',
                        '<longitude()>',
                    ],
                ],
            ],
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('username')->willReturn($usernameFlags = new FlagBag('parsed_username'));
        $flagParserProphecy->parse('name')->willReturn($nameFlags = new FlagBag('parsed_name'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $construct)
            ->willReturn($constructor = new FakeMethodCall())
        ;
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $propertyDenormalizerProphecy = $this->prophesize(PropertyDenormalizerInterface::class);
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_username', '<name()>', $usernameFlags)
            ->willReturn($usernameProp = new Property('username', '<name()>'))
        ;
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_name', 'bob', $nameFlags)
            ->willReturn($nameProp = new Property('name', 'bob'))
        ;
        /** @var PropertyDenormalizerInterface $propertyDenormalizer */
        $propertyDenormalizer = $propertyDenormalizerProphecy->reveal();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, 'setLocation', $setLocationArgs)
            ->willReturn($call = new NoMethodCall())
        ;
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            (new PropertyBag())
                ->with($usernameProp)
                ->with($nameProp)
            ,
            (new MethodCallBag())->with($call)
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, $propertyDenormalizer, $callsDenormalizer);
        $actual = $denormalizer->denormalize(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $propertyDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $callsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected method call value to be an array, got "string" instead.
     */
    public function testDenormalizeInvalidCalls()
    {
        $specs = [
            '__calls' => [
                'invalid value'
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected method name, got "NULL" instead.
     */
    public function testDenormalizeCallsWithInvalidMethod()
    {
        $specs = [
            '__calls' => [
                [],
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected method call value to be an array, got "NULL" instead.
     */
    public function testDenormalizeWithInvalidMethodCalls()
    {
        $specs = [
            '__calls' => [
                null,
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }
}
