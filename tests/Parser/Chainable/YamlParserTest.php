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

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Nelmio\Alice\Parser\Chainable\YamlParser
 */
class YamlParserTest extends TestCase
{
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var YamlParser
     */
    private $parser;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/yaml';
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    /**
     * @inheritdoc
     */
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

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(YamlParser::class))->isCloneable());
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
     * @dataProvider provideJsonList
     */
    public function testCannotParseJsonFiles(string $file)
    {
        $actual = $this->parser->canParse($file);

        $this->assertFalse($actual);
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
     * @expectedExceptionMessage The file "/nowhere.yml" could not be found.
     */
    public function testThrowExceptionIfFileDoesNotExist()
    {
        $this->parser->parse('/nowhere.yml');
    }

    public function testUseSymfonyParserToParseFile()
    {
        $file = self::$dir.'/basic.yml';
        $fileContent = <<<'EOF'
#
# This file is part of the Alice package.
#
# (c) Nelmio <hello@nelm.io>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

Nelmio\Alice\Model\User:
    user0:
        fullname: John Doe

EOF;

        $expected = [new \stdClass()];

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        if (defined('Symfony\\Component\\Yaml\\Yaml::PARSE_CONSTANT')) {
            $symfonyYamlParserProphecy->parse($fileContent, Yaml::PARSE_CONSTANT)->willReturn($expected);
        } else {
            $symfonyYamlParserProphecy->parse($fileContent)->willReturn($expected);
        }

        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $actual = $parser->parse($file);

        $this->assertSame($expected, $actual);

        $symfonyYamlParserProphecy->parse(Argument::cetera())->shouldBeCalledTimes(1);
    }

    public function testReturnsParsedFileContent()
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

    public function testParseReturnsInterpretedConstants()
    {
        if (!defined('Symfony\\Component\\Yaml\\Yaml::PARSE_CONSTANT')) {
            $this->markTestSkipped('This test needs symfony/yaml v3.2 or higher.');
        }

        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/constants.yml');

        $this->assertSame(
            [
                'Nelmio\Alice\Model\User' => [
                    'user0' => [
                        'max_int' => PHP_INT_MAX,
                    ],
                ],
            ],
            $actual
        );
    }

    public function testParsingEmptyFileResultsInEmptySet()
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/empty.yml');

        $this->assertSame([], $actual);
    }

    public function testThrowsAnExceptionIfFileNotParsable()
    {
        try {
            $file = self::$dir.'/basic.yml';

            $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
            $symfonyYamlParserProphecy->parse(Argument::cetera())->willThrow(SymfonyParseException::class);
            /* @var SymfonyYamlParser $symfonyYamlParser */
            $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

            $parser = new YamlParser($symfonyYamlParser);
            $parser->parse($file);

            $this->fail('Expected exception to be thrown.');
        } catch (UnparsableFileException $exception) {
            $this->assertRegExp('/^The file ".+\/basic\.yml" does not contain valid YAML\.$/', $exception->getMessage());
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    public function testThrowsAnExceptionOnUnexpectedParseException()
    {
        try {
            $file = self::$dir.'/basic.yml';

            $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
            $symfonyYamlParserProphecy->parse(Argument::any())->willThrow(\Exception::class);
            /* @var SymfonyYamlParser $symfonyYamlParser */
            $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

            $parser = new YamlParser($symfonyYamlParser);
            $parser->parse($file);

            $this->fail('Expected exception to be thrown.');
        } catch (UnparsableFileException $exception) {
            $this->assertRegExp('/^Could not parse the file ".+\/basic\.yml"\.$/', $exception->getMessage());
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }
}
