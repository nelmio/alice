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

use Nelmio\Alice\support\extensions\CustomBuilder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';

    /**
     * @var Builder
     */
    protected $builder;

    protected function createBuilder(array $options = [])
    {
        $defaults = [
            'methods' => []
        ];
        $options = array_merge($defaults, $options);

        return $this->builder = new Builder($options['methods']);
    }

    public function testAddBuilder()
    {
        $this->createBuilder();
        $this->builder->addBuilder(new CustomBuilder);
        $fixtures = $this->builder->build(self::USER, 'spec dumped', [ 'thisShould' => 'be gone' ]);
        $this->assertEmpty($fixtures[0]->getProperties());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage All methods passed into Builder must implement MethodInterface.
     */
    public function testOnlyMethodInterfacesCanBeUsedToInstantiateTheBuilder()
    {
        $builder = new Builder(['CustomBuilder']);
    }
}
