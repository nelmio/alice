<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

use Nelmio\Alice\Fixture\Flag\ExtendFlag;
use Nelmio\Alice\Fixture\Flag\TemplateFlag;
use Nelmio\Alice\Fixture\FlagBag;

final class FlagParser implements FlagParserInterface
{
    /**
     * @var RawFlagParser
     */
    private $rawFlagParser;

    public function __construct()
    {
        $this->rawFlagParser = new RawFlagParser();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function parse(string $element): FlagBag
    {
        $rawFlags = $this->rawFlagParser->parse($element);
        $flags = new FlagBag($rawFlags[0]);

        foreach ($rawFlags[1] as $rawFlag) {
            if ('extends ' === substr($rawFlag, 0, 8)) {
                $flag = new ExtendFlag(substr($rawFlag, 8));
                $flags = $flags->with($flag);

                continue;
            }

            if ('template' === $rawFlag) {
                $flag = new TemplateFlag();
                $flags = $flags->with($flag);

                continue;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Unable to parse the flag "%s".',
                    $rawFlag
                )
            );
        }
        
        return $flags;
    }
}
