<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Flag;

final class ExtendsFlag
{
    /**
     * @var string[]
     */
    private $references;

    /**
     * @param string[] $references
     */
    public function __construct(array $references)
    {
        $this->references = $references;
    }

    /**
     * @return string[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }
}
