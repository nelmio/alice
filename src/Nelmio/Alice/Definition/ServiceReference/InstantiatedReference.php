<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;

/**
 * Value object to point to refer to a service. For example, 
 */
final class InstantiatedReference implements ServiceReferenceInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $reference 
     */
    public function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    public function getReference(): string
    {
        return $this->reference;
    }
}
