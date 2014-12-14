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

class YamlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Yaml
     **/
    private $parser;

    public function setUp()
    {
        $this->parser = new Yaml(['value' => 'test']);
    }

    public function testCanParseWillReturnTrueForYamlExtensions()
    {
        $this->assertTrue($this->parser->canParse('test.yaml'));
        $this->assertTrue($this->parser->canParse('test.yml'));
    }

    public function testCanParseWillReturnTrueForYamlExtensionsWithPhpContext()
    {
        $this->assertTrue($this->parser->canParse('test.yaml.php'));
    }

    public function testCanParseWillReturnFalseForNonYamlExtensions()
    {
        $this->assertFalse($this->parser->canParse('test.xml'));
    }

    public function testParseWillExecuteWithASetContext()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/parsers/yamltest.yml.php');

        $this->assertEquals('test', $data['contextual']);
    }

    public function testParseWillReturnAProperDataArray()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/parsers/yamltest.yml.php');

        $this->assertEquals(['contextual' => 'test', 'username' => '<username()>'], $data);
    }

    public function testIncludeFiles()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/include.yml');

        $expectedData = [
            'Nelmio\\Alice\\fixtures\\Product' =>
                [
                    'product_base (template)' =>
                        [
                            'status' => 'in_stock',
                            'site' => '<word()>',
                            'changed' => 'n',
                            'locked' => '<word()>',
                            'cancelled' => '<word()>',
                            'canBuy' => 'y',
                            'package' => 'n',
                            'price' => '<randomFloat()>',
                            'amount' => 1,
                            'markDeleted' => '<word()>',
                            'paid' => 'y',
                        ],
                    'product1' =>
                        [
                            'amount' => 45,
                            'paid' => 'n',
                            'user' => '@user0',
                        ],
                    'product0' =>
                        [
                            'changed' => 'y',
                            'user' => '@user1',
                        ],
                ],
            'Nelmio\\Alice\\fixtures\\Shop' =>
                [
                    'shop2' =>
                        [
                            'domain' => 'amazon.com',
                        ],
                    'shop1' =>
                        [
                            'domain' => '<{ebay_domain_name}>',
                        ],
                ],
            'Nelmio\\Alice\\fixtures\\User' =>
                [
                    'user_base (template)' =>
                        [
                            'email' => '<email()>',
                        ],
                ],
        ];
        $this->assertEquals($expectedData, $data);
    }

    public function testParametersNotReturnedInData()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/include.yml');

        $this->assertFalse(isset($data['parameters']));
    }

    public function testParametersSetOnTheLoader()
    {
        $loader = new Loader;
        $parser = new Yaml($loader);

        $parser->parse(__DIR__.'/../../../support/fixtures/include.yml');

        $this->assertEquals('ebay.us', $loader->getParameterBag()->get('ebay_domain_name'));
    }
}
