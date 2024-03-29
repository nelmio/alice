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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer
 * @internal
 */
class ConstructorDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ConstructorDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesInputAsAConstructorMethod(): void
    {
        $arguments = ['foo', 'bar'];
        $fixture = new FakeFixture();
        $flagParser = new FakeFlagParser();

        $argumentsDenormalizerProphecy = $this->prophesize(ArgumentsDenormalizerInterface::class);
        $argumentsDenormalizerProphecy
            ->denormalize($fixture, $flagParser, $arguments)
            ->willReturn($arguments);
        /** @var ArgumentsDenormalizerInterface $argumentsDenormalizer */
        $argumentsDenormalizer = $argumentsDenormalizerProphecy->reveal();

        $expected = new SimpleMethodCall(
            '__construct',
            ['foo', 'bar'],
        );

        $denormalizer = new ConstructorDenormalizer($argumentsDenormalizer);

        $actual = $denormalizer->denormalize($fixture, $flagParser, $arguments);

        self::assertEquals($expected, $actual);

        $argumentsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
