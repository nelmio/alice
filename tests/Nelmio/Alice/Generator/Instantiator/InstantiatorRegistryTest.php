<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Loader\NativeLoader;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry
 */
class InstantiatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    public function setUp()
    {
        $this->instantiator = (new NativeLoader())->getBuiltInInstantiatorRegistry();
    }

    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(InstantiatorRegistry::class, InstantiatorInterface::class, true));
    }

    //TODO: unit tests


}
