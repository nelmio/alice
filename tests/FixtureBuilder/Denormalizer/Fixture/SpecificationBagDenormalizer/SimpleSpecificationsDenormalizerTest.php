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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use InvalidArgumentException;
use LogicException;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FakeCallsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FakeConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\FakePropertyDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use TypeError;

/**
 * @internal
 */
#[CoversClass(SimpleSpecificationsDenormalizer::class)]
final class SimpleSpecificationsDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        clone new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
    }

    public function testCanDenormalizeEmptySpecs(): void
    {
        $specs = [];
        $flagParser = new FakeFlagParser();

        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);
    }

    public function testCanDenormalizeConstructor(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $construct = [
                'foo',
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $construct)
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    '__construct',
                    ['foo'],
                ),
            );
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            new PropertyBag(),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeFactory(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__factory' => $factory = [
                'create' => ['foo'],
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $factory)
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    'create',
                    ['foo'],
                ),
            );
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            new PropertyBag(),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedDeprecation Using factories with the fixture keyword "__construct" has been deprecated since 3.0.0 and will no longer be supported in Alice 4.0.0. Use "__factory" instead.
     */
    #[Group('legacy')]
    public function testUsingAFactoryWithConstructIsDeprecated(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $factory = [
                'create' => ['foo', 'bar'],
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $factory)
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    'create',
                    ['foo', 'bar'],
                ),
            );
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            new PropertyBag(),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCannotProceedWithInvalidProperty(): void
    {
        $unparsedSpecs = [
            'foo',
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(
            new FakeConstructorDenormalizer(),
            new FakePropertyDenormalizer(),
            new FakeCallsDenormalizer(),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid property name: 0.');

        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $unparsedSpecs);
    }

    public function testCannotDenormalizeAnInvalidFactory(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $construct = [
                'foo',
            ],
            '__factory' => $factory = [
                'create' => [
                    'foo',
                ],
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $construct)
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    '__construct',
                    ['foo'],
                ),
            );
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $factory)
            ->shouldNotBeCalled();
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot use the fixture property "__construct" and "__factory" together.');

        $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);
    }

    public function testCannotDenormalizeAFactoryAndAConstructor(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__factory' => $construct = [
                '<latitude()>',
            ],
        ];
        $flagParser = new FakeFlagParser();

        $constructorDenormalizerProphecy = $this->prophesize(ConstructorDenormalizerInterface::class);
        $constructorDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $construct)
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    '__construct',
                    [],
                ),
            );
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, new FakePropertyDenormalizer(), new FakeCallsDenormalizer());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not denormalize the given factory.');

        $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);
    }

    public function testCanDenormalizeTheNoConstructor(): void
    {
        $specs = [
            '__construct' => false,
        ];

        $expected = new SpecificationBag(
            new NoMethodCall(),
            new PropertyBag(),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);

        self::assertEquals($expected, $actual);
    }

    public function testCanDenormalizeProperties(): void
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
            ->willReturn($usernameProp = new Property('username', '<name()>'));
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_name', 'bob', $nameFlags)
            ->willReturn($nameProp = new Property('name', 'bob'));
        /** @var PropertyDenormalizerInterface $propertyDenormalizer */
        $propertyDenormalizer = $propertyDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            null,
            (new PropertyBag())
                ->with($usernameProp)
                ->with($nameProp),
            new MethodCallBag(),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), $propertyDenormalizer, new FakeCallsDenormalizer());
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
        $propertyDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testCanDenormalizeCalls(): void
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
            ->willReturn($call = new NoMethodCall());
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            (new MethodCallBag())->with($call),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), $callsDenormalizer);
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $callsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanDenormalizeCompleteSpecs(): void
    {
        $fixture = new FakeFixture();
        $specs = [
            '__construct' => $construct = [
                '<latitude()>',
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
            ->willReturn(
                $constructor = new SimpleMethodCall(
                    '__construct',
                    ['<latitude()>'],
                ),
            );
        /** @var ConstructorDenormalizerInterface $constructorDenormalizer */
        $constructorDenormalizer = $constructorDenormalizerProphecy->reveal();

        $propertyDenormalizerProphecy = $this->prophesize(PropertyDenormalizerInterface::class);
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_username', '<name()>', $usernameFlags)
            ->willReturn($usernameProp = new Property('username', '<name()>'));
        $propertyDenormalizerProphecy
            ->denormalize($fixture, 'parsed_name', 'bob', $nameFlags)
            ->willReturn($nameProp = new Property('name', 'bob'));
        /** @var PropertyDenormalizerInterface $propertyDenormalizer */
        $propertyDenormalizer = $propertyDenormalizerProphecy->reveal();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, 'setLocation', $setLocationArgs)
            ->willReturn($call = new NoMethodCall());
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $expected = new SpecificationBag(
            $constructor,
            (new PropertyBag())
                ->with($usernameProp)
                ->with($nameProp),
            (new MethodCallBag())->with($call),
        );

        $denormalizer = new SimpleSpecificationsDenormalizer($constructorDenormalizer, $propertyDenormalizer, $callsDenormalizer);
        $actual = $denormalizer->denormalize(new FakeFixture(), $flagParser, $specs);

        self::assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
        $constructorDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $propertyDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $callsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizeInvalidCalls(): void
    {
        $specs = [
            '__calls' => [
                'invalid value',
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Expected method call value to be an array. Got "string" instead.');

        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }

    public function testDenormalizeCallsWithInvalidMethod(): void
    {
        $specs = [
            '__calls' => [
                [],
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Expected method name. Got "NULL" instead.');

        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }

    public function testDenormalizeWithInvalidMethodCalls(): void
    {
        $specs = [
            '__calls' => [
                null,
            ],
        ];

        $denormalizer = new SimpleSpecificationsDenormalizer(new FakeConstructorDenormalizer(), new FakePropertyDenormalizer(), new FakeCallsDenormalizer());

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Expected method call value to be an array. Got "NULL" instead.');

        $denormalizer->denormalize(new FakeFixture(), new FakeFlagParser(), $specs);
    }
}
