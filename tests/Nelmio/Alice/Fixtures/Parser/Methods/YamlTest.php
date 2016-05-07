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
use Nelmio\Alice\Fixtures\Parser\Methods\Yaml as YamlParser;
use Prophecy\Argument;

class YamlTest extends \PHPUnit_Framework_TestCase
{
    private static $dir;

    /**
     * @var YamlParser
     */
    private $parser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$dir = __DIR__.'/../Files/Yaml';
    }

    public static function tearDownAfterClass()
    {
        self::$dir = null;

        parent::tearDownAfterClass();
    }


    public function setUp()
    {
        $this->parser = new YamlParser();
    }

    public function test_is_a_parser_method()
    {
        $this->assertTrue(
            is_a(
                'Nelmio\Alice\Fixtures\Parser\Methods\Yaml',
                'Nelmio\Alice\Fixtures\Parser\Methods\MethodInterface',
                true
            )
        );
    }

    /**
     * @dataProvider provideFiles
     */
    public function test_can_parse_yaml_files($file, $expected)
    {
        $actual = $this->parser->canParse($file);

        $this->assertEquals($expected, $actual);
    }

    public function test_parse_returns_a_yaml_array()
    {
        $data = $this->parser->parse(self::$dir.'/regular_file.yml');

        $this->assertSame(
            [
                'username' => '<username()>',
            ],
            $data
        );
    }

    public function test_can_parse_a_context_to_parsed_files()
    {
        $parser = new YamlParser(['value' => 'test']);
        $data = $parser->parse(self::$dir.'/contextual_file.yml.php');

        $this->assertSame(
            [
                'contextual' => 'test',
                'username' => '<username()>',
            ],
            $data
        );
    }

    public function test_include_files()
    {
        $data = $this->parser->parse(self::$dir.'/include/main.yml');

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
        $data = $this->parser->parse(self::$dir.'/include_order/main.yml');

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
        $data = $this->parser->parse(self::$dir.'/include_overlap/main.yml');

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

    public function test_dont_return_parameters_when_no_parameter_is_declared()
    {
        $data = $this->parser->parse(self::$dir.'/regular_file.yml');

        $this->assertFalse(isset($data['parameters']));
    }

    public function test_load_parameters()
    {
        $parameterBagProphecy = $this->prophesize('Nelmio\Alice\Fixtures\ParameterBag');
        $parameterBagProphecy->set('foo', 'bar')->shouldBeCalled();

        $loaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $loaderProphecy->getFakerProcessorMethod()->shouldBeCalled();
        $loaderProphecy->getParameterBag()->willReturn($parameterBagProphecy->reveal());
        /* @var Loader $loader */
        $loader = $loaderProphecy->reveal();

        $parser = new YamlParser($loader);
        $parser->parse(self::$dir.'/file_with_parameters.yml');

        $loaderProphecy->getParameterBag()->shouldHaveBeenCalledTimes(1);
        $parameterBagProphecy->set(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function test_load_parameters_of_included_files()
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

        $parser = new YamlParser($loader);
        $parser->parse(self::$dir.'/include_parameters/main1.yml');

        $this->assertEquals('bar', $actual['foo']);

        $loaderProphecy->getParameterBag()->shouldHaveBeenCalledTimes(2);
        $parameterBagProphecy->set(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    public function provideFiles()
    {
        return [
            'YAML file' => [
                'test.yml',
                true,
            ],
            'YAML with another extension' => [
                'test.yaml',
                true,
            ],
            'relative YAML file' => [
                './../test.yml',
                true,
            ],

            'php file' => [
                'test.php',
                false,
            ],
            'xml file' => [
                'test.xml',
                false,
            ],
        ];
    }
}
