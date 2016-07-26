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
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer
 */
class SimpleSpecificationsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testDenormalize()
    {
        $specs = [
            '__construct' => [
                '<latitude()>'
            ],
            'username' => '<name()>',
            '__calls' => [
                [
                    'setLocation' => [
                        '<latitude()>',
                        '<longitude()>',
                    ],
                ],
            ],
        ];
        $expected = new SpecificationBag(
            new SimpleMethodCall(
                '__construct',
                ['<latitude()>']
            ),
            (new PropertyBag())->with(new Property('username', '<name()>')),
            (new MethodCallBag())
                ->with(
                    new SimpleMethodCall(
                        'setLocation',
                        [
                            '<latitude()>',
                            '<longitude()>',
                        ]
                    )
                )
        );

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('username')->willReturn(new FlagBag('username'));
        $flagParserProphecy->parse('setLocation')->willReturn(new FlagBag('setLocation'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer(new FakeFixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeEmptySpecs()
    {
        $specs = [];
        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            new MethodCallBag()
        );

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeEmptyNoConstructor()
    {
        $specs = [
            '__construct' => false,
        ];
        $expected = new SpecificationBag(
            new NoMethodCall(),
            new PropertyBag(),
            new MethodCallBag()
        );

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeConstructorDefaultArguments()
    {
        $specs = [
            '__construct' => [],
        ];
        $expected = new SpecificationBag(
            new SimpleMethodCall('__construct', []),
            new PropertyBag(),
            new MethodCallBag()
        );

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeNamedConstructor()
    {
        $specs = [
            '__construct' => [
                'namedConstruct' => [],
            ],
        ];
        $expected = new SpecificationBag(
            new MethodCallWithReference(
                new StaticReference('Dummy'),
                'namedConstruct',
                []
            ),
            new PropertyBag(),
            new MethodCallBag()
        );

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer($fixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeEmptyCalls()
    {
        $specs = [
            '__calls' => [],
        ];
        $expected = new SpecificationBag(
            null,
            new PropertyBag(),
            new MethodCallBag()
        );

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $actual = $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);

        $this->assertEquals($expected, $actual);
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

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);
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

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $denormalizer->denormalizer(new FakeFixture(), $flagParser, $specs);
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

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer(new DummyParser());
        $denormalizer->denormalizer($fixture, $flagParser, $specs);
    }
}
