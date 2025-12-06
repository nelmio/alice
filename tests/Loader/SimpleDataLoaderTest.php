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

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSetFactory;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\ObjectSetFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Loader\SimpleDataLoader
 * @internal
 */
final class SimpleDataLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testIsADataLoader(): void
    {
        self::assertTrue(is_a(SimpleDataLoader::class, DataLoaderInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleDataLoader::class))->isCloneable());
    }

    public function testLoadAFileAndReturnsAnObjectSet(): void
    {
        $data = [new stdClass()];
        $parameters = [
            'foo' => 'bar',
        ];
        $objects = [
            'dummy0' => new stdClass(),
        ];

        $fixtureSet = FixtureSetFactory::create();
        $objectSet = ObjectSetFactory::create();

        $fixtureBuilderProphecy = $this->prophesize(FixtureBuilderInterface::class);
        $fixtureBuilderProphecy->build($data, $parameters, $objects)->willReturn($fixtureSet);
        /** @var FixtureBuilderInterface $fixtureBuilder */
        $fixtureBuilder = $fixtureBuilderProphecy->reveal();

        $generatorProphecy = $this->prophesize(GeneratorInterface::class);
        $generatorProphecy->generate($fixtureSet)->willReturn($objectSet);
        /** @var GeneratorInterface $generator */
        $generator = $generatorProphecy->reveal();

        $loader = new SimpleDataLoader($fixtureBuilder, $generator);
        $result = $loader->loadData($data, $parameters, $objects);

        self::assertSame($objectSet, $result);

        $fixtureBuilderProphecy->build(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $generatorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
