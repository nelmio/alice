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

use Nelmio\Alice\Exception\Parser\ParserNotFoundException;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class ParserRegistry implements ParserInterface
{
    use NotClonableTrait;

    /**
     * @var ChainableParserInterface[]
     */
    private $parsers;

    /**
     * @param ChainableParserInterface[] $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = (function (ChainableParserInterface ...$parsers) { return $parsers; })(...$parsers);
    }

    /**
     * Looks for the first suitable parser to parse the file.
     *
     * {@inheritdoc}
     */
    public function parse(string $file): array
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($file)) {
                return $parser->parse($file);
            }
        }

        throw ParserNotFoundException::create($file);
    }
}
