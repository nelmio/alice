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

namespace Nelmio\Alice\Parser;

use InvalidArgumentException;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\IncludeProcessor\FakeIncludeProcessor;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Parser\RuntimeCacheParser
 */
class RuntimeCacheParserTest extends TestCase
{
    use ProphecyTrait;

    private static $dir;
    
    protected function setUp(): void
    {
        self::$dir = __DIR__.'/../../fixtures/Parser/files/cache';
    }

    public function testIsAParser(): void
    {
        static::assertTrue(is_a(RuntimeCacheParser::class, ParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(RuntimeCacheParser::class))->isCloneable());
    }

    public function testCanParseFile(): void
    {
        $file = 'foo.php';
        $expected = [new stdClass()];

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate($file)->willReturn('/path/to/foo.php');
        /** @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('/path/to/foo.php')->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $includeProcessor = new FakeIncludeProcessor();

        $parser = new RuntimeCacheParser($decoratedParser, $fileLocator, $includeProcessor);
        $actual = $parser->parse($file);

        static::assertSame($expected, $actual);

        // As the parser cache the results, parsing each file does not re-trigger a parse call
        $actual = $parser->parse($file);

        static::assertSame($expected, $actual);
    }

    public function testParsesTheResultAndCacheIt(): void
    {
        $file1 = 'foo.php';
        $file2 = '/another/path/to/foo.php';
        $file3 = 'bar.yml';

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate($file1)->willReturn('/path/to/foo.php');
        $fileLocatorProphecy->locate($file2)->willReturn('/path/to/foo.php');
        $fileLocatorProphecy->locate($file3)->willReturn('/path/to/bar.php');
        /** @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse('/path/to/foo.php')
            ->willReturn(
                $file1Result = [
                    'parameters' => [
                        'foo',
                    ],
                ]
            )
        ;
        $decoratedParserProphecy
            ->parse('/path/to/bar.php')
            ->willReturn(
                $file3Result = [
                    'parameters' => [
                        'bar',
                    ],
                ]
            )
        ;
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $includeProcessor = new FakeIncludeProcessor();

        $parser = new RuntimeCacheParser($decoratedParser, $fileLocator, $includeProcessor);
        $actual1 = $parser->parse($file1);
        $actual2 = $parser->parse($file2);
        $actual3 = $parser->parse($file3);

        static::assertSame($file1Result, $actual1);
        static::assertSame($file1Result, $actual2);
        static::assertSame($file3Result, $actual3);

        $fileLocatorProphecy->locate(Argument::any())->shouldHaveBeenCalledTimes(3);
        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testThrowsAnExceptionIfFileCouldNotBeFound(): void
    {
        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate(Argument::any())->willThrow(FileNotFoundException::class);
        /** @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $parser = new RuntimeCacheParser(new FakeParser(), $fileLocator, new FakeIncludeProcessor());
        try {
            $parser->parse('/nowhere');

            static::fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'The file "/nowhere" could not be found.',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testProcessesIncludesAndCacheTheResultOfEachIncludedFile(): void
    {
        $mainFile = '/path/to/main.yml';
        $parsedMainFileContent = [
            'include' => [
                'file1.yml',
                'another_level/file2.yml',
            ],
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];
        $parsedFile1Content = [
            'Nelmio\Alice\Model\User' => [
                'user_file1' => [],
            ],
        ];
        $parsedFile2Content = [
            'include' => [
                '/path/to/file3.yml',
            ],
            'Nelmio\Alice\Model\User' => [
                'user_file2' => [],
            ],
        ];
        $parsedFile3Content = [
            'Nelmio\Alice\Model\User' => [
                'user_file3' => [],
            ],
        ];
        $expected = [
            'Nelmio\Alice\Model\User' => [
                'user_file1' => [],
                'user_file3' => [],
                'user_file2' => [],
                'user_main' => [],
            ],
        ];
        $expectedFile2 = [
            'Nelmio\Alice\Model\User' => [
                'user_file3' => [],
                'user_file2' => [],
            ],
        ];

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate($mainFile)->willReturn($mainFile);
        $fileLocatorProphecy->locate('file1.yml', '/path/to')->willReturn('/path/to/file1.yml');
        $fileLocatorProphecy->locate('/path/to/file1.yml')->willReturn('/path/to/file1.yml');
        $fileLocatorProphecy->locate('another_level/file2.yml', '/path/to')->willReturn('/path/to/file2.yml');
        $fileLocatorProphecy->locate('/path/to/file2.yml')->willReturn('/path/to/file2.yml');
        $fileLocatorProphecy->locate('/path/to/file3.yml', '/path/to')->willReturn('/path/to/file3.yml');
        $fileLocatorProphecy->locate('/path/to/file3.yml')->willReturn('/path/to/file3.yml');
        /** @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('/path/to/main.yml')->willReturn($parsedMainFileContent);
        $decoratedParserProphecy->parse('/path/to/file1.yml')->willReturn($parsedFile1Content);
        $decoratedParserProphecy->parse('/path/to/file2.yml')->willReturn($parsedFile2Content);
        $decoratedParserProphecy->parse('/path/to/file3.yml')->willReturn($parsedFile3Content);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new RuntimeCacheParser($decoratedParser, $fileLocator, new DefaultIncludeProcessor($fileLocator));
        $actual = $parser->parse($mainFile);

        static::assertSame($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(4);
        $fileLocatorProphecy->locate(Argument::any())->shouldHaveBeenCalledTimes(4 + 2); // nbr of files + includes


        // As the parser cache the results, parsing each file does not re-trigger a parse call
        $fileLocatorProphecy->locate('file1.yml')->willReturn('/path/to/file1.yml');
        $fileLocatorProphecy->locate('file2.yml')->willReturn('/path/to/file2.yml');
        $fileLocatorProphecy->locate('file3.yml')->willReturn('/path/to/file3.yml');

        $actual = $parser->parse($mainFile);
        $actualFile1 = $parser->parse('file1.yml');
        $actualFile2 = $parser->parse('file2.yml');
        $actualFile3 = $parser->parse('file3.yml');

        static::assertSame($expected, $actual);
        static::assertSame($parsedFile1Content, $actualFile1);
        static::assertSame($expectedFile2, $actualFile2);
        static::assertSame($parsedFile3Content, $actualFile3);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(4);
        $fileLocatorProphecy->locate(Argument::any())->shouldHaveBeenCalledTimes(4 + 4 + 2);
    }
}
