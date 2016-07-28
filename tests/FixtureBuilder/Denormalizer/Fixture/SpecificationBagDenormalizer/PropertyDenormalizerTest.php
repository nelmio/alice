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

use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\PropertyDenormalizer
 */
class PropertyDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testDenormalize()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $name = 'groupId';
        $value = 10;
        $flags = new FlagBag('');

        $expected = new Property($name, $value);

        $denormalizer = new PropertyDenormalizer(new FakeParser());
        $actual = $denormalizer->denormalize($fixture, $name, $value, $flags);
        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeWithUniqueFlag()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $name = 'groupId';
        $value = 10;
        $flags = (new FlagBag(''))->with(new UniqueFlag());

        $expected = new Property($name, $value);

        $denormalizer = new PropertyDenormalizer(new FakeParser());
        $result = $denormalizer->denormalize($fixture, $name, $value, $flags);

        $this->assertEquals('groupId', $expected->getName());
        /** @var UniqueValue $uniqueValue */
        $uniqueValue = $result->getValue();
        $this->assertInstanceOf(UniqueValue::class, $uniqueValue);
        $this->assertEquals(1, preg_match('/^dummy.+$/', $uniqueValue->getId()));
    }

    public function testParseStringValues()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $name = 'username';
        $value = '<name()>';
        $parsedValue = new FunctionCallValue('name');
        $flags = (new FlagBag(''))->with(new UniqueFlag());

        $expected = new Property($name, $parsedValue);

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse('<name()>')->willReturn($parsedValue);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new PropertyDenormalizer($parser);
        $result = $denormalizer->denormalize($fixture, $name, $value, $flags);

        $this->assertEquals('username', $expected->getName());
        /** @var UniqueValue $uniqueValue */
        $uniqueValue = $result->getValue();
        $this->assertInstanceOf(UniqueValue::class, $uniqueValue);
        $this->assertEquals(1, preg_match('/^dummy.+$/', $uniqueValue->getId()));

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
