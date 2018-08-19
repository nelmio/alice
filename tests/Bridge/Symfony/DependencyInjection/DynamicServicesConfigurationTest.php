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

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection;

use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Bridge\Symfony\Application\AppKernel;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Symfony\KernelFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @group integration
 */
class DynamicServicesConfigurationTest extends TestCase
{
    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->kernel = KernelFactory::createKernel(__DIR__.'/../../../../fixtures/Bridge/Symfony/Application/config_custom.yml');
        $this->kernel->boot();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        if (null !== $this->kernel) {
            $this->kernel->shutdown();
        }
    }

    public function testResolverUsesTheLimitIsDefinedInTheConfiguration()
    {
        /** @var RecursiveParameterResolver $resolver */
        $resolver = $this->kernel->getContainer()->get('nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver');

        $this->assertInstanceOf(RecursiveParameterResolver::class, $resolver);
        $limitRefl = (new \ReflectionClass(RecursiveParameterResolver::class))->getProperty('limit');
        $limitRefl->setAccessible(true);

        $this->assertEquals(50, $limitRefl->getValue($resolver));
    }

    public function testUniqueValueResolverUsesTheLimitIsDefinedInTheConfiguration()
    {
        /** @var UniqueValueResolver $resolver */
        $resolver = $this->kernel->getContainer()->get('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver');

        $this->assertInstanceOf(UniqueValueResolver::class, $resolver);
        $limitRefl = (new \ReflectionClass(UniqueValueResolver::class))->getProperty('limit');
        $limitRefl->setAccessible(true);

        $this->assertEquals(15, $limitRefl->getValue($resolver));
    }

    public function testUniqueValueResolverUsesTheSeedAndLocaleIsDefinedInTheConfiguration()
    {
        /** @var FakerGenerator $generator */
        $generator = $this->kernel->getContainer()->get('nelmio_alice.faker.generator');

        $this->assertInstanceOf(FakerGenerator::class, $generator);
        $this->assertGeneratorLocaleIs('fr_FR', $generator);
        $this->assertHasAliceProvider($generator);
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

    private function assertHasAliceProvider(FakerGenerator $generator)
    {
        $providers = $generator->getProviders();
        foreach ($providers as $provider) {
            if ($provider instanceof AliceProvider) {
                return;
            }
        }

        throw new \Exception(sprintf('Generator does not have the provider "%s".', AliceProvider::class));
    }
}
