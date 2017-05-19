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
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Nelmio\Alice\Bridge\Symfony\DependencyInjection\Configuration
 * @group integration
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultValues()
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

        $this->assertEquals($expected, $actual);
    }

    public function testOverriddeValues()
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
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "nelmio_alice.locale": Expected a string value but got "boolean" instead.
     */
    public function testLocaleMustBeAStringValues()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'locale' => false,
                ]
            ]
        );
    }

    public function testSeedCanBeNull()
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
                ]
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^Invalid type for path "nelmio_alice.functions_blacklist"\. Expected array, but got string/
     */
    public function testFunctionsBlacklistMustAnArray()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'functions_blacklist' => 'string',
                ]
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "nelmio_alice.functions_blacklist": Expected an array of strings but got "boolean" element in the array instead.
     */
    public function testFunctionsBlacklistMustBeStrings()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'functions_blacklist' => [true],
                ]
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "nelmio_alice.max_unique_values_retry": Expected a strictly positive integer but got "0" instead.
     */
    public function testMaxUniqueValuesRetryMustBeAStrictlyPositiveValues()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'max_unique_values_retry' => 0,
                ]
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^Invalid type for path "nelmio_alice.loading_limit"\. Expected int, but got boolean\./
     */
    public function testLoadingLimitMustBeAnInteger()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'loading_limit' => false,
                ]
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "nelmio_alice.loading_limit": Expected a strictly positive integer but got "0" instead.
     */
    public function testLoadingLimitMustBeAStrictlyPositiveValues()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'loading_limit' => 0,
                ]
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^Invalid type for path "nelmio_alice.max_unique_values_retry"\. Expected int, but got boolean\./
     */
    public function testMaxUniqueValuesRetryMustBeAnInteger()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'max_unique_values_retry' => false,
                ]
            ]
        );
    }

    public function testConfigurationParametersAreInjectedAsParameters()
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
            $this->assertEquals($value, $kernel->getContainer()->getParameter($parameterName));
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
