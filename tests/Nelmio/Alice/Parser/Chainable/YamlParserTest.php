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

use Nelmio\Alice\Exception\Parser\InvalidArgumentException;
use Nelmio\Alice\Exception\Parser\ParseException;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Prophecy\Argument;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * @covers Nelmio\Alice\Parser\Chainable\YamlParser
 */
class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    private static $dir;

    /**
     * @var YamlParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../File/Yaml';
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

    public function test_is_a_chainable_parser()
    {
        $this->assertTrue(is_a(YamlParser::class, ChainableParserInterface::class, true));
    }

    /**
     * @dataProvider provideFiles
     */
    public function test_can_parse_yaml_files(string $file, bool $expected)
    {
        $actual = $this->parser->canParse($file);

        $this->assertEquals($expected, $actual);
    }

    public function test_throw_exception_if_file_does_not_exist()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'File "/nowhere.yml" could not be found.'
        );
        $this->parser->parse('/nowhere.yml');
    }

    public function test_use_symfony_parser_to_parse_file()
    {
        $file = self::$dir.'/basic.yml';
        $fileContent = <<<'EOF'
Nelmio\Alice\support\models\User:
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

    public function test_parse_regular_file()
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/basic.yml');

        $this->assertSame(
            [
                'Nelmio\Alice\support\models\User' => [
                    'user0' => [
                        'fullname' => 'John Doe',
                    ],
                ],
            ],
            $actual
        );
    }

    public function test_parse_empty_file()
    {
        $symfonyParser = new SymfonyYamlParser();

        $parser = new YamlParser($symfonyParser);
        $actual = $parser->parse(self::$dir.'/empty.yml');

        $this->assertSame([], $actual);
    }

    public function test_throw_exception_if_file_not_parsable()
    {
        $file = self::$dir.'/basic.yml';
        $this->setExpectedException(
            ParseException::class,
            'The file "/Users/Theo/Sites/GitHub/Alice/alice/tests/Nelmio/Alice/Parser/Chainable/../File/Yaml/basic.yml"'
            .' does not contain valid YAML.'
        );

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::any())->willThrow(SymfonyParseException::class);
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $parser->parse($file);
    }

    public function test_throw_exception_on_unexpected_parse_error()
    {
        $file = self::$dir.'/basic.yml';
        $this->setExpectedException(
            ParseException::class,
            'Could not parse the file "/Users/Theo/Sites/GitHub/Alice/alice/tests/Nelmio/Alice/Parser/Chainable/../File/Yaml/basic.yml".'
        );

        $symfonyYamlParserProphecy = $this->prophesize(SymfonyYamlParser::class);
        $symfonyYamlParserProphecy->parse(Argument::any())->willThrow(\Error::class);
        /* @var SymfonyYamlParser $symfonyYamlParser */
        $symfonyYamlParser = $symfonyYamlParserProphecy->reveal();

        $parser = new YamlParser($symfonyYamlParser);
        $parser->parse($file);
    }

    public function provideFiles()
    {
        return [
            ['dummy.yml', true],
            ['dummy.yaml', true],
            ['dummy.YML', true],
            ['dummy.YAML', true],
            ['dummy.php.yml', true],

            ['https://example.com/dummy.yml', false],

            ['dummy', false],
            ['dummy/', false],
            ['dummy.php', false],
            ['dummy.xml', false],
            ['dummy.csv', false],
            ['dummy.yml.php', false],
        ];
    }
}
