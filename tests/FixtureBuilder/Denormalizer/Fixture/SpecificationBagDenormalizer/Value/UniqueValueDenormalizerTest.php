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
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Exception\RootParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer
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
        clone new UniqueValueDenormalizer(new FakeValueDenormalizer());
    }

    public function testReturnsParsedValueIfNoUniqueFlagsHasBeenFound()
    {
        $fixture = new FakeFixture();
        $flags = new FlagBag('');
        $value = 'foo';

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($expected = 'denormalized_value')
        ;
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertEquals($expected, $actual);

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsUniqueValueIfUniqueFlagsFound()
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());
        $value = 'foo';

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn('denormalized_value')
        ;
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertInstanceOf(UniqueValue::class, $result);
        $this->stringContains('dummy_id', $result->getId());
        $this->assertEquals('denormalized_value', $result->getValue());

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfParsedValueIsDynamicArrayThenUniqueFlagAppliesToItsElementInstead()
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $denormalizedValue = new DynamicArrayValue(10, 'parsed_value');
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($denormalizedValue)
        ;
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertInstanceOf(DynamicArrayValue::class, $result);
        /** @var DynamicArrayValue $result */
        $this->assertEquals(10, $result->getQuantifier());
        $this->assertInstanceOf(UniqueValue::class, $result->getElement());
        $this->stringContains('dummy_id', $result->getElement()->getId());
        $this->assertEquals('parsed_value', $result->getElement()->getValue());
    }

    public function testIfParsedValueIsArrayValueThenUniqueFlagAppliesToItsElementInstead()
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $denormalizedValue = new ArrayValue(['foo', 'bar']);
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($denormalizedValue)
        ;
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        $this->assertInstanceOf(ArrayValue::class, $result);
        /** @var ArrayValue $result */
        $this->assertInstanceOf(UniqueValue::class, $result->getValue()[0]);
        $this->stringContains('dummy_id', $result->getValue()[0]->getId());
        $this->assertEquals('foo', $result->getValue()[0]->getValue());

        $this->assertInstanceOf(UniqueValue::class, $result->getValue()[1]);
        $this->stringContains('dummy_id', $result->getValue()[1]->getId());
        $this->assertEquals('bar', $result->getValue()[1]->getValue());
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
