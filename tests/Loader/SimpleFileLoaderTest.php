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
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectSetFactory;
use Nelmio\Alice\ParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Loader\SimpleFileLoader
 * @internal
 */
class SimpleFileLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALoader(): void
    {
        self::assertTrue(is_a(SimpleFileLoader::class, FileLoaderInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleFileLoader::class))->isCloneable());
    }

    public function testLoadAFileAndReturnsAnObjectSet(): void
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
            'dummy0' => new stdClass(),
        ];
        $objectSet = ObjectSetFactory::create();

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

        self::assertSame($objectSet, $result);

        $parserProphecy->parse(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $dataLoaderProphecy->loadData(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
