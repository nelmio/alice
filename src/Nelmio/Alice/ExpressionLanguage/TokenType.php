<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage;

final class TokenType
{
    const STRING_TYPE = 'STRING_TYPE';
    const PARAMETER_TYPE = 'PARAMETER_TYPE';
    const ESCAPED_ARROW_TYPE = 'ESCAPED_ARROW_TYPE';
    const FUNCTION_TYPE = 'FUNCTION_TYPE';
    const IDENTITY_TYPE = 'IDENTITY_TYPE';
    const OPTIONAL_TYPE = 'OPTIONAL_TYPE';

    const DYNAMIC_ARRAY_TYPE = 'DYNAMIC_ARRAY_TYPE';
    const ESCAPED_ARRAY_TYPE = 'ESCAPED_ARRAY_TYPE';
    const STRING_ARRAY_TYPE = 'STRING_ARRAY_TYPE';

    const ESCAPED_REFERENCE_TYPE = 'ESCAPED_REFERENCE_TYPE';
    const SIMPLE_REFERENCE_TYPE = 'SIMPLE_REFERENCE_TYPE';
    const LIST_REFERENCE_TYPE = 'LIST_REFERENCE_TYPE';
    const WILDCARD_REFERENCE_TYPE = 'WILDCARD_REFERENCE_TYPE';
    const RANGE_REFERENCE_TYPE = 'RANGE_REFERENCE_TYPE';
    const PROPERTY_REFERENCE_TYPE = 'PROPERTY_REFERENCE_TYPE';
    const METHOD_REFERENCE_TYPE = 'METHOD_REFERENCE_TYPE';

    const VARIABLE_TYPE = 'VARIABLE_TYPE';
    const ESCAPED_VARIABLE_TYPE = 'ESCAPED_VARIABLE_TYPE';
    
    private static $values = [
        self::STRING_TYPE => true,
        self::PARAMETER_TYPE => true,
        self::ESCAPED_ARROW_TYPE => true,
        self::FUNCTION_TYPE => true,
        self::IDENTITY_TYPE => true,
        self::OPTIONAL_TYPE => true,
        self::DYNAMIC_ARRAY_TYPE => true,
        self::ESCAPED_ARRAY_TYPE => true,
        self::STRING_ARRAY_TYPE => true,
        self::ESCAPED_REFERENCE_TYPE => true,
        self::SIMPLE_REFERENCE_TYPE => true,
        self::LIST_REFERENCE_TYPE => true,
        self::WILDCARD_REFERENCE_TYPE => true,
        self::RANGE_REFERENCE_TYPE => true,
        self::PROPERTY_REFERENCE_TYPE => true,
        self::METHOD_REFERENCE_TYPE => true,
        self::VARIABLE_TYPE => true,
        self::ESCAPED_VARIABLE_TYPE => true,
    ];

    /**
     * @var int
     */
    private $value;

    public function __construct(string $type)
    {
        if (false === array_key_exists($type, self::$values)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected type to be a known token type but got "%s".',
                    $type
                )
            );
        }
        
        $this->value = $type;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
}
