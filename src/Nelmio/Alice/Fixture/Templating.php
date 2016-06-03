<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

use Nelmio\Alice\Fixture\Flag\ExtendFlag;
use Nelmio\Alice\Fixture\Flag\TemplateFlag;

/**
 * @internal
 */
final class Templating
{
    /**
     * @var bool
     */
    private $isAtemplate = false;

    /**
     * @var string[]
     */
    private $extends = [];

    public function __construct(string $className, FlagBag $flags)
    {
        foreach ($flags as $flag) {
            if ($flag instanceof TemplateFlag) {
                $this->isAtemplate = true;

                continue;
            }

            if ($flag instanceof ExtendFlag) {
                $this->extends[] = $className.$flag->getExtendedFixture();
            }
        }
    }

    public function isATemplate(): bool
    {
        return null !== $this->isAtemplate;
    }

    public function extendsFixtures(): bool
    {
        return [] != $this->extends;
    }

    /**
     * @return string[] List of the full references of the extended fixtures.
     */
    public function getExtendedFixtures(): array
    {
        return $this->extends;
    }

    public function __clone()
    {
        throw new \DomainException('Is not clonable.');
    }
}
