<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\FakeFixtureBuilder;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\FakeGenerator;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Loader\SimpleDataLoader
 */
class SimpleDataLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsADataLoader()
    {
        $this->assertTrue(is_a(SimpleDataLoader::class, DataLoaderInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $loader = new SimpleDataLoader(new FakeFixtureBuilder(), new FakeGenerator());
        clone $loader;
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

        $fixtureSet = new FixtureSet(new ParameterBag(), new ParameterBag(), new FixtureBag(), new ObjectBag());
        $objectSet = new ObjectSet();

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
