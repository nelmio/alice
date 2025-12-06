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

use Exception;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Bridge\Symfony\Application\AppKernel;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Symfony\KernelFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function restore_error_handler;

/**
 * @internal
 */
#[Group('integration')]
#[CoversNothing]
#[RunClassInSeparateProcess]
final class DynamicServicesConfigurationTest extends TestCase
{
    private AppKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = KernelFactory::createKernel(
            __DIR__.'/../../../../fixtures/Bridge/Symfony/Application/config_custom.yml',
        );
        $this->kernel->boot();
    }

    protected function tearDown(): void
    {
        if (null !== $this->kernel) {
            $this->kernel->shutdown();
        }
    }

    public function testResolverUsesTheLimitIsDefinedInTheConfiguration(): void
    {
        /** @var RecursiveParameterResolver $resolver */
        $resolver = $this->kernel->getContainer()->get('nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver');

        self::assertInstanceOf(RecursiveParameterResolver::class, $resolver);
        $limitRefl = (new ReflectionClass(RecursiveParameterResolver::class))->getProperty('limit');

        self::assertEquals(50, $limitRefl->getValue($resolver));
    }

    public function testUniqueValueResolverUsesTheLimitIsDefinedInTheConfiguration(): void
    {
        /** @var UniqueValueResolver $resolver */
        $resolver = $this->kernel->getContainer()->get('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver');

        self::assertInstanceOf(UniqueValueResolver::class, $resolver);
        $limitRefl = (new ReflectionClass(UniqueValueResolver::class))->getProperty('limit');

        self::assertEquals(15, $limitRefl->getValue($resolver));
    }

    public function testUniqueValueResolverUsesTheSeedAndLocaleIsDefinedInTheConfiguration(): void
    {
        /** @var FakerGenerator $generator */
        $generator = $this->kernel->getContainer()->get('nelmio_alice.faker.generator');

        self::assertInstanceOf(FakerGenerator::class, $generator);
        $this->assertGeneratorLocaleIs('fr_FR', $generator);
        $this->assertHasAliceProvider($generator);
    }

    private function assertGeneratorLocaleIs(string $locale, FakerGenerator $generator): void
    {
        $providers = $generator->getProviders();
        $regex = sprintf('/^Faker\\\Provider\\\%s\\\.*/', $locale);
        foreach ($providers as $provider) {
            if (preg_match($regex, $provider::class)) {
                return;
            }
        }

        throw new Exception(sprintf('Generator has not been initialised with the locale "%s".', $locale));
    }

    private function assertHasAliceProvider(FakerGenerator $generator): void
    {
        $providers = $generator->getProviders();
        foreach ($providers as $provider) {
            if ($provider instanceof AliceProvider) {
                return;
            }
        }

        throw new Exception(sprintf('Generator does not have the provider "%s".', AliceProvider::class));
    }
}
