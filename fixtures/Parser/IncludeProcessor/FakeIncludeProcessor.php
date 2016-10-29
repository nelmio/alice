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

namespace Nelmio\Alice\Parser\IncludeProcessor;

use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\Parser\IncludeProcessorInterface;
use Nelmio\Alice\ParserInterface;

final class FakeIncludeProcessor implements IncludeProcessorInterface
{
    use NotCallableTrait;

    public function process(ParserInterface $parser, string $file, array $data): array
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
