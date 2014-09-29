<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser;

use Nelmio\Alice\support\extensions\CustomParser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser
     */
    protected $parser;

    protected function createParser(array $options = array())
    {
        $defaults = array(
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->parser = new Parser($options['methods']);
    }

    public function testAddParser()
    {
        $this->createParser();
        $this->parser->addParser(new CustomParser);
        $data = $this->parser->parse(__DIR__.'/../../support/fixtures/parsers/csvtest.csv');

        $expectedData = array(
            'Nelmio\Alice\support\models\User' => array(
                    'user{1..10}' => array('username' => '<username()>', 'email' => '<current>@test.org'),
                    'user11' => array('username' => 'user11', 'email' => 'user11@test.org')
                )
            );

        $this->assertEquals($expectedData, $data);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage All parsers passed into Parser must implement MethodInterface.
     */
    public function testOnlyMethodInterfacesCanBeUsedToInstantiateTheParser()
    {
        $parser = new Parser(array('CustomParser'));
    }
}
