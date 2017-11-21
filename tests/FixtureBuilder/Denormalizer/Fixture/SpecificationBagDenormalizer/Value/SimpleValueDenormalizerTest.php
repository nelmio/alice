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
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;
use Nelmio\Alice\Throwable\Exception\RootParseException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer
 */
class SimpleValueDenormalizerTest extends TestCase
{
    public function testIsAValueDenormalizer()
    {
        $this->assertTrue(is_a(SimpleValueDenormalizer::class, ValueDenormalizerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleValueDenormalizer::class))->isCloneable());
    }

    public function testReturnsParsedValueIfValueIsAString()
    {
        $value = 'foo';

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($value)->willReturn($expected = 'parsed_value');
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new SimpleValueDenormalizer($parser);
        $actual = $denormalizer->denormalize(new FakeFixture(), new FlagBag(''), $value);

        $this->assertEquals($expected, $actual);

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheValueIsAnArrayThenAppliesItselfRecursivelyToArrays()
    {
        $value = [
            'foo',
            'bar' => 'baz',
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse('foo')->willReturn('parsed_foo');
        $parserProphecy->parse('baz')->willReturn('parsed_baz');
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $expected = new ArrayValue([
            'parsed_foo',
            'bar' => 'parsed_baz',
        ]);

        $denormalizer = new SimpleValueDenormalizer($parser);
        $actual = $denormalizer->denormalize(new FakeFixture(), new FlagBag(''), $value);

        $this->assertEquals($expected, $actual);

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testReturnsUnchangedValueIfTheValueIsNotAStringOrAnArray()
    {
        $value = $expected = 10;

        $denormalizer = new SimpleValueDenormalizer(new FakeParser());
        $actual = $denormalizer->denormalize(new FakeFixture(), new FlagBag(''), $value);

        $this->assertEquals($expected, $actual);
    }

    public function testWhenParserThrowsExceptionDenormalizerAExceptionIsThrown()
    {
        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy
            ->parse(Argument::any())
            ->willThrow(
                $thrownException = new RootParseException('hello world', 10)
            )
        ;
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizer = new SimpleValueDenormalizer($parser);
        try {
            $denormalizer->denormalize(new FakeFixture(), null, 'foo');
            $this->fail('Expected throwable to be thrown.');
        } catch (DenormalizationThrowable $throwable) {
            $this->assertEquals(
                'Could not parse value "foo".',
                $throwable->getMessage()
            );
            $this->assertEquals(0, $throwable->getCode());
            $this->assertEquals($thrownException, $throwable->getPrevious());
        }
    }
}
