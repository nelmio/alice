<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\ParameterBagDenormalizerInterface;
use Nelmio\Alice\ParameterBag;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer
 */
class SimpleParameterBagDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleParameterBagDenormalizer
     */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = new SimpleParameterBagDenormalizer();
    }
    
    public function testIsAParameterBagDenormalizer()
    {
        $this->assertInstanceOf(ParameterBagDenormalizerInterface::class, $this->denormalizer);
    }

    /**
     * @dataProvider provideDataWithNoParameters
     */
    public function testReturnsEmptyBagIfNoParametersHaveBeenDeclared(array $data)
    {
        $actual = $this->denormalizer->denormalize($data);

        $this->assertEquals(new ParameterBag(), $actual);
    }

    /**
     * @dataProvider provideDataWithInvalidParameterKeys
     */
    public function testThrowExceptionIfParametersKeyIsNotAnArray(array $data, string $expectedExceptionMessage)
    {
        try {
            $this->denormalizer->denormalize($data);
            $this->fail('Expected exception to be thrown.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals($expectedExceptionMessage, $exception->getMessage());
        }

        $this->assertTrue(true, 'Did not expect an exception to be thrown.');
    }

    public function provideDataWithNoParameters()
    {
        yield 'no parameters' => [
            [],
        ];

        yield 'parameters with null value' => [
            [
                'parameters' => null,
            ],
        ];

        yield 'parameters with empty value' => [
            [
                'parameters' => [],
            ],
        ];
    }

    public function provideDataWithInvalidParameterKeys()
    {
        yield 'string value' => [
            [
                'parameters' => 'string value',
            ],
            'Expected parameters to be an array. Got "string" instead.',
        ];

        yield 'object value' => [
            [
                'parameters' => new \stdClass(),
            ],
            'Expected parameters to be an array. Got "stdClass" instead.',
        ];
    }
}
