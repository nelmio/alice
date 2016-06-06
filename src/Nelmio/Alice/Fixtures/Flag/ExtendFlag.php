<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Flag;

final class ExtendFlag implements FlagInterface
{
    /**
     * @var string
     */
    private $templateName;

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }
}
