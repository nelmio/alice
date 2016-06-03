<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Fixture\SpecificationBag;

/**
 * Represents a fixture object which has not been resolved yet.
 */
interface UnresolvedFixtureInterface
{
    /**
     * @return string e.g. 'dummy0'
     */
    public function getReference(): string;

    /**
     * @return string FQCN
     */
    public function getClassName(): string;

    public function getSpecs(): SpecificationBag;
}
