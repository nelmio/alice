<?php

/*
 * This file is part of the Alice package.
 *  
 *  (c) Nelmio <hello@nelm.io>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances;

use Nelmio\Alice\Exception\Instantiator\ExceptionInterface;
use Nelmio\Alice\Fixtures\Fixture;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface InstantiatorInterface
{
    /**
     * Creates and returns an instance of the class described by the given fixture.
     *
     * @param  Fixture $fixture
     *
     * @throws ExceptionInterface
     *
     * @return mixed
     */
    public function instantiate(Fixture $fixture);
}
