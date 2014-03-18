<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\Instantiator;
use Nelmio\Alice\support\extensions\CustomInstantiator;

class InstantiatorTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';

    /**
     * @var Instantiator
     */
    protected $instantiator;

    protected function createInstantiator(array $options = array())
    {
        $defaults = array(
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->instantiator = new Instantiator($options['methods']);
    }

    public function testAddInstantiator()
    {
        $class = self::USER;
        $fixture = new Fixture($class, 'referenced', array(), null);

        $this->createInstantiator();
        $this->instantiator->addInstantiator(new CustomInstantiator);
        $object = $this->instantiator->instantiate($fixture);
        $this->assertTrue($object instanceof $class);
        $this->assertFalse(is_null($object->uuid));
    }
}