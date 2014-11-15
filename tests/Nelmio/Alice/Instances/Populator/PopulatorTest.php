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
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\support\extensions\CustomPopulator;

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

    /**
    * @var Collection
    */
    protected $objects;

    /**
     * @var Populator
     */
    protected $populator;

    protected function createPopulator(array $options = array())
    {
        $objects = isset($options['objects']) ? $options['objects'] : new Collection;
        $defaults = array(
            'objects' => $objects,
            'processor' => new Processor($objects, array()),
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->populator = new Populator($options['objects'], $options['processor'], $options['methods']);
    }

    public function testAddPopulator()
    {
        $class = self::CONTACT;
        $fixture = new Fixture($class, 'test', array( 'magicProp' => 'magicValue' ), null);
        $object = new $class(new \Nelmio\Alice\support\models\User);

        $this->createPopulator(array( 'objects' => new Collection(array( 'test' => $object )) ));
        $this->populator->addPopulator(new CustomPopulator);
        $this->populator->populate($fixture);
        $this->assertEquals('magicValue set by magic setter', $object->magicProp);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage All setters passed into Populator must implement MethodInterface.
     */
    public function testOnlyMethodInterfacesCanBeUsedToInstantiateThePopulator()
    {
        $populator = $this->createPopulator(array('methods' => array('CustomPopulator')));
    }
}
