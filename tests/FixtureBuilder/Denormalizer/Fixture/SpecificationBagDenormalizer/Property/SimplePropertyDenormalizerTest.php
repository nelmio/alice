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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer
 * @internal
 */
class SimplePropertyDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimplePropertyDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesValueBeforeReturningProperty(): void
    {
        $fixture = new FakeFixture();
        $name = 'groupId';
        $value = 10;
        $flags = new FlagBag('');

        $valueDenormalizerProphecy = $this->prophesize(ValueDenormalizerInterface::class);
        $valueDenormalizerProphecy->denormalize($fixture, $flags, $value)->willReturn('denormalized_value');
        /** @var ValueDenormalizerInterface $valueDenormalizer */
        $valueDenormalizer = $valueDenormalizerProphecy->reveal();

        $expected = new Property($name, 'denormalized_value');

        $denormalizer = new SimplePropertyDenormalizer($valueDenormalizer);
        $actual = $denormalizer->denormalize($fixture, $name, $value, $flags);

        self::assertEquals($expected, $actual);

        $valueDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
