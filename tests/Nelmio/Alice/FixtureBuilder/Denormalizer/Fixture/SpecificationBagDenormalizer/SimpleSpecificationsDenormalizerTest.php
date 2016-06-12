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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
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

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse('username')->willReturn(new FlagBag('username'));
        $flagParserProphecy->parse('setLocation')->willReturn(new FlagBag('setLocation'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleSpecificationsDenormalizer();
        $actual = $denormalizer->denormalizer($fixture, $flagParser, $specs);

        $this->assertEquals($expected, $actual);
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

        $denormalizer = new SimpleSpecificationsDenormalizer();
        $denormalizer->denormalizer($fixture, $flagParser, $specs);
    }
}
