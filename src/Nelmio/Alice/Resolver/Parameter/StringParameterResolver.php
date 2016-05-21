<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Exception\Resolver\ParameterNotFoundException;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

final class StringParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    const PATTERN = '/<{(?<parameter>[^<{]+?)}>/';
    const SINGLE_PARAMETER_PATTERN = '/^<{(?<parameter>(?(?=\{)^[\>]|.)+)}>$/';

    /**
     * @var ParameterResolverInterface|null
     */
    private $resolver;

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        return is_string($parameter->getValue());
    }

    /**
     * {@inheritdoc}
     *
     * @param bool|int|float $parameter
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag
    {
        $self = $this;
        $result = new ParameterBag();
        $value = preg_replace_callback(
            self::PATTERN,
            function ($match) use ($self, $context, $unresolvedParameters, $resolvedParameters, $parameter, &$result) {
                $key = $match['parameter'];
                $context = $context->with($key);
                
                $resolvedBag = $self->resolveStringKey($parameter, $key, $unresolvedParameters, $resolvedParameters, $context);

                $resolvedKey = $resolvedBag->get($key);
                foreach ($resolvedBag as $paramKey => $paramValue) {
                    $result = $result->with(new Parameter($paramKey, $paramValue));
                }

                return $resolvedKey;
            },
            $parameter->getValue()
        );

        return $result->with($parameter->withValue($value));
    }

    /**
     * @param Parameter        $parameter Parameter being resolved
     * @param string           $key       Key of the parameter that need to be resolved to resolve $parameter
     * @param ParameterBag     $unresolvedParameters
     * @param ParameterBag     $resolvedParameters
     * @param ResolvingContext $context
     *
     * @return array|mixed|ParameterBag
     * @throws ParameterNotFoundException
     * @throws \Nelmio\Alice\Exception\Resolver\CircularReferenceException
     */
    private function resolveStringKey(
        Parameter $parameter,
        string $key,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context
    ): ParameterBag
    {
        if ($resolvedParameters->has($key)) {
            return new ParameterBag([$key => $resolvedParameters->get($key)]);
        }

        if (false === $unresolvedParameters->has($key)) {
            throw new ParameterNotFoundException(
                sprintf(
                    'Could not find the parameter "%s" when resolving "%s".',
                    $key,
                    $parameter->getKey()
                )
            );
        }

        $context->checkForCircularReference($key);
        $context = $context->with($key);

        return $this->resolver->resolve(
            new Parameter($key, $unresolvedParameters->get($key)),
            $unresolvedParameters,
            $resolvedParameters,
            $context
        );
    }
}
