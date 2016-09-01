<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

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
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\OptionalCallsDenormalizer
 */
class OptionalCallsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new OptionalCallsDenormalizer(new FakeArgumentsDenormalizer());
    }

    public function testDenormalizesInputToReturnAMethodCall()
    {
        $fixture = new FakeFixture();

        $unparsedMethod = 'setLocation';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse($unparsedMethod)->willReturn(new FlagBag('parsed_method'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn($parsedArguments = [new \stdClass()])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall('parsed_method', $parsedArguments);

        $denormalizer = new OptionalCallsDenormalizer($argumentsDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flagParser, $unparsedMethod, $unparsedArguments);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $argumentsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @dataProvider provideMethods
     */
    public function testDenormalizesMethodWithOptionalCallFlagToReturnAOptionalMethodCall(
        FlagParserInterface $flagParser,
        bool $optional
    ) {
        $fixture = new FakeFixture();

        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $unparsedArguments)
            ->willReturn($parsedArguments = [new \stdClass()])
        ;
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall('parsed_method', $parsedArguments);

        $denormalizer = new OptionalCallsDenormalizer($argumentsDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flagParser, 'something', $unparsedArguments);

        if ($optional) {
            $this->assertEquals(
                new OptionalMethodCall($expected, new OptionalFlag(80)),
                $actual
            );
        } else {
            $this->assertEquals($expected, $actual);
        }
    }

    public function provideMethods()
    {
        $flags = new FlagBag('parsed_method');

        yield 'no flag' => [
            $this->createFlagParser($flags),
            false,
        ];

        yield 'dummy flag' => [
            $this->createFlagParser($flags->withFlag(new DummyFlag())),
            false,
        ];

        yield 'optional flag' => [
            $this->createFlagParser($flags->withFlag(new OptionalFlag(80))),
            true,
        ];

        yield 'optional and dummy flag' => [
            $this->createFlagParser($flags->withFlag(new DummyFlag())->withFlag(new OptionalFlag(80))),
            true,
        ];
    }

    private function createFlagParser(FlagBag $flags): FlagParserInterface
    {
        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->willReturn($flags);

        return $flagParserProphecy->reveal();
    }
}
