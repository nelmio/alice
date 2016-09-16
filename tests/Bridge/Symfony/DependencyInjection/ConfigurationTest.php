<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection;

use Nelmio\Alice\Symfony\KernelFactory;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Nelmio\Alice\Bridge\Symfony\DependencyInjection\Configuration
 * @group integration
 * @group symfony
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultValues()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'locale' => 'en_US',
            'seed' => 1,
            'loading_limit' => 5,
            'max_unique_values_retries' => 150,
        ];
        $actual = $processor->processConfiguration($configuration, []);

        $this->assertEquals($expected, $actual);
    }

    public function testSeedCanBeNull()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'locale' => 'en_US',
            'seed' => null,
            'loading_limit' => 5,
            'max_unique_values_retries' => 150,
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
     * @dataProvider provideInvalidSeedValues
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^Invalid configuration for path "nelmio_alice.seed": Expected value "nelmio_alice.seed" to be either null or a strictly positive interger but got ".*" instead\.$/
     */
    public function testIfNotNullThenSeedMustBeAStrictlyPositiveInteger($seed)
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration(
            $configuration,
            [
                'nelmio_alice' => [
                    'seed' => $seed,
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
            'nelmio_alice.seed' => 1,
            'nelmio_alice.loading_limit' => 5,
            'nelmio_alice.max_unique_values_retries' => 150,
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
