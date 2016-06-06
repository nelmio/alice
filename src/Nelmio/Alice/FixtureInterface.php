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

/**
 * A fixture is a value object representing an object to be built.
 */
interface FixtureInterface
{
    /**
     * @return string Unique across a whole fixture set, mainly used to build unique values. By default is
     *                'className#reference'
     */
    public function getId(): string;
    
    /**
     * @return string e.g. 'dummy0'. May contain flags depending of the implementation.
     */
    public function getReference(): string;

    /**
     * @return string FQCN. May contain flags depending of the implementation.
     */
    public function getClassName(): string;

    public function getSpecs(): SpecificationBag;
}
