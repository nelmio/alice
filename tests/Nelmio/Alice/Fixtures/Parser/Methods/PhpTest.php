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

use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Fixtures\Parser\Methods\Php as PhpParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PhpTest extends TestCase
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

    public function testIsAParserMethod()
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
    public function testCanParsePhpFiles($file, $expected)
    {
        $actual = $this->parser->canParse($file);

        $this->assertEquals($expected, $actual);
    }

    public function testParseReturnsAPhpArray()
    {
        $data = $this->parser->parse(self::$dir.'/regular_file.php');

        $this->assertSame(
            [
                'username' => '<username()>',
            ],
            $data
        );
    }

    /**
     * @group legacy
     */
    public function testCanParseAContextToParsedFiles()
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

    public function testThrowExceptionIfFileDoesntReturnArray()
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

    public function testIncludeFiles()
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

    public function testIncludedFilesAreParsedBeforeParsedFile()
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

    public function testLastFixtureDeclaredIsKept()
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

    public function testLoadParameters()
    {
        $parameterBagProphecy = $this->prophesize('Nelmio\Alice\Fixtures\ParameterBag');
        $parameterBagProphecy->set('foo', 'bar')->shouldBeCalled();

        $loaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $loaderProphecy->getFakerProcessorMethod()->shouldBeCalled();
        $loaderProphecy->getParameterBag()->willReturn($parameterBagProphecy->reveal());
        /* @var Loader $loader */
        $loader = $loaderProphecy->reveal();

        $parser = new PhpParser($loader);
        $parser->parse(self::$dir.'/file_with_parameters.php');

        $loaderProphecy->getParameterBag()->shouldHaveBeenCalledTimes(1);
        $parameterBagProphecy->set(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testLoadParametersOfIncludedFiles()
    {
        $parameterBagProphecy = $this->prophesize('Nelmio\Alice\Fixtures\ParameterBag');

        $actual = ['foo' => null];
        $parameterBagProphecy
            ->set('foo', 'boo')
            ->will(function($args) use (&$actual) {
                $actual['foo'] = $args[1];
            })
        ;
        $parameterBagProphecy->set('ping', 'pong')->shouldBeCalled();
        $parameterBagProphecy
            ->set('foo', 'bar')
            ->will(function($args) use (&$actual) {
                $actual['foo'] = $args[1];
            })
        ;

        $loaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $loaderProphecy->getFakerProcessorMethod()->shouldBeCalled();
        $loaderProphecy->getParameterBag()->willReturn($parameterBagProphecy->reveal());
        /* @var Loader $loader */
        $loader = $loaderProphecy->reveal();

        $parser = new PhpParser($loader);
        $parser->parse(self::$dir.'/include_parameters/main1.php');

        $this->assertEquals('bar', $actual['foo']);

        $loaderProphecy->getParameterBag()->shouldHaveBeenCalledTimes(2);
        $parameterBagProphecy->set(Argument::cetera())->shouldHaveBeenCalledTimes(3);
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
