<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeParser implements ParserInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function parse(string $value)
    {
        $this->__call();
    }
}
