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

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ParserInterface;

class FakeParser implements ParserInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function parse(string $file): array
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
