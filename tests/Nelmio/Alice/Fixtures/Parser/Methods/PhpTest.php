<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser\Methods;

use Nelmio\Alice\Fixtures\Parser\Methods\Php as PhpParser;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    private static $dir;

    /**
     * @var PhpParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../Files/Php';
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

    public function test_is_a_parser_method()
    {
        $this->assertTrue(
            is_a(
                'Nelmio\Alice\Fixtures\Parser\Methods\Php',
                'Nelmio\Alice\Fixtures\Parser\Methods\MethodInterface',
                true
            )
        );
    }

    /**
     * @dataProvider provideFiles
     */
    public function test_can_parse_php_files($file, $expected)
    {
        $actual = $this->parser->canParse($file);

        $this->assertEquals($expected, $actual);
    }

    public function test_parse_returns_a_php_array()
    {
        $data = $this->parser->parse(self::$dir.'/regular_file.php');

        $this->assertSame(
            [
                'username' => '<username()>',
            ],
            $data
        );
    }

    public function test_can_parse_a_context_to_parsed_files()
    {
        $parser = new PhpParser(['value' => 'test']);
        $data = $parser->parse(self::$dir.'/contextual_file.php');

        $this->assertSame(
            [
                'contextual' => 'test',
                'username' => '<username()>',
            ],
            $data
        );
    }

    public function test_throw_exception_if_file_doesnt_return_array()
    {
        $file = self::$dir.'/no_return.php';
        try {
            $this->parser->parse($file);
            $this->fail(sprintf('Expected parsing file "%s" to throw an exception', $file));
        } catch (\UnexpectedValueException $exception) {
            $this->assertEquals(
                sprintf('Included file "%s" must return an array of data', $file),
                $exception->getMessage()
            );
        }

        $file = self::$dir.'/wrong_return.php';
        try {
            $this->parser->parse($file);

            $this->fail(sprintf('Expected parsing file "%s" to throw an exception', $file));
        } catch (\UnexpectedValueException $exception) {
            $this->assertEquals(
                sprintf('Included file "%s" must return an array of data', $file),
                $exception->getMessage()
            );
        }
    }

    public function test_include_files()
    {
        $data = $this->parser->parse(self::$dir.'/include/main.php');

        $this->assertSame(
            [
                'Nelmio\Alice\Entity\Product' => [
                    'product_base (template)' => [
                        'status' => 'in_stock',
                    ],
                    'product1 (extends product_base)' => [
                        'amount' => 45,
                    ]
                ],
                'Nelmio\Alice\Entity\Shop' => [
                    'shop' => [
                        'status' => 'none',
                    ],
                ],
            ],
            $data
        );
    }

    public function test_included_files_are_parsed_before_parsed_file()
    {
        $data = $this->parser->parse(self::$dir.'/include_order/main.php');

        $this->assertSame(
            [
                'Bar' => [
                    'bar' => [
                        'id' => 100,
                        'text' => '<word()>',
                    ],
                ],
                'Foo' => [
                    'foo' => [
                        'id' => 200,
                        'text' => '<word()>',
                    ],
                ],
                'Main' => [
                    'main' => [
                        'id' => 300,
                        'text' => '<word()>',
                    ],
                ],
            ],
            $data
        );
    }

    public function test_last_fixture_declared_is_kept()
    {
        $data = $this->parser->parse(self::$dir.'/include_overlap/main.php');

        $this->assertSame(
            [
                'Nelmio\Alice\Entity\Product' => [
                    'product0' => [
                        'value' => 'second',
                    ],
                ],
            ],
            $data
        );
    }

    public function provideFiles()
    {
        return [
            'php file' => [
                'test.php',
                true,
            ],
            'relative php file' => [
                './../test.php',
                true,
            ],
            'absolute file file' => [
                __FILE__,
                true,
            ],

            'xml file' => [
                'test.xml',
                false,
            ],
            'YAML file' => [
                'test.yml',
                false,
            ],
            'YAML with another extension' => [
                'test.yaml',
                false,
            ],
        ];
    }
}
