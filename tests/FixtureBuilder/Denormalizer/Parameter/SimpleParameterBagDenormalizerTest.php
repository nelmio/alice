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
use stdClass;
use TypeError;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer
 * @internal
 */
class SimpleParameterBagDenormalizerTest extends TestCase
{
    /**
     * @var SimpleParameterBagDenormalizer
     */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new SimpleParameterBagDenormalizer();
    }

    public function testIsAParameterBagDenormalizer(): void
    {
        self::assertInstanceOf(ParameterBagDenormalizerInterface::class, $this->denormalizer);
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionObject($this->denormalizer))->isCloneable());
    }

    /**
     * @dataProvider provideDataWithNoParameters
     */
    public function testReturnsEmptyBagIfNoParametersHaveBeenDeclared(array $data): void
    {
        $actual = $this->denormalizer->denormalize($data);

        self::assertEquals(new ParameterBag(), $actual);
    }

    /**
     * @dataProvider provideDataWithInvalidParameterKeys
     */
    public function testThrowsExceptionIfParametersKeyIsNotAnArray(array $data, string $expectedExceptionMessage): void
    {
        try {
            $this->denormalizer->denormalize($data);
            self::fail('Expected exception to be thrown.');
        } catch (TypeError $exception) {
            self::assertEquals($expectedExceptionMessage, $exception->getMessage());
        }
    }

    public function provideDataWithNoParameters(): iterable
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

    public function provideDataWithInvalidParameterKeys(): iterable
    {
        yield 'string value' => [
            [
                'parameters' => 'string value',
            ],
            'Expected parameters to be an array. Got "string" instead.',
        ];

        yield 'object value' => [
            [
                'parameters' => new stdClass(),
            ],
            'Expected parameters to be an array. Got "stdClass" instead.',
        ];
    }
}
