<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\FakeValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer
 */
class SimpleArgumentsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SimpleArgumentsDenormalizer(new FakeValueDenormalizer());
    }

    public function testParsesStringKeys()
    {
        $arguments = [
            0 => 'foo',
            '1' => 'bar',   // will become numeric
            '2 (dummy_flag)' => 'baz',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse('2 (dummy_flag)')
            ->willReturn(
                $arg2Flags = (new FlagBag('2'))->withFlag(new ElementFlag('dummy_flag'))
            )
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $valueDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $valueDenormalizerProphecy->denormalize(Argument::cetera())->will(function ($args) { return $args[2]; });
        /** @var ValueDenormalizerInterface $valueDenormalizer */
        $valueDenormalizer = $valueDenormalizerProphecy->reveal();

        $denormalizer = new SimpleArgumentsDenormalizer($valueDenormalizer);
        $denormalizer->denormalize(new FakeFixture(), $flagParser, $arguments);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizesEachArgument()
    {
        $arguments = [
            '0 (dummy_flag)' => '<latitude()>',
            '1 (dummy_flag)' => '<longitude()>',
            '2 (dummy_flag)' => 'dudu',
            '3 (dummy_flag)' => 1000,
        ];
        $fixture = new FakeFixture();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->will(function ($args) { return new FlagBag($args[0]); });
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $valueDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $valueDenormalizerProphecy->denormalize(Argument::cetera())->will(function ($args) { return $args[2]; });
        /** @var ValueDenormalizerInterface $valueDenormalizer */
        $valueDenormalizer = $valueDenormalizerProphecy->reveal();

        $denormalizer = new SimpleArgumentsDenormalizer($valueDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flagParser, $arguments);

        $this->assertEquals(
            [
                '<latitude()>',
                '<longitude()>',
                'dudu',
                1000,
            ],
            $result
        );

        $valueDenormalizerProphecy->denormalize($fixture, new FlagBag('0 (dummy_flag)'), '<latitude()>')->shouldHaveBeenCalledTimes(1);
        $valueDenormalizerProphecy->denormalize($fixture, new FlagBag('1 (dummy_flag)'), '<longitude()>')->shouldHaveBeenCalledTimes(1);
        $valueDenormalizerProphecy->denormalize($fixture, new FlagBag('2 (dummy_flag)'), 'dudu')->shouldHaveBeenCalledTimes(1);
        $valueDenormalizerProphecy->denormalize($fixture, new FlagBag('3 (dummy_flag)'), 1000)->shouldHaveBeenCalledTimes(1);
    }
}
