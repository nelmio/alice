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
        $this->parser = new Php(array('value' => 'test'));
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
        $this->assertEquals(array('contextual' => 'test', 'username' => '<username()>'), $data);
    }

    public function testParseWillThrowIfTheFixtureDoesntReturnAnArray()
    {
        try {
            $this->parser->parse($file = __DIR__.'/../../../support/fixtures/invalid.php');
        } catch (UnexpectedValueException $e) {
            $this->assertEquals("Included file \"{$file}\" must return an array of data", $e->getMessage());
        }
    }
}
