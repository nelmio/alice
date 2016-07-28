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

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\ExpressionLanguage\Parser\DummyParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizer
 */
class CallsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallsDenormalizer
     */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = new CallsDenormalizer(new DummyParser());
    }

    public function testDenormalize()
    {
        $unparsedMethod = 'setLocation';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse($unparsedMethod)->willReturn(new FlagBag('setLocation'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new SimpleMethodCall(
            'setLocation',
            [
                '<latitude()>',
                '<longitude()>',
            ]
        );
        
        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedMethod, $unparsedArguments);

        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeWithFlags()
    {
        $unparsedMethod = 'setLocation (50%?)';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $optionalFlag = new OptionalFlag(50);
        $flagParserProphecy->parse($unparsedMethod)->willReturn((new FlagBag('setLocation'))->with($optionalFlag));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new OptionalMethodCall(
            new SimpleMethodCall(
                'setLocation',
                [
                    '<latitude()>',
                    '<longitude()>',
                ]
            ),
            new OptionalFlag(50)
        );

        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedMethod, $unparsedArguments);

        $this->assertEquals($expected, $actual);
    }
}
