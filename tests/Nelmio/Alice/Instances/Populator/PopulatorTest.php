<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\Populator\Populator;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\TestExtensions\CustomPopulator;

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    const MAGIC_OBJECT = 'Nelmio\Alice\Instances\Populator\MagicMethodPopulated';

    /**
     * @var Populator
     */
    protected $populator;

    protected function createPopulator(array $options = array())
    {
        $defaults = array(
            'objects' => new Collection,
            'processor' => new Processor(array()),
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->populator = new Populator($options['objects'], $options['processor'], $options['methods']);
    }

    public function testAddPopulator()
    {
        $class = self::MAGIC_OBJECT;
        $fixture = new Fixture($class, 'test', array( 'magicProp' => 'magicValue' ), null);
        $object = new $class;

        $this->createPopulator(array( 'objects' => new Collection(array( 'test' => $object )) ));
        $this->populator->addPopulator(new CustomPopulator);
        $this->populator->populate($fixture);
        $this->assertEquals('magicValue', $object->magicProp);
    }
}

class MagicMethodPopulated
{   
    protected $properties = array();

    public function __get($property)
    {
        return $this->properties[$property];
    }

    public function __set($property, $value)
    {
        $this->properties[$property] = $value;
    }

}