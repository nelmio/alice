<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\IncludeProcessor\FakeIncludeProcessor;
use Nelmio\Alice\ParserInterface;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Parser\RuntimeCacheParser
 */
class RuntimeCacheParserTest extends \PHPUnit_Framework_TestCase
{
    private static $dir;

    public function setUp()
    {
        self::$dir = __DIR__.'/../../fixtures/Parser/files/cache';
    }

    public function testIsAParser()
    {
        $this->assertTrue(is_a(RuntimeCacheParser::class, ParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new RuntimeCacheParser(new FakeParser(), new FakeIncludeProcessor());
    }

    /**
     * @dataProvider provideParsableFile
     */
    public function testCanParseFile(string $file)
    {
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($file)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $includeProcessor = new FakeIncludeProcessor();

        $parser = new RuntimeCacheParser($decoratedParser, $includeProcessor);
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);

        // As the parser cache the results, parsing each file does not re-trigger a parse call
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);
    }

    public function testCacheParseResult()
    {
        $realFilePath = __FILE__;
        $file1 = $realFilePath;
        $file2 = __DIR__.'/../Parser/'.basename($realFilePath);
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($realFilePath)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $includeProcessor = new FakeIncludeProcessor();

        $parser = new RuntimeCacheParser($decoratedParser, $includeProcessor);
        $actual1 = $parser->parse($file1);
        $actual2 = $parser->parse($file2);

        $this->assertSame($expected, $actual1);
        $this->assertSame($actual1, $actual2);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testDoesntCacheParseResultIfNoAbsolutePathCouldBeRetrieved()
    {
        $file = 'https://example.com/script.php';
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($file)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $includeProcessor = new FakeIncludeProcessor();

        $parser = new RuntimeCacheParser($decoratedParser, $includeProcessor);
        $actual1 = $parser->parse($file);
        $actual2 = $parser->parse($file);

        $this->assertSame($expected, $actual1);
        $this->assertSame($actual1, $actual2);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testProcessesIncludesAndCacheTheResultOfEachIncludedFile()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
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
                self::$dir.'/file3.yml',
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
                'user_file3' => [],
                'user_file2' => [],
                'user_file1' => [],
                'user_main' => [],
            ],
        ];
        $expectedFile2 = [
            'Nelmio\Alice\Model\User' => [
                'user_file3' => [],
                'user_file2' => [],
            ],
        ];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse(Argument::containingString('main.yml'))->willReturn($parsedMainFileContent);
        $decoratedParserProphecy->parse(Argument::containingString('file1.yml'))->willReturn($parsedFile1Content);
        $decoratedParserProphecy->parse(Argument::containingString('file2.yml'))->willReturn($parsedFile2Content);
        $decoratedParserProphecy->parse(Argument::containingString('file3.yml'))->willReturn($parsedFile3Content);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new RuntimeCacheParser($decoratedParser, new DefaultIncludeProcessor(new DefaultFileLocator()));
        $actual = $parser->parse($mainFile);

        $this->assertSame($expected, $actual);

        // As the parser cache the results, parsing each file does not re-trigger a parse call
        $actual = $parser->parse($mainFile);
        $actualFile1 = $parser->parse('file1.yml');
        $actualFile2 = $parser->parse('file2.yml');
        $actualFile3 = $parser->parse('file3.yml');

        $this->assertSame($expected, $actual);
        $this->assertSame($parsedFile1Content, $actualFile1);
        $this->assertSame($expectedFile2, $actualFile2);
        $this->assertSame($parsedFile3Content, $actualFile3);

        $decoratedParserProphecy->parse(Argument::containingString('main.yml'))->shouldHaveBeenCalledTimes(1);
        $decoratedParserProphecy->parse('file1.yml')->shouldHaveBeenCalledTimes(1);
        $decoratedParserProphecy->parse('file2.yml')->shouldHaveBeenCalledTimes(1);
        $decoratedParserProphecy->parse('file3.yml')->shouldHaveBeenCalledTimes(1);
    }

    public function provideParsableFile()
    {
        return [
            'real existing file' => [__FILE__],
            'inexisting file' => ['/nowhere'],
        ];
    }
}
