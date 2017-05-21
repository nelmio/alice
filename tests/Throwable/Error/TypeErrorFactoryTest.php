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
    public function testCreateForObjectArgument()
    {
        $error = TypeErrorFactory::createForObjectArgument(10);

        $this->assertEquals(
            'Expected instance argument to be an object. Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForDynamicArrayQuantifier()
    {
        $error = TypeErrorFactory::createForDynamicArrayQuantifier(new stdClass());

        $this->assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForDynamicArrayQuantifier(10);

        $this->assertEquals(
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForDynamicArrayElement()
    {
        $error = TypeErrorFactory::createForDynamicArrayElement(new stdClass());

        $this->assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForDynamicArrayElement(10);

        $this->assertEquals(
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueQuantifier()
    {
        $error = TypeErrorFactory::createForOptionalValueQuantifier(new stdClass());

        $this->assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueQuantifier(10);

        $this->assertEquals(
            'Expected quantifier to be either a scalar value or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueFirstMember()
    {
        $error = TypeErrorFactory::createForOptionalValueFirstMember(new stdClass());

        $this->assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueFirstMember(10);

        $this->assertEquals(
            'Expected first member to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForOptionalValueSecondMember()
    {
        $error = TypeErrorFactory::createForOptionalValueSecondMember(new stdClass());

        $this->assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForOptionalValueSecondMember(10);

        $this->assertEquals(
            'Expected second member to be either null, a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidParameterKey()
    {
        $error = TypeErrorFactory::createForInvalidParameterKey(new stdClass());

        $this->assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidParameterKey(10);

        $this->assertEquals(
            'Expected parameter key to be either a string or an instance of "'.ValueInterface::class.'". '
            .'Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidDenormalizerType()
    {
        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, new stdClass());

        $this->assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidDenormalizerType(2, 10);

        $this->assertEquals(
            'Expected denormalizer 2 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
            .'"integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCall()
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCall(new stdClass());

        $this->assertEquals(
            'Expected method call value to be an array. Got "object" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidSpecificationBagMethodCallName()
    {
        $error = TypeErrorFactory::createForInvalidSpecificationBagMethodCallName(new stdClass());

        $this->assertEquals(
            'Expected method name. Got "object" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureBagParameters()
    {
        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(new stdClass());

        $this->assertEquals(
            'Expected parameters to be an array. Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidFixtureBagParameters(10);

        $this->assertEquals(
            'Expected parameters to be an array. Got "integer" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludeStatementInData()
    {
        $error = TypeErrorFactory::createForInvalidIncludeStatementInData(new stdClass(), 'foo.yml');

        $this->assertEquals(
            'Expected include statement to be either null or an array of files to include. Got "object" '
            .'instead in file "foo.yml".',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidIncludedFilesInData()
    {
        $error = TypeErrorFactory::createForInvalidIncludedFilesInData(new stdClass(), 'foo.yml');

        $this->assertEquals(
            'Expected elements of include statement to be file names. Got "object" instead in file "foo.yml".',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidFixtureFileReturnedData()
    {
        $error = TypeErrorFactory::createForInvalidFixtureFileReturnedData('foo.yml');

        $this->assertEquals(
            'The file "foo.yml" must return a PHP array.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }

    public function testCreateForInvalidChainableParameterResolver()
    {
        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(new stdClass());

        $this->assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "stdClass" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());


        $error = TypeErrorFactory::createForInvalidChainableParameterResolver(10);

        $this->assertEquals(
            'Expected resolvers to be "'.ParameterResolverInterface::class.'" objects. Got "10" instead.',
            $error->getMessage()
        );
        $this->assertEquals(0, $error->getCode());
        $this->assertNull($error->getPrevious());
    }
}
