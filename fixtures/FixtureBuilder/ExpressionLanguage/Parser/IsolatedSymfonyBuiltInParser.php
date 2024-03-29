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
use Nelmio\Alice\Symfony\KernelIsolatedServiceCall;

class IsolatedSymfonyBuiltInParser implements ParserInterface
{
    public function parse(string $value)
    {
        return KernelIsolatedServiceCall::call(
            'nelmio_alice.fixture_builder.expression_language.parser',
            static fn (ParserInterface $parser) => $parser->parse($value),
        );
    }
}
