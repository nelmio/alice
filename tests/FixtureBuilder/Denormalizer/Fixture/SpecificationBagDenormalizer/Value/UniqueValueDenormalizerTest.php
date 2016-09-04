<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Exception\RootParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer
 */
class UniqueValueDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValueDenormalizer()
    {
        $this->assertTrue(is_a(UniqueValueDenormalizer::class, ValueDenormalizerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new UniqueValueDenormalizer(new FakeParser());
    }

    /**
     * @dataProvider provideValues
     */
    public function testReturnsParsedValueIfNoUniqueFlagsHasBeenFound($value, bool $parserCalled, FlagBag $flags = null)
    {
        $expected = $parserCalled ? 'parsed_value' : $value;

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse('1')->willReturn('parsed_value');
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($parser);
        $actual = $denormalizer->denormalize(new FakeFixture(), $flags, $value);

        $this->assertEquals($expected, $actual);

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes($parserCalled);
    }

    /**
     * @dataProvider provideValues
     */
    public function testReturnsUniqueValueIfUniqueFlagsFound($value, bool $parserCalled)
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $expected = $parserCalled ? 'parsed_value' : $value;

        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse('1')->willReturn('parsed_value');
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($parser);
        $actual = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertInstanceOf(UniqueValue::class, $actual);
        /** @var UniqueValue $actual */
        $this->assertEquals($expected, $actual->getValue());
        $this->stringContains('dummy_id', $actual->getId());

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes($parserCalled);
    }

    public function testIfParsedValueIsDynamicArrayUniqueFlagAppliesToItsElementInstead()
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $parsedValue = new DynamicArrayValue(10, 'parsed_value');
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($value)->willReturn($parsedValue);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($parser);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertInstanceOf(DynamicArrayValue::class, $result);
        /** @var DynamicArrayValue $result */
        $this->assertEquals(10, $result->getQuantifier());
        $this->assertInstanceOf(UniqueValue::class, $result->getElement());
        $this->stringContains('dummy_id', $result->getElement()->getId());
        $this->assertEquals('parsed_value', $result->getElement()->getValue());
    }

    public function testWhenParserThrowsExceptionDenormalizerAExceptionIsThrown()
    {
        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->willThrow(new RootParseException());
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($parser);
        try {
            $denormalizer->denormalize(new FakeFixture(), null, '');
            $this->fail('Expected throwable to be thrown.');
        } catch (DenormalizationThrowable $throwable) {
            // expected result
        }
    }

    public function provideValues()
    {
        $unparsedValues = [
            'null' => null,
            'int' => 0,
            'float' => .5,
            'bool' => true,
            'array' => [],
            'object' => new \stdClass(),
        ];

        $flagBags = [
            'null' => null,
            'empty' => new FlagBag(''),
            'with random flag' => (new FlagBag(''))->withFlag(new DummyFlag()),
        ];

        foreach ($flagBags as $flagName => $flags) {
            foreach ($unparsedValues as $unparsedValueName => $unparsedValue) {
                yield $unparsedValueName.'/'.$flagName => [$unparsedValue, false, $flags];
            }

            yield 'string value /'.$flagName => ['1', true, $flags];
        }
    }
}
