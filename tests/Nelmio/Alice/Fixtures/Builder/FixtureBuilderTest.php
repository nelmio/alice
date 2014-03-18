<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\Builder\Builder;
use Nelmio\Alice\support\extensions\CustomBuilder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';

    /**
     * @var Builder
     */
    protected $builder;

    protected function createBuilder(array $options = array())
    {
        $defaults = array(
            'methods' => array()
        );
        $options = array_merge($defaults, $options);

        return $this->builder = new Builder($options['methods']);
    }

    public function testAddBuilder()
    {
        $this->createBuilder();
        $this->builder->addBuilder(new CustomBuilder);
        $fixtures = $this->builder->build(self::USER, 'spec dumped', array( 'thisShould' => 'be gone' ));
        $this->assertTrue($fixtures[0]->getProperties()->isEmpty());
    }
}
