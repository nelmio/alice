<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor;

use Nelmio\Alice\Instances\Processor\Processable;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\support\extensions\CustomProcessor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    protected function createProcessor(array $options = array())
    {
        $defaults = array(
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->processor = new Processor($options['methods']);
    }

    public function testAddProcessor()
    {
        $processable = new Processable('uppercase processor:test my custom processor');
        
        $this->createProcessor();
        $this->processor->addProcessor(new CustomProcessor);
        $result = $this->processor->process($processable, array());
        $this->assertEquals('TEST MY CUSTOM PROCESSOR', $result);
    }
}