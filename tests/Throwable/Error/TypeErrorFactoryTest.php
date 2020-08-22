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
 */
class TypeErrorFactoryTest extends TestCase
{
    public function testCreateForDynamicArrayQuantifier(): void
    {
        $error = TypeErrorFactory::createForDynamicArrayQuantifier(new stdClass());

        static::assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForDynamicArrayQuantifier(10);

        static::assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForDynamicArrayElement(): void
    {
        $error = TypeErrorFactory::createForDynamicArrayElement(new stdClass());

        static::assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForDynamicArrayElement(10);

        static::assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueQuantifier(): void
    {
        $error = TypeErrorFactory::createForOptionalValueQuantifier(new stdClass());

        static::assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueQuantifier(10);

        static::assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueFirstMember(): void
    {
        $error = TypeErrorFactory::createForOptionalValueFirstMember(new stdClass());

        static::assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueFirstMember(10);

        static::assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueSecondMember(): void
    {
        $error = TypeErrorFactory::createForOptionalValueSecondMember(new stdClass());

        static::assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueSecondMember(10);

        static::assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidParameterKey(): void
    {
        $error = TypeErrorFactory::createForInvalidParameterKey(new stdClass());

        static::assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidParameterKey(10);

        static::assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidDenormalizerType(): void
    {
        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, new stdClass());

        static::assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, 10);

        static::assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCall(): void
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCall(new stdClass());

        static::assertEquals(
            'Expected method call value to be an array. Got "object" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCallName(): void
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCallName(new stdClass());

        static::assertEquals(
            'Expected method name. Got "object" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureBagParameters(): void
    {
        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(new stdClass());

        static::assertEquals(
            'Expected parameters to be an array. Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(10);

        static::assertEquals(
            'Expected parameters to be an array. Got "integer" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludeStatementInData(): void
    {
        $error = TypeErrorFactory::createForInvalidIncludeStatementInData(new stdClass(), 'foo.yml');

        static::assertEquals(
            'Expected include statement to be either null or an array of files to include. Got "object" '
            .'instead in file "foo.yml".',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludedFilesInData(): void
    {
        $error = TypeErrorFactory::createForInvalidIncludedFilesInData(new stdClass(), 'foo.yml');

        static::assertEquals(
            'Expected elements of include statement to be file names. Got "object" instead in file "foo.yml".',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureFileReturnedData(): void
    {
        $error = TypeErrorFactory::createForInvalidFixtureFileReturnedData('foo.yml');

        static::assertEquals(
            'The file "foo.yml" must return a PHP array.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }

    public function testCreateForInvalidChainableParameterResolver(): void
    {
        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(new stdClass());

        static::assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "stdClass" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(10);

        static::assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "10" instead.',
            $error->getMessage()
        );
        static::assertEquals(0, $error->getCode());
        static::assertNull($error->getPrevious());
    }
}
