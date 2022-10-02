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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use InvalidArgumentException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FixtureBagDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;

final class SimpleFixtureBagDenormalizer implements FixtureBagDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var FixtureDenormalizerInterface
     */
    private $fixtureDenormalizer;

    /**
     * @var FlagParserInterface
     */
    private $flagParser;

    public function __construct(FixtureDenormalizerInterface $fixtureDenormalizer, FlagParserInterface $flagParser)
    {
        $this->fixtureDenormalizer = $fixtureDenormalizer;
        $this->flagParser = $flagParser;
    }

    /**
     * @param array $data subset of PHP data coming from the parser (does not contains any parameters)
     *
     * @example
     *  $data = [
     *      'Nelmio\Alice\Entity\User' => [
     *          'user0' => [
     *              'username' => 'bob',
     *          ],
     *      ],
     *  ];
     */
    public function denormalize(array $data): FixtureBag
    {
        $fixtures = new FixtureBag();

        $alreadyRetried = [];
        while ($result = array_splice($data, 0, 1)) {
            $fqcnWithFlags = key($result);
            $rawFixtureSet = current($result);

            $flags = $this->flagParser->parse($fqcnWithFlags);
            $fqcn = $flags->getKey();

            if (false === is_array($rawFixtureSet)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected an array for the class "%s", found "%s" instead.',
                        $fqcnWithFlags,
                        gettype($rawFixtureSet)
                    )
                );
            }

            try {
                foreach ($rawFixtureSet as $reference => $specs) {
                    $fixtures = $this->fixtureDenormalizer->denormalize(
                        $fixtures,
                        $fqcn,
                        $reference,
                        $specs ?? [],
                        $flags
                    );
                }
            } catch (UnexpectedValueException $exception) {
                if (!isset($alreadyRetried[$fqcnWithFlags])) {
                    $data[$fqcnWithFlags] = $rawFixtureSet;
                    $alreadyRetried[$fqcnWithFlags] = true;
                } else {
                    throw $exception;
                }
            }
        }

        return $fixtures;
    }
}
