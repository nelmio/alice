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

use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizer
 */
class ArgumentsDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testDenormalize()
    {
        $arguments = [
            '<latitude()>',
            '1 (unique)' => '<longitude()>',
            '2 (dummy_flag)' => 'dudu',
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'))->with(new UniqueFlag());
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        $arg2Flags = (new FlagBag('2'))->with(new ElementFlag('dummy_flag'));
        $flagParserProphecy->parse('2 (dummy_flag)')->willReturn($arg2Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new ArgumentsDenormalizer();
        $result = $denormalizer->denormalize($fixture, $flagParser, $arguments);

        $this->assertCount(3, $result);
        $this->assertEquals('<latitude()>', $result[0]);

        /** @var UniqueValue $uniqueValue */
        $uniqueValue = $result[1];
        $this->assertInstanceOf(UniqueValue::class, $uniqueValue);
        $this->assertEquals(1, preg_match('/^dummy.+$/', $uniqueValue->getId()));
        $this->assertEquals('<longitude()>', $uniqueValue->getValue());

        $this->assertEquals('dudu', $result[2]);

        $fixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }
}
