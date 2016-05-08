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
 * @covers Nelmio\Alice\Parser\Chainable\PhpParser
 */
class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    private static $dir;

    /**
     * @var PhpParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../File/Php';
    }

    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $this->parser = new PhpParser();
    }

    public function test_is_a_chainable_parser()
    {
        $this->assertTrue(is_a(PhpParser::class, ChainableParserInterface::class, true));
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
            'File "/nowhere.php" could not be found.'
        );
        $this->parser->parse('/nowhere.php');
    }

    public function test_parse_regular_file()
    {
        $actual = $this->parser->parse(self::$dir.'/basic.php');

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
        $actual = $this->parser->parse(self::$dir.'/empty.php');

        $this->assertSame([], $actual);
    }

    public function test_throw_exception_if_no_array_returned_in_parsed_file()
    {
        $file = self::$dir.'/no_return.php';
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('The file "%s" must return a PHP array.', $file)
        );

        $this->parser->parse($file);
    }

    public function test_throw_exception_if_wrong_value_returned_in_parsed_file()
    {
        $file = self::$dir.'/wrong_return.php';
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('The file "%s" must return a PHP array.', $file)
        );

        $this->parser->parse($file);
    }

    public function provideFiles()
    {
        return [
            ['dummy.php', true],
            ['dummy.yml.php', true],

            ['https://example.com/dummy.yml', false],

            ['dummy', false],
            ['dummy/', false],
            ['dummy.yml', false],
            ['dummy.yaml', false],
            ['dummy.YML', false],
            ['dummy.YAML', false],
            ['dummy.php.yml', false],
            ['dummy.xml', false],
            ['dummy.csv', false],

        ];
    }
}
