<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\Exception\FixtureBuilder\Parser\ParserNotFoundException;
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
     *
     * @throws ParserNotFoundException When no parser is found.
     */
    public function parse(string $file): array
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($file)) {
                return $parser->parse($file);
            }
        }

        throw new ParserNotFoundException(
            sprintf(
                'No suitable parser found for the file "%s".',
                $file
            )
        );
    }
}
