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

use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\ObjectSet;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Loader\SimpleLoader
 */
class SimpleLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $builderRefl;

    /**
     * @var \ReflectionProperty
     */
    private $generatorRefl;

    public function setUp()
    {
        $refl = new \ReflectionClass(SimpleLoader::class);

        $this->builderRefl = $refl->getProperty('builder');
        $this->builderRefl->setAccessible(true);

        $this->generatorRefl = $refl->getProperty('generator');
        $this->generatorRefl->setAccessible(true);
    }

    public function testIsALoader()
    {
        $this->assertTrue(is_a(SimpleLoader::class, LoaderInterface::class, true));
    }

    public function testIsDeepClonable()
    {
        $fixtureBuilderProphecy = $this->prophesize(FixtureBuilderInterface::class);
        $fixtureBuilderProphecy->build(Argument::cetera())->shouldNotBeCalled();
        /** @var FixtureBuilderInterface $fixtureBuilder */
        $fixtureBuilder = $fixtureBuilderProphecy->reveal();

        $generatorProphecy = $this->prophesize(GeneratorInterface::class);
        $generatorProphecy->generate(Argument::cetera())->shouldNotBeCalled();
        /** @var GeneratorInterface $generator */
        $generator = $generatorProphecy->reveal();

        $loader = new SimpleLoader($fixtureBuilder, $generator);
        $clone = clone $loader;

        $this->assertEquals($loader, $clone);
        $this->assertNotSame($clone, $loader);

        $originalBuilder = $this->builderRefl->getValue($loader);
        $cloneBuilder = $this->builderRefl->getValue($clone);

        $this->assertEquals($originalBuilder, $cloneBuilder);
        $this->assertNotSame($originalBuilder, $cloneBuilder);

        $originalGenerator = $this->generatorRefl->getValue($loader);
        $cloneGenerator = $this->generatorRefl->getValue($clone);
        $this->assertEquals($originalGenerator, $cloneGenerator);
        $this->assertNotSame($originalGenerator, $cloneGenerator);
    }

    public function testLoadAFileAndReturnsAnObjectSet()
    {
        $file = 'dummy.yml';
        $parameters = [
            'foo' => 'bar',
        ];
        $objects = [
            'dummy0' => new \stdClass(),
        ];

        $fixtureSet = new FixtureSet();
        $objectSet = new ObjectSet();

        $fixtureBuilderProphecy = $this->prophesize(FixtureBuilderInterface::class);
        $fixtureBuilderProphecy->build($file, $parameters, $objects)->willReturn($fixtureSet);
        /** @var FixtureBuilderInterface $fixtureBuilder */
        $fixtureBuilder = $fixtureBuilderProphecy->reveal();

        $generatorProphecy = $this->prophesize(GeneratorInterface::class);
        $generatorProphecy->generate($fixtureSet)->willReturn($objectSet);
        /** @var GeneratorInterface $generator */
        $generator = $generatorProphecy->reveal();

        $loader = new SimpleLoader($fixtureBuilder, $generator);
        $result = $loader->load($file, $parameters, $objects);

        $this->assertSame($objectSet, $result);

        $fixtureBuilderProphecy->build(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $generatorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
