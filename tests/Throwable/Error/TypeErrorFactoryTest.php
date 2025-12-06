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

namespace Nelmio\Alice\Throwable\Error;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Error\TypeErrorFactory
 * @internal
 */
final class TypeErrorFactoryTest extends TestCase
{
    public function testCreateForDynamicArrayQuantifier(): void
    {
        $error = TypeErrorFactory::createForDynamicArrayQuantifier(new stdClass());

        self::assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForDynamicArrayQuantifier(10);

        self::assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForDynamicArrayElement(): void
    {
        $error = TypeErrorFactory::createForDynamicArrayElement(new stdClass());

        self::assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForDynamicArrayElement(10);

        self::assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueQuantifier(): void
    {
        $error = TypeErrorFactory::createForOptionalValueQuantifier(new stdClass());

        self::assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForOptionalValueQuantifier(10);

        self::assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueFirstMember(): void
    {
        $error = TypeErrorFactory::createForOptionalValueFirstMember(new stdClass());

        self::assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForOptionalValueFirstMember(10);

        self::assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueSecondMember(): void
    {
        $error = TypeErrorFactory::createForOptionalValueSecondMember(new stdClass());

        self::assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForOptionalValueSecondMember(10);

        self::assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidParameterKey(): void
    {
        $error = TypeErrorFactory::createForInvalidParameterKey(new stdClass());

        self::assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForInvalidParameterKey(10);

        self::assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidDenormalizerType(): void
    {
        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, new stdClass());

        self::assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, 10);

        self::assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCall(): void
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCall(new stdClass());

        self::assertEquals(
            'Expected method call value to be an array. Got "object" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCallName(): void
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCallName(new stdClass());

        self::assertEquals(
            'Expected method name. Got "object" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureBagParameters(): void
    {
        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(new stdClass());

        self::assertEquals(
            'Expected parameters to be an array. Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(10);

        self::assertEquals(
            'Expected parameters to be an array. Got "integer" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludeStatementInData(): void
    {
        $error = TypeErrorFactory::createForInvalidIncludeStatementInData(new stdClass(), 'foo.yml');

        self::assertEquals(
            'Expected include statement to be either null or an array of files to include. Got "object" '
            .'instead in file "foo.yml".',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludedFilesInData(): void
    {
        $error = TypeErrorFactory::createForInvalidIncludedFilesInData(new stdClass(), 'foo.yml');

        self::assertEquals(
            'Expected elements of include statement to be file names. Got "object" instead in file "foo.yml".',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureFileReturnedData(): void
    {
        $error = TypeErrorFactory::createForInvalidFixtureFileReturnedData('foo.yml');

        self::assertEquals(
            'The file "foo.yml" must return a PHP array.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidChainableParameterResolver(): void
    {
        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(new stdClass());

        self::assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "stdClass" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());

        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(10);

        self::assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "10" instead.',
            $error->getMessage(),
        );
        self::assertEquals(0, $error->getCode());
        self::assertNull($error->getPrevious());
    }
}
