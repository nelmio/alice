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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer
 * @internal
 */
final class UniqueValueDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAValueDenormalizer(): void
    {
        self::assertTrue(is_a(UniqueValueDenormalizer::class, ValueDenormalizerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(UniqueValueDenormalizer::class))->isCloneable());
    }

    public function testReturnsParsedValueIfNoUniqueFlagsHasBeenFound(): void
    {
        $fixture = new FakeFixture();
        $flags = new FlagBag('');
        $value = 'foo';

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($expected = 'denormalized_value');
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $flags, $value);

        self::assertEquals($expected, $actual);

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsUniqueValueIfUniqueFlagsFound(): void
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());
        $value = 'foo';

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn('denormalized_value');
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        self::assertInstanceOf(UniqueValue::class, $result);
        self::assertStringStartsWith('Dummy#', $result->getId());
        self::assertEquals('denormalized_value', $result->getValue());

        $decoratedDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfParsedValueIsDynamicArrayThenUniqueFlagAppliesToItsElementInstead(): void
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $denormalizedValue = new DynamicArrayValue(10, 'parsed_value');
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($denormalizedValue);
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        self::assertInstanceOf(DynamicArrayValue::class, $result);
        /** @var DynamicArrayValue $result */
        self::assertEquals(10, $result->getQuantifier());
        self::assertInstanceOf(UniqueValue::class, $result->getElement());
        self::assertStringStartsWith('Dummy#', $result->getElement()->getId());
        self::assertEquals('parsed_value', $result->getElement()->getValue());
    }

    public function testThrowsAnExceptionIsATemporaryFixtureWithAUniqueValue(): void
    {
        $fixture = new SimpleFixture(uniqid('temporary_id'), 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $denormalizedValue = new FakeValue();
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($denormalizedValue);
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);

        $this->expectException(InvalidScopeException::class);
        $this->expectExceptionMessage('Cannot bind a unique value scope to a temporary fixture.');

        $denormalizer->denormalize($fixture, $flags, $value);
    }

    public function testIfParsedValueIsArrayValueThenUniqueFlagAppliesToItsElementInstead(): void
    {
        $fixture = new SimpleFixture('dummy_id', 'Dummy', SpecificationBagFactory::create());
        $value = 'string value';
        $denormalizedValue = new ArrayValue(['foo', 'bar']);
        $flags = (new FlagBag(''))->withFlag(new UniqueFlag());

        $decoratedDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $decoratedDenormalizerProphecy
            ->denormalize($fixture, $flags, $value)
            ->willReturn($denormalizedValue);
        /** @var ValueDenormalizerInterface $decoratedDenormalizer */
        $decoratedDenormalizer = $decoratedDenormalizerProphecy->reveal();

        $denormalizer = new UniqueValueDenormalizer($decoratedDenormalizer);
        $result = $denormalizer->denormalize($fixture, $flags, $value);

        self::assertInstanceOf(ArrayValue::class, $result);
        /** @var ArrayValue $result */
        self::assertInstanceOf(UniqueValue::class, $result->getValue()[0]);
        self::assertStringStartsWith('Dummy#', $result->getValue()[0]->getId());
        self::assertEquals('foo', $result->getValue()[0]->getValue());

        self::assertInstanceOf(UniqueValue::class, $result->getValue()[1]);
        self::assertStringStartsWith('Dummy#', $result->getValue()[1]->getId());
        self::assertEquals('bar', $result->getValue()[1]->getValue());
    }

    public function provideValues()
    {
        $unparsedValues = [
            'null' => null,
            'int' => 0,
            'float' => .5,
            'bool' => true,
            'array' => [],
            'object' => new stdClass(),
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
