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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\ParameterBagDenormalizerInterface;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer
 */
class SimpleParameterBagDenormalizerTest extends TestCase
{
    /**
     * @var SimpleParameterBagDenormalizer
     */
    private $denormalizer;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->denormalizer = new SimpleParameterBagDenormalizer();
    }

    public function testIsAParameterBagDenormalizer()
    {
        $this->assertInstanceOf(ParameterBagDenormalizerInterface::class, $this->denormalizer);
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionObject($this->denormalizer))->isCloneable());
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
    public function testThrowsExceptionIfParametersKeyIsNotAnArray(array $data, string $expectedExceptionMessage)
    {
        try {
            $this->denormalizer->denormalize($data);
            $this->fail('Expected exception to be thrown.');
        } catch (\TypeError $exception) {
            $this->assertEquals($expectedExceptionMessage, $exception->getMessage());
        }
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
