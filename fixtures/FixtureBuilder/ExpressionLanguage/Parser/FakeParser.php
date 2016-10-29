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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeParser implements ParserInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function parse(string $value)
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
