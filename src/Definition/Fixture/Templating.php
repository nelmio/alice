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

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\FixtureWithFlagsInterface;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

/**
 * Helper to easily manipulate flags related to templates.
 *
 * @private
 */
final class Templating
{
    /**
     * @var bool
     */
    private $isATemplate = false;

    /**
     * @var FixtureReference[]
     */
    private $extends = [];

    public function __construct(FixtureWithFlagsInterface $fixture)
    {
        $flags = $fixture->getFlags();
        foreach ($flags as $flag) {
            if ($flag instanceof TemplateFlag) {
                $this->isATemplate = true;

                continue;
            }

            if ($flag instanceof ExtendFlag) {
                // Potential flag duplication is handled at the flagbag level
                array_unshift($this->extends, $flag->getExtendedFixture());
            }
        }
    }

    public function isATemplate(): bool
    {
        return $this->isATemplate;
    }

    public function extendsFixtures(): bool
    {
        return [] !== $this->extends;
    }

    /**
     * @return FixtureReference[] List of the full references of the extended fixtures.
     */
    public function getExtendedFixtures(): array
    {
        return $this->extends;
    }
}
