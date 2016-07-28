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
use Nelmio\Alice\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParserInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Loader\SimpleFileLoader
 */
class SimpleFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(SimpleFileLoader::class, FileLoaderInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {

        $loader = new SimpleFileLoader(new FakeParser(), new FakeDataLoader());
        clone $loader;
    }

    public function testLoadAFileAndReturnsAnObjectSet()
    {
        $file = 'dummy.yml';
        $data = [
            'Nelmio\Alice\Entity\Dummy' => [
                'dummy0' => [],
            ],
        ];
        $parameters = [
            'foo' => 'bar',
        ];
        $objects = [
            'dummy0' => new \stdClass(),
        ];
        $objectSet = new ObjectSet(new ParameterBag(), new ObjectBag());

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file)->willReturn($data);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $dataLoaderProphecy = $this->prophesize(DataLoaderInterface::class);
        $dataLoaderProphecy->loadData($data, $parameters, $objects)->willReturn($objectSet);
        /** @var DataLoaderInterface $dataLoader */
        $dataLoader = $dataLoaderProphecy->reveal();

        $loader = new SimpleFileLoader($parser, $dataLoader);
        $result = $loader->loadFile($file, $parameters, $objects);

        $this->assertSame($objectSet, $result);

        $parserProphecy->parse(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $dataLoaderProphecy->loadData(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
