<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

<<<<<<< 3b8bf753d248df7ab96028af0553bdf09119056b
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
=======
use Nelmio\Alice\Generator\Resolver\ParameterResolvingContext;
>>>>>>> WIP
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Parameter\ArrayParameterResolver
 */
class ArrayParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableParameterResolver()
    {
        $this->assertTrue(is_a(ArrayParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver()
    {
        $this->assertTrue(is_a(ArrayParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testIsImmutable()
    {
        $resolver = new ArrayParameterResolver();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $newResolver = $resolver->withResolver($injectedResolver);

        $this->assertInstanceOf(ArrayParameterResolver::class, $newResolver);
        $this->assertNotSame($resolver, $newResolver);
    }

    public function testIsDeepClonable()
    {
        $resolver = new ArrayParameterResolver();
        $newResolver = clone $resolver;

        $this->assertInstanceOf(ArrayParameterResolver::class, $newResolver);
        $this->assertNotSame($newResolver, $resolver);

        $resolver = (new ArrayParameterResolver())->withResolver(new DummyParameterResolverInterface());
        $newResolver = clone $resolver;

        $this->assertInstanceOf(ArrayParameterResolver::class, $newResolver);
        $this->assertNotSame($newResolver, $resolver);
        $this->assertNotSameInjectedResolver($newResolver, $resolver);
    }
    
    public function testCanResolveOnlyArrayValues()
    {
        $resolver = new ArrayParameterResolver();
        $parameter = new Parameter('foo', null);
        
        $this->assertTrue($resolver->canResolve($parameter->withValue([])));

        $this->assertFalse($resolver->canResolve($parameter->withValue(null)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(10)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(.75)));
        $this->assertFalse($resolver->canResolve($parameter->withValue('string')));
        $this->assertFalse($resolver->canResolve($parameter->withValue(new \stdClass())));
        $this->assertFalse($resolver->canResolve($parameter->withValue(function () {})));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Resolver "Nelmio\Alice\Generator\Resolver\Parameter\ArrayParameterResolver" must have a resolver
     *                           set before having the method "ArrayParameterResolver::resolve()" called.
     */
    public function testRequiresInjectedResolverToResolverAParameter()
    {
        $resolver = new ArrayParameterResolver();

        $resolver->resolve(new Parameter('foo', null), new ParameterBag(), new ParameterBag());
    }
    
    public function testIterateOverEachElementAndUseTheDecoratedResolverToResolveEachValue()
    {
        $array = [
            $val1 = new \stdClass(),
            $val2 = function () {},
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('0', $val1),
                $unresolvedParameters,
                $resolvedParameters,
                $context->with('array_param')
            )
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1'
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', $val2),
                $unresolvedParameters,
                $resolvedParameters,
                $context->with('array_param')
            )
            ->willReturn(
                new ParameterBag([
                    '1' => 'val2'
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testIncludeAdditionalResolvedParametersInResult()
    {
        $array = [
            $val1 = new \stdClass(),
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1',
                    'other_param' => 'yo',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                ],
                'other_param' => 'yo',
            ]),
            $result
        );
    }

    /**
     * @dataProvider provideContexts
     */
    public function testEnsureAValidContextIsAlwaysPassedToTheInjectedResolver(\Nelmio\Alice\Generator\Resolver\ParameterResolvingContext $context = null, \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext $expected)
    {
        $array = [
            $val1 = new \stdClass(),
            $val2 = function () {},
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('0', $val1),
                $unresolvedParameters,
                $resolvedParameters,
                $expected
            )
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1'
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', $val2),
                $unresolvedParameters,
                $resolvedParameters,
                $expected
            )
            ->willReturn(
                new ParameterBag([
                    '1' => 'val2'
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
    }

    public function provideContexts()
    {
        return [
            'no context' => [
                null,
                new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('array_param'),
            ],
            'context that does not contain the parameter being resolved' => [
                new ParameterResolvingContext('unrelated'),
                (new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('unrelated'))->with('array_param'),
            ],
            'context that contains the parameter being resolved' => [
                (new ParameterResolvingContext('unrelated'))->with('array_param'),
                (new ParameterResolvingContext('unrelated'))->with('array_param'),
            ],
        ];
    }

    private function assertNotSameInjectedResolver(ArrayParameterResolver $firstResolver, ArrayParameterResolver $secondResolver)
    {
        $this->assertNotSame(
            $this->getInjectedResolver($firstResolver),
            $this->getInjectedResolver($secondResolver)
        );
    }

    private function getInjectedResolver(ArrayParameterResolver $resolver): ParameterResolverInterface
    {
        $resolverReflectionObject = new \ReflectionObject($resolver);
        $resolverPropertyReflection = $resolverReflectionObject->getProperty('resolver');
        $resolverPropertyReflection->setAccessible(true);

        return $resolverPropertyReflection->getValue($resolver);
    }
}
