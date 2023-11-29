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

use Nelmio\Alice\Symfony\KernelFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Nelmio\Alice\Bridge\Symfony\DependencyInjection\Configuration
 *
 * @group integration
 * @internal
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'locale' => 'en_US',
            'seed' => 1,
            'functions_blacklist' => ['current'],
            'loading_limit' => 5,
            'max_unique_values_retry' => 150,
        ];
        $actual = $processor->processConfiguration($configuration, []);

        self::assertEquals($expected, $actual);
    }

    public function testOverriddeValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'locale' => 'fr_FR',
            'seed' => 10,
            'functions_blacklist' => ['fake'],
            'loading_limit' => 50,
            'max_unique_values_retry' => 15,
        ];
        $actual = $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'locale' => 'fr_FR',
                    'seed' => 10,
                    'functions_blacklist' => ['fake'],
                    'loading_limit' => 50,
                    'max_unique_values_retry' => 15,
                ],
            ],
        );

        self::assertEquals($expected, $actual);
    }

    public function testLocaleMustBeAStringValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "nelmio_alice.locale": Expected a string value but got "boolean" instead.');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'locale' => false,
                ],
            ],
        );
    }

    public function testSeedCanBeNull(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'locale' => 'en_US',
            'seed' => null,
            'functions_blacklist' => ['current'],
            'loading_limit' => 5,
            'max_unique_values_retry' => 150,
        ];
        $actual = $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'seed' => null,
                ],
            ],
        );

        self::assertEquals($expected, $actual);
    }

    public function testFunctionsBlacklistMustAnArray(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/^Invalid type for path "nelmio_alice.functions_blacklist"\. Expected "?array"?, but got "?string"?/');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'functions_blacklist' => 'string',
                ],
            ],
        );
    }

    public function testFunctionsBlacklistMustBeStrings(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "nelmio_alice.functions_blacklist": Expected an array of strings but got "boolean" element in the array instead.');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'functions_blacklist' => [true],
                ],
            ],
        );
    }

    public function testMaxUniqueValuesRetryMustBeAStrictlyPositiveValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "nelmio_alice.max_unique_values_retry": Expected a strictly positive integer but got "0" instead.');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'max_unique_values_retry' => 0,
                ],
            ],
        );
    }

    public function testLoadingLimitMustBeAnInteger(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/^Invalid type for path "nelmio_alice.loading_limit"\. Expected "?int"?, but got "?bool(ean)?"?\./');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'loading_limit' => false,
                ],
            ],
        );
    }

    public function testLoadingLimitMustBeAStrictlyPositiveValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "nelmio_alice.loading_limit": Expected a strictly positive integer but got "0" instead.');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'loading_limit' => 0,
                ],
            ],
        );
    }

    public function testMaxUniqueValuesRetryMustBeAnInteger(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/^Invalid type for path "nelmio_alice.max_unique_values_retry"\. Expected "?int"?, but got "?bool(ean)?"?\./');

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'max_unique_values_retry' => false,
                ],
            ],
        );
    }

    public function testConfigurationParametersAreInjectedAsParameters(): void
    {
        $kernel = KernelFactory::createKernel();
        $kernel->boot();

        $expected = [
            'nelmio_alice.locale' => 'en_US',
            'nelmio_alice.functions_blacklist' => ['current'],
            'nelmio_alice.seed' => 1,
            'nelmio_alice.loading_limit' => 5,
            'nelmio_alice.max_unique_values_retry' => 150,
        ];

        foreach ($expected as $parameterName => $value) {
            self::assertEquals($value, $kernel->getContainer()->getParameter($parameterName));
        }

        $kernel->shutdown();
    }

    public function provideInvalidSeedValues()
    {
        yield 'negative integer' => [-1];

        yield 'null integer' => [0];

        yield 'string value' => ['seed'];
    }
}
