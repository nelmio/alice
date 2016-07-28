<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use Prophecy\Argument;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Parser\Chainable\YamlParser
 */
class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var YamlParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../../../fixtures/Nelmio/Alice/Parser/files/yaml';
    }

    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $this->parser = new YamlParser($symfonyYamlParser);
    }

    public function testIsAChainableParser()
    {
        $this->assertTrue(is_a(YamlParser::class, ChainableParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone $this->parser;
    }

    /**
     * @dataProvider providePhpList
     */
    public function testCannotParsePhpFiles(string $file)
    {
        $actual = $this->parser->canParse($file);

        $this->assertFalse($actual);
    }

    /**
     * @dataProvider provideYamlList
     */
    public function testCanParseYamlFiles(string $file, array $expectedParsers)
    {
        $actual = $this->parser->canParse($file);
        $expected = (in_array(get_class($this->parser), $expectedParsers));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideUnsupportedList
     */
    public function testCannotParseUnsupportedFiles(string $file)
    {
        $actual = $this->parser->canParse($file);

        $this->assertFalse($actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File "/nowhere.yml" could not be found.
     */
    public function testThrowExceptionIfFileDoesNotExist()
    {
        $this->parser->parse('/nowhere.yml');
    }

    public function testUseSymfonyParserToParseFile()
    {
        $file = self::$dir.'/basic.yml';
        $fileContent = <<<'EOF'
Nelmio\Alice\Model\User:
    user0:
        fullname: John Doe

EOF;

        $expected = [new \stdClass()];

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse($fileContent)->willReturn($expected);
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);

        $symfonyYamlParserProphecy->parse(Argument::any())->shouldBeCalledTimes(1);
    }

    public function testParseRegularFile()
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/basic.yml');

        $this->assertSame(
            [
                'Nelmio\Alice\Model\User' => [
                    'user0' => [
                        'fullname' => 'John Doe',
                    ],
                ],
            ],
            $actual
        );
    }

    public function testParseEmptyFile()
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/empty.yml');

        $this->assertSame([], $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Parser\ParseException
     * @expectedExceptionMessageRegExp /^The file ".+\/basic\.yml" does not contain valid YAML\.$/
     */
    public function testThrowExceptionIfFileNotParsable()
    {
        $file = self::$dir.'/basic.yml';

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::any())->willThrow(SymfonyParseException::class);
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $parser->parse($file);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Parser\ParseException
     * @expectedExceptionMessageRegExp /^Could not parse the file ".+\/basic\.yml"\.$/
     */
    public function testThrowExceptionOnUnexpectedParseException()
    {
        $file = self::$dir.'/basic.yml';

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::any())->willThrow(\Exception::class);
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $parser->parse($file);
    }
}
