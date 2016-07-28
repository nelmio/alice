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
 * Value object to point to refer to a fixture.
 */
final class FixtureReference implements ServiceReferenceInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $fixtureId
     */
    public function __construct(string $fixtureId)
    {
        $this->id = $fixtureId;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
