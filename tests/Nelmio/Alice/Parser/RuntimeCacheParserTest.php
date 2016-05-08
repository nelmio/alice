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

use Nelmio\Alice\ParserInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Parser\RuntimeCacheParser
 */
class RuntimeCacheParserTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_a_parser()
    {
        $this->assertTrue(is_a(RuntimeCacheParser::class, ParserInterface::class, true));
    }

    /**
     * @dataProvider provideParsableFile
     */
    public function test_can_parse_file(string $file)
    {
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($file)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new RuntimeCacheParser($decoratedParser);
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);

        // as the parser cache the result, run a second time
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);
    }

    public function test_cache_parse_result()
    {
        $realFilePath = __FILE__;
        $file1 = $realFilePath;
        $file2 = __DIR__.'/../Parser/'.basename($realFilePath);
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($realFilePath)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new RuntimeCacheParser($decoratedParser);
        $actual1 = $parser->parse($file1);
        $actual2 = $parser->parse($file2);

        $this->assertSame($expected, $actual1);
        $this->assertSame($actual1, $actual2);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function test_dont_cache_parse_result_with_no_absolute_path_could_be_retrieved()
    {
        $file = 'https://example.com/script.php';
        $expected = [new \stdClass()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($file)->willReturn($expected);
        /* @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new RuntimeCacheParser($decoratedParser);
        $actual1 = $parser->parse($file);
        $actual2 = $parser->parse($file);

        $this->assertSame($expected, $actual1);
        $this->assertSame($actual1, $actual2);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function provideParsableFile()
    {
        return [
            'real existing file' => [__FILE__],
            'inexisting file' => ['/nowhere'],
        ];
    }
}
