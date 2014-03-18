<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\FixtureBuilder;

use Nelmio\Alice\Instances\FixtureBuilder\FixtureBuilder;
use Nelmio\Alice\support\extensions\CustomBuilder;

class FixtureBuilderTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';

    /**
     * @var FixtureBuilder
     */
    protected $builder;

    protected function createBuilder(array $options = array())
    {
        $defaults = array(
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->builder = new FixtureBuilder($options['methods']);
    }

    public function testAddFixtureBuilder()
    {
        $this->createBuilder();
        $this->builder->addFixtureBuilder(new CustomBuilder);
        $fixtures = $this->builder->build(self::USER, 'spec dumped', array( 'thisShould' => 'be gone' ));
        $this->assertTrue($fixtures[0]->getProperties()->isEmpty());
    }
}
