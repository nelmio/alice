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
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Loader\SimpleDataLoader
 */
class SimpleDataLoaderTest extends TestCase
{
    public function testIsADataLoader()
    {
        $this->assertTrue(is_a(SimpleDataLoader::class, DataLoaderInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleDataLoader::class))->isCloneable());
    }

    public function testLoadAFileAndReturnsAnObjectSet()
    {
        $data = [new \stdClass()];
        $parameters = [
            'foo' => 'bar',
        ];
        $objects = [
            'dummy0' => new \stdClass(),
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

        $this->assertSame($objectSet, $result);

        $fixtureBuilderProphecy->build(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $generatorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
