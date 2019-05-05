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
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\ObjectSetFactory;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\User;
use Nelmio\Alice\UserDetail;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Loader\SimpleFilesLoader
 */
class SimpleFilesLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(SimpleFilesLoader::class, FilesLoaderInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleFilesLoader::class))->isCloneable());
    }

    public function testLoadFilesAndReturnsAnObjectSet()
    {
        $files = [
            $file1 = 'dummy.yml',
            $file2 = $file1,
            $file3 = 'another_dummy.yml',
            $file4 = 'dummy4.yml',
        ];

        $file1Data = [
            'Nelmio\Alice\Entity\Dummy' => [
                'dummy0' => [],
            ],
        ];
        $file3Data = [
            'Nelmio\Alice\Entity\AnotherDummy' => [
                'another_dummy0' => [],
            ],
        ];
        $file4Data = [
            'Nelmio\Alice\Entity\Dummy' => [
                'dummy4' => [],
            ],
        ];

        $expectedData = [
            'Nelmio\Alice\Entity\Dummy' => [
                'dummy0' => [],
                'dummy4' => [],
            ],
            'Nelmio\Alice\Entity\AnotherDummy' => [
                'another_dummy0' => [],
            ],
        ];

        $parameters = [
            'foo' => 'bar',
        ];

        $objects = [
            'dummy0' => new stdClass(),
            'dummy4' => new stdClass(),
            'another_dummy0' => new stdClass(),
        ];

        $objectSet = ObjectSetFactory::create();

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file1)->willReturn($file1Data);
        $parserProphecy->parse($file3)->willReturn($file3Data);
        $parserProphecy->parse($file4)->willReturn($file4Data);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $dataLoaderProphecy = $this->prophesize(DataLoaderInterface::class);
        $dataLoaderProphecy->loadData($expectedData, $parameters, $objects)->willReturn($objectSet);
        /** @var DataLoaderInterface $dataLoader */
        $dataLoader = $dataLoaderProphecy->reveal();

        $loader = new SimpleFilesLoader($parser, $dataLoader);
        $result = $loader->loadFiles($files, $parameters, $objects);

        $this->assertSame($objectSet, $result);

        $parserProphecy->parse(Argument::cetera())->shouldHaveBeenCalledTimes(3);
        $dataLoaderProphecy->loadData(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testLoadFilesWithDifferentOrder()
    {
        $file1 = 'dummy1.yml';
        $file2 = 'dummy2.yml';

        $file1Data = [
            User::class => [
                'user1' => [
                    'name' => '<username()>',
                ],
            ],
        ];
        $file2Data = [
            UserDetail::class => [
                'userdetail_{@user*}' => [
                    'email' => '<email()>',
                ],
                'userdetail_single_{@user1}' => [
                    'email' => '<email()>',
                ],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file1)->willReturn($file1Data);
        $parserProphecy->parse($file2)->willReturn($file2Data);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $loader = new SimpleFilesLoader($parser, new IsolatedLoader());

        $objects = $loader->loadFiles([$file2, $file1], [], [])->getObjects();
        self::assertArrayHasKey('user1', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);
        self::assertArrayHasKey('userdetail_single_user1', $objects);

        $objects = $loader->loadFiles([$file1, $file2], [], [])->getObjects();
        self::assertArrayHasKey('user1', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);
        self::assertArrayHasKey('userdetail_single_user1', $objects);
    }

    public function testLoadFixturesWithDifferentOrder()
    {
        $file1 = 'dummy1.yml';
        $file2 = 'dummy2.yml';

        $file1Data = [
            User::class => [
                'user1' => [
                    'name' => '<username()>',
                ],
            ],
            UserDetail::class => [
                'userdetail_{@user*}' => [
                    'email' => '<email()>',
                ],
                'userdetail_single_{@user1}' => [
                    'email' => '<email()>',
                ],
            ],
        ];
        $file2Data = [
            UserDetail::class => [
                'userdetail_{@user*}' => [
                    'email' => '<email()>',
                ],
                'userdetail_single_{@user1}' => [
                    'email' => '<email()>',
                ],
            ],
            User::class => [
                'user1' => [
                    'name' => '<username()>',
                ],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file1)->willReturn($file1Data);
        $parserProphecy->parse($file2)->willReturn($file2Data);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $loader = new SimpleFilesLoader($parser, new IsolatedLoader());

        $objects = $loader->loadFiles([$file1], [], [])->getObjects();
        self::assertArrayHasKey('user1', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);
        self::assertArrayHasKey('userdetail_single_user1', $objects);

        $objects = $loader->loadFiles([$file2], [], [])->getObjects();
        self::assertArrayHasKey('user1', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);
        self::assertArrayHasKey('userdetail_single_user1', $objects);
    }
}
