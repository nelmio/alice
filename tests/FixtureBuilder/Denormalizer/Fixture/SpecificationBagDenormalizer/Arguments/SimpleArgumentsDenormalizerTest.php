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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer
 */
class SimpleArgumentsDenormalizerTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleArgumentsDenormalizer::class))->isCloneable());
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
        $valueDenormalizerProphecy->denormalize(Argument::cetera())->will(function ($args) {
            return $args[2];
        });
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
            'foo (dummy_flag)' => '<longitude()>',
            'bar (dummy_flag)' => 'dudu',
            '3 (dummy_flag)' => 1000,
            500,
        ];
        $fixture = new FakeFixture();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse(Argument::any())
            ->will(
                function ($args) {
                    preg_match('/(?<val>.+?)\s\(.+\)/', $args[0], $matches);

                    return new FlagBag($matches['val']);
                }
            )
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $valueDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $valueDenormalizerProphecy->denormalize(Argument::cetera())->will(function ($args) {
            return $args[2];
        });
        /** @var ValueDenormalizerInterface $valueDenormalizer */
        $valueDenormalizer = $valueDenormalizerProphecy->reveal();

        $denormalizer = new SimpleArgumentsDenormalizer($valueDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flagParser, $arguments);

        $this->assertEquals(
            [
                0 => '<latitude()>',
                'foo' => '<longitude()>',
                'bar' => 'dudu',
                3 => 1000,
                4 => 500,
            ],
            $result
        );

        $valueDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(5);
    }
}
