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
use Nelmio\Alice\Faker\Provider\DummyProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Faker\GeneratorFactory
 * @group integration
 */
class GeneratorFactoryTest extends TestCase
{
    public function testAssertGeneratorLocaleMethod()
    {
        $this->assertGeneratorLocaleIs('en_US', FakerFactory::create());
        try {
            $this->assertGeneratorLocaleIs('fr_FR', FakerFactory::create());
            $this->fail('Expected exception to be thrown.');
        } catch (\Exception $exception) {
            $this->assertEquals(
                'Generator has not been initialised with the locale "fr_FR".',
                $exception->getMessage()
            );
        }

        $this->assertGeneratorLocaleIs('fr_FR', FakerFactory::create('fr_FR'));
    }

    public function testIfALocaleIsGivenThenCreatesANewGeneratorWithThisLocaleAndTheDecoratedGeneratorProviders()
    {
        $generator = FakerFactory::create();
        $generator->addProvider(new DummyProvider());

        $factory = new GeneratorFactory($generator);
        $actual = $factory->createOrReturnExistingInstance('fr_FR');

        $expected = FakerFactory::create('fr_FR');
        $expected->addProvider(new DummyProvider());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox When a locale is given, only the non-default providers of the decorated generator are added to the created generator.
     */
    public function testFakerDefaultProvidersAreNotAdded()
    {
        $generator = FakerFactory::create();
        $generator->addProvider(new DummyProvider());

        $factory = new GeneratorFactory($generator);
        $instance = $factory->createOrReturnExistingInstance('fr_FR');

        $this->assertGeneratorLocaleIsNot('en_US', $instance);
    }

    public function testEachGeneratorCreatedIsCached()
    {
        $factory = new GeneratorFactory(FakerFactory::create());

        $this->assertSame(
            $factory->createOrReturnExistingInstance('fr_FR'),
            $factory->createOrReturnExistingInstance('fr_FR')
        );
    }

    public function testCreatingGeneratorWithInvalidLocaleFallsbackOnFakerDefaultLocale()
    {
        $factory = new GeneratorFactory(FakerFactory::create());

        $this->assertEquals(
            $factory->createOrReturnExistingInstance('unknown'),
            $factory->createOrReturnExistingInstance('en_US')
        );
    }

    public function testCanReturnDecoratedGenerator()
    {
        $generator = FakerFactory::create();
        $factory = new GeneratorFactory($generator);

        $this->assertSame($generator, $factory->getSeedGenerator());
    }

    private function assertGeneratorLocaleIs(string $locale, FakerGenerator $generator)
    {
        $providers = $generator->getProviders();
        $regex = sprintf('/^Faker\\\Provider\\\%s\\\.*/', $locale);
        foreach ($providers as $provider) {
            if (preg_match($regex, get_class($provider))) {
                return;
            }
        }

        throw new \Exception(sprintf('Generator has not been initialised with the locale "%s".', $locale));
    }

    private function assertGeneratorLocaleIsNot(string $locale, FakerGenerator $generator)
    {
        try {
            $this->assertGeneratorLocaleIs($locale, $generator);

            return;
        } catch (\Exception $exception) {
            if ($exception->getMessage() === sprintf('Generator has not been initialised with the locale "%s".', $locale)) {
                return;
            }

            throw $exception;
        }

        throw new \Exception(sprintf('Generator has been initialised with the locale "%s".', $locale));
    }
}
