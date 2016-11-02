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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Exception\Generator\Resolver\CircularReferenceException;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;

final class GenerationContext
{
    /**
     * @var bool
     */
    private $isFirstPass;

    /**
     * @var ResolvingContext
     */
    private $resolving;

    /**
     * @var bool
     */
    private $needsCompleteResolution = false;

    public function __construct()
    {
        $this->isFirstPass = true;
        $this->resolving = new ResolvingContext();
    }

    public function isFirstPass(): bool
    {
        return $this->isFirstPass;
    }

    public function setToSecondPass()
    {
        $this->isFirstPass = false;
    }

    /**
     * @param string $id
     *
     * @throws CircularReferenceException
     */
    public function markIsResolvingFixture(string $id)
    {
        $this->resolving->add($id);
        $this->resolving->checkForCircularReference($id);
    }

    public function markAsNeedsCompleteGeneration()
    {
        $this->needsCompleteResolution = true;
    }

    public function unmarkAsNeedsCompleteGeneration()
    {
        $this->needsCompleteResolution = false;
    }

    public function needsCompleteGeneration(): bool
    {
        return $this->needsCompleteResolution;
    }
}
