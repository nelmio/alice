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

use Exception;
use InvalidArgumentException;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Parser\FileListProviderTrait;
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Nelmio\Alice\Parser\Chainable\YamlParser
 */
class YamlParserTest extends TestCase
{
    use ProphecyTrait;
    use FileListProviderTrait;

    private static $dir;

    /**
     * @var YamlParser
     */
    private $parser;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../../../fixtures/Parser/files/yaml';
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::cetera())->shouldNotBeCalled();
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $this->parser = new YamlParser($symfonyYamlParser);
    }

    public function testIsAChainableParser(): void
    {
        static::assertTrue(is_a(YamlParser::class, ChainableParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(YamlParser::class))->isCloneable());
    }

    /**
     * @dataProvider providePhpList
     */
    public function testCannotParsePhpFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    /**
     * @dataProvider provideYamlList
     */
    public function testCanParseYamlFiles(string $file, array $expectedParsers): void
    {
        $actual = $this->parser->canParse($file);
        $expected = (in_array(get_class($this->parser), $expectedParsers));

        static::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideJsonList
     */
    public function testCannotParseJsonFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    /**
     * @dataProvider provideUnsupportedList
     */
    public function testCannotParseUnsupportedFiles(string $file): void
    {
        $actual = $this->parser->canParse($file);

        static::assertFalse($actual);
    }

    public function testThrowExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "/nowhere.yml" could not be found.');

        $this->parser->parse('/nowhere.yml');
    }

    public function testUseSymfonyParserToParseFile(): void
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

        $expected = [new stdClass()];

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

        static::assertSame($expected, $actual);

        $symfonyYamlParserProphecy->parse(Argument::cetera())->shouldBeCalledTimes(1);
    }

    public function testReturnsParsedFileContent(): void
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/basic.yml');

        static::assertSame(
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

    public function testParseReturnsInterpretedConstants(): void
    {
        if (!defined('Symfony\\Component\\Yaml\\Yaml::PARSE_CONSTANT')) {
            static::markTestSkipped('This test needs symfony/yaml v3.2 or higher.');
        }

        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/constants.yml');

        static::assertSame(
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

    public function testParsingEmptyFileResultsInEmptySet(): void
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/empty.yml');

        static::assertSame([], $actual);
    }

    public function testParseReturnsNamedParameters(): void
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/named_parameters.yml');

        static::assertSame(
            [
                'Nelmio\Alice\DummyWithMethods' => [
                    'dummy_with_methods' => [
                        '__construct' => [
                            '$foo1' => 'foo1',
                            '$foo2' => 'foo2',
                        ],
                        '__calls' => [
                            [
                                'bar' => [
                                    '$bar1' => 'bar1',
                                    '$bar2' => 'bar2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $actual
        );
    }

    public function testThrowsAnExceptionIfFileNotParsable(): void
    {
        try {
            $file = self::$dir.'/basic.yml';

            $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
            $symfonyYamlParserProphecy->parse(Argument::cetera())->willThrow(SymfonyParseException::class);
            /* @var SymfonyYamlParser $symfonyYamlParser */
            $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

            $parser = new YamlParser($symfonyYamlParser);
            $parser->parse($file);

            static::fail('Expected exception to be thrown.');
        } catch (UnparsableFileException $exception) {
            $this->assertMatchesRegularExpression('/^The file ".+\/basic\.yml" does not contain valid YAML\.$/', $exception->getMessage());
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testThrowsAnExceptionOnUnexpectedParseException(): void
    {
        try {
            $file = self::$dir.'/basic.yml';

            $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
            $symfonyYamlParserProphecy->parse(Argument::cetera())->willThrow(Exception::class);
            /* @var SymfonyYamlParser $symfonyYamlParser */
            $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

            $parser = new YamlParser($symfonyYamlParser);
            $parser->parse($file);

            static::fail('Expected exception to be thrown.');
        } catch (UnparsableFileException $exception) {
            $this->assertMatchesRegularExpression('/^Could not parse the file ".+\/basic\.yml"\.$/', $exception->getMessage());
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }
}
