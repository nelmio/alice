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

namespace Nelmio\Alice\Faker;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

/**
 * Factory for faker generators to easily create a new generator instance with the same providers as the original one.
 * The factory implements the singleton pattern as it is not meant to be used with a Dependency Injection container as
 * new instances are created with a locale value which is determined at runtime.
 */
final class GeneratorFactory
{
    /**
     * @var FakerGenerator[]
     */
    private $generators = [];

    /**
     * @var FakerGenerator
     */
    private $generator;

    public function __construct(FakerGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string $locale e.g. 'fr_FR', 'en_US'
     */
    public function createOrReturnExistingInstance(string $locale): FakerGenerator
    {
        if (array_key_exists($locale, $this->generators)) {
            return $this->generators[$locale];
        }

        $instance = FakerFactory::create($locale);
        $generatorProviders = $this->generator->getProviders();
        foreach ($generatorProviders as $provider) {
            if (1 !== preg_match('/^Faker\\\Provider/u', get_class($provider))) {
                $instance->addProvider($provider);
            }
        }

        return $this->generators[$locale] = $instance;
    }

    public function getSeedGenerator(): FakerGenerator
    {
        return $this->generator;
    }
}
