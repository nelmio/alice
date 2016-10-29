<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;

/**
 * Value object representing a reference to an existing fixture.
 */
final class FixtureReference implements ServiceReferenceInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $fixtureId e.g. 'user0'
     */
    public function __construct(string $fixtureId)
    {
        $this->id = $fixtureId;
    }

    /**
     * {@inheritdoc}
     *
     * @return string fixture ID e.g. 'user0'
     */
    public function getId(): string
    {
        return $this->id;
    }
}
