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

use Nelmio\Alice\Fixtures\ParameterBag;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\support\extensions\CustomProcessor;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $objects;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage All methods passed into Processor must implement MethodInterface.
     */
    public function testOnlyMethodInterfacesCanBeUsedToInstantiateTheProcessor()
    {
        new Processor(new Collection, ['CustomProcessor'], new ParameterBag());
    }

    public function testAddProcessor()
    {
        $processable = new Processable('uppercase processor:test my custom processor');

        $this->createProcessor();
        $this->processor->addProcessor(new CustomProcessor);
        $result = $this->processor->process($processable, []);
        $this->assertEquals('TEST MY CUSTOM PROCESSOR', $result);
    }

    public function testAddProcessorWillSetObjectsIfSetterExists()
    {
        $this->createProcessor();
        $this->processor->addProcessor($custom = new CustomProcessor);
        $this->assertEquals($this->objects, $custom->objects);
    }

    public function testAddProcessorWillSetTheProcessorIfSetterExists()
    {
        $this->createProcessor();
        $this->processor->addProcessor($custom = new CustomProcessor);
        $this->assertEquals($this->processor, $custom->processor);
    }

    protected function createProcessor(array $options = [])
    {
        $defaults = [
            'objects' => new Collection,
            'methods' => []
        ];
        $options = array_merge($defaults, $options);

        return $this->processor = new Processor($this->objects = $options['objects'], $options['methods'], new ParameterBag());
    }
}
