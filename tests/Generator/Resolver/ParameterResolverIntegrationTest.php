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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @coversNothing
 */
class ParameterResolverIntegrationTest extends TestCase
{
    /**
     * @var SimpleParameterBagResolver
     */
    protected $resolver;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->resolver = (new NativeLoader())->getParameterResolver();
    }

    /**
     * @dataProvider provideParameters
     */
    public function testResolveParameters(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null,
        ParameterBag $expected
    ) {
        $actual = $this->resolver->resolve($unresolvedParameters, $injectedParameters);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideCircularReferences
     *
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException
     * @expectedExceptionMessageRegExp /^Circular reference detected for the parameter "[^\"]+" while resolving \[.+]\.$/
     */
    public function testThrowExceptionIfCircularReferenceDetected(ParameterBag $unresolvedParameters, ParameterBag $injectedParameters = null)
    {
        $this->resolver->resolve($unresolvedParameters, $injectedParameters);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\ParameterNotFoundException
     */
    public function testThrowExceptionWhenResolvingNonExistentParameter()
    {
        $this->resolver->resolve(
            new ParameterBag([
                'param1' => '<{inexisting_param}>',
            ])
        );
    }

    public function provideCircularReferences()
    {
        $return = [];

        $return['simple scenario'] = [
            new ParameterBag([
                'param1' => '<{param2}>',
                'param2' => '<{param1}>',
            ]),
            null,
        ];

        $return['one level deep scenario1'] = [
            new ParameterBag([
                'param1' => '<{param2}>',
                'param2' => '<{param3}>',
                'param3' => '<{param2}>',
            ]),
            null,
        ];

        $return['one level deep scenario2'] = [
            new ParameterBag([
                'param1' => '<{param2}>',
                'param2' => '<{param3}>',
                'param3' => '<{param1}>',
            ]),
            null,
        ];

        return $return;
    }

    public function provideParameters()
    {
        $return = [];

        $staticValues = new ParameterBag([
            'bool_param' => true,
            'int_param' => 2000,
            'float_param' => -.89,
            'object_param' => new \stdClass(),
            'closure_param' => function () {
            },
            'class_param' => 'App\Test\Dummy',
            'array_value' => [
                'dummy',
                'en' => 'GB',
                'fr' => [
                    200,
                    .5,
                ],
            ],
        ]);
        $return['static values'] = [
            $staticValues,
            null,
            $staticValues
        ];

        $return['simple dynamic parameter'] = [
            new ParameterBag([
                'param1' => '<{param2}>',
                'param2' => 'hello',
            ]),
            null,
            new ParameterBag([
                'param1' => 'hello',
                'param2' => 'hello',
            ])
        ];

        $return['simple inversed dynamic parameter'] = [
            new ParameterBag([
                'param1' => 'hello',
                'param2' => '<{param1}>',
            ]),
            null,
            new ParameterBag([
                'param1' => 'hello',
                'param2' => 'hello',
            ])
        ];

        $return['composite parameter'] = [
            new ParameterBag([
                'param1' => '<{param2}> <{param3}>',
                'param2' => 'NaN',
                'param3' => 'Bat'
            ]),
            null,
            new ParameterBag([
                'param1' => 'NaN Bat',
                'param2' => 'NaN',
                'param3' => 'Bat'
            ])
        ];

        $return['composite stringified reference'] = [
            new ParameterBag([
                'param1' => '<{param2}> <{param3}> <{param4}>',
                'param2' => true,
                'param3' => false,
                'param4' => -.89,
            ]),
            null,
            new ParameterBag([
                'param1' => '1  -0.89',
                'param2' => true,
                'param3' => false,
                'param4' => -.89,
            ])
        ];

        $return['composite stringified reference'] = [
            new ParameterBag([
                'param1' => '<{param2}> <{param4}>',
                'param2' => '<{param3}>',
                'param3' => false,
                'param4' => -.89,
            ]),
            null,
            new ParameterBag([
                'param1' => ' -0.89',
                'param2' => false,
                'param3' => false,
                'param4' => -.89,
            ])
        ];

        $return['nested parameters'] = [
            new ParameterBag([
                'param1' => '<{param<{param2}>}>',
                'param2' => 3,
                'param3' => 'foo',
            ]),
            null,
            new ParameterBag([
                'param1' => 'foo',
                'param2' => 3,
                'param3' => 'foo',
            ])
        ];

        $return['deep nested parameters'] = [
            new ParameterBag([
                'param1' => '<{param<{param<{param3}>}>}>',
                'param3' => 2,
                'param2' => 4,
                'param4' => 'foo'
            ]),
            null,
            new ParameterBag([
                'param1' => 'foo',
                'param3' => 2,
                'param2' => 4,
                'param4' => 'foo'
            ])
        ];

        $return['deep nested parameters'] = [
            new ParameterBag([
                'param1' => 'hey <{param<{param<{param3}>}>}> <{param4}> world',
                'param3' => 2,
                'param2' => 4,
                'param4' => 'foo'
            ]),
            null,
            new ParameterBag([
                'param1' => 'hey foo foo world',
                'param3' => 2,
                'param2' => 4,
                'param4' => 'foo'
            ])
        ];

        return $return;
    }
}
