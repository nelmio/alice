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
use Nelmio\Alice\support\extensions\CustomPopulator;

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

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
        $class = self::CONTACT;
        $fixture = new Fixture($class, 'test', array( 'magicProp' => 'magicValue' ), null);
        $object = new $class(new \Nelmio\Alice\support\models\User);

        $this->createPopulator(array( 'objects' => new Collection(array( 'test' => $object )) ));
        $this->populator->addPopulator(new CustomPopulator);
        $this->populator->populate($fixture);
        $this->assertEquals('magicValue set by magic setter', $object->magicProp);
    }
}