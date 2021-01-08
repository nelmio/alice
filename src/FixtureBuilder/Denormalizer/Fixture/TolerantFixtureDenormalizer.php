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

use Error;
use LogicException;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use RuntimeException;
use Throwable;

final class TolerantFixtureDenormalizer implements FixtureDenormalizerInterface
{
    use IsAServiceTrait;
    
    /**
     * @var FixtureDenormalizerInterface
     */
    private $denormalizer;
    
    public function __construct(FixtureDenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }
    
    public function denormalize(FixtureBag $builtFixtures, string $className, string $fixtureId, array $specs, FlagBag $flags): FixtureBag
    {
        try {
            return $this->denormalizer->denormalize(...func_get_args());
        } catch (RuntimeException $throwable) {
            $throwableClass = UnexpectedValueException::class;
        } catch (LogicException $throwable) {
            $throwableClass = LogicException::class;
        } catch (Throwable $throwable) {
            $throwableClass = Error::class;
        }

        $arguments = [
            sprintf(
                'An error occurred while denormalizing the fixture "%s" (%s): %s',
                $fixtureId,
                $className,
                $throwable->getMessage()
            ),
            $throwable->getCode(),
            $throwable
        ];

        throw new $throwableClass(...$arguments);
    }
}
