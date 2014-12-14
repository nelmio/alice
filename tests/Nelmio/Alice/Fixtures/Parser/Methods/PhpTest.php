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

use UnexpectedValueException;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Php
     **/
    private $parser;

    public function setUp()
    {
        $this->parser = new Php(['value' => 'test']);
    }

    public function testCanParseWillReturnTrueForPhpExtensions()
    {
        $this->assertTrue($this->parser->canParse('test.php'));
    }

    public function testCanParseWillReturnFalseForNonPhpExtensions()
    {
        $this->assertFalse($this->parser->canParse('test.xml'));
    }

    public function testParseWillExecuteWithASetContext()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/parsers/phptest.php');
        $this->assertEquals('test', $data['contextual']);
    }

    public function testParseWillReturnAProperDataArray()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/parsers/phptest.php');
        $this->assertEquals(['contextual' => 'test', 'username' => '<username()>'], $data);
    }

    public function testParseWillThrowIfTheFixtureDoesntReturnAnArray()
    {
        try {
            $this->parser->parse($file = __DIR__.'/../../../support/fixtures/invalid.php');
        } catch (UnexpectedValueException $e) {
            $this->assertEquals("Included file \"{$file}\" must return an array of data", $e->getMessage());
        }
    }

    public function testIncludeFiles()
    {
        $data = $this->parser->parse(__DIR__.'/../../../support/fixtures/include.php');

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
                            'domain' => 'ebay.com',
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
}
