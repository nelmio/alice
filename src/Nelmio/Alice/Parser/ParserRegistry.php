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

use Nelmio\Alice\Exception\Parser\RuntimeException;
use Nelmio\Alice\ParserInterface;

final class ParserRegistry implements ParserInterface
{
    /**
     * @var ChainableParserInterface[]
     */
    private $parsers;

    /**
     * @param ChainableParserInterface[] $parsers
     *
     * @throws \InvalidArgumentException When invalid parser is passed.
     */
    public function __construct(array $parsers)
    {
        foreach ($parsers as $parser) {
            if (false === $parser instanceof ChainableParserInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected parsers to be "%s" objects. Parser "%s" is not.',
                        ChainableParserInterface::class,
                        is_object($parser) ? get_class($parser) : $parser
                    )
                );
            }
        }

        $this->parsers = $parsers;
    }

    /**
     * Looks for the first suitable parser to parse the file.
     *
     * {@inheritdoc}
     *
     * @throws RuntimeException When no parser is found.
     */
    public function parse(string $file): array
    {
        foreach ($this->parsers as $parser) {
            if (false === $parser->canParse($file)) {
                continue;
            }

            $data = $parser->parse($file);
            $data['meta'] = [
                'file' => $file
            ];

            return $data;
        }

        throw new RuntimeException(
            sprintf(
                'No suitable parser found for the file "%s".',
                $file
            )
        );
    }
}
