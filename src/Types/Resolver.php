<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\Type;

final class Resolver
{
    /** @var string Definition of the ARRAY operator for types */
    const OPERATOR_ARRAY = '[]';

    /** @var string Definition of the NAMESPACE operator in PHP */
    const OPERATOR_NAMESPACE = '\\';

    /** @var string[] List of recognized keywords and unto which Value Object they map */
    private $keywords = array(
        'string' => 'phpDocumentor\Reflection\Types\String',
        'int' => 'phpDocumentor\Reflection\Types\Integer',
        'integer' => 'phpDocumentor\Reflection\Types\Integer',
        'bool' => 'phpDocumentor\Reflection\Types\Boolean',
        'boolean' => 'phpDocumentor\Reflection\Types\Boolean',
        'float' => 'phpDocumentor\Reflection\Types\Float',
        'double' => 'phpDocumentor\Reflection\Types\Float',
        'object' => 'phpDocumentor\Reflection\Types\Object_',
        'mixed' => 'phpDocumentor\Reflection\Types\Mixed',
        'array' => 'phpDocumentor\Reflection\Types\Array_',
        'resource' => 'phpDocumentor\Reflection\Types\Resource',
        'void' => 'phpDocumentor\Reflection\Types\Void',
        'null' => 'phpDocumentor\Reflection\Types\Null_',
        'scalar' => 'phpDocumentor\Reflection\Types\Scalar',
        'callback' => 'phpDocumentor\Reflection\Types\Callable_',
        'callable' => 'phpDocumentor\Reflection\Types\Callable_',
        'false' => 'phpDocumentor\Reflection\Types\Boolean',
        'true' => 'phpDocumentor\Reflection\Types\Boolean',
        'self' => 'phpDocumentor\Reflection\Types\Self_',
        '$this' => 'phpDocumentor\Reflection\Types\This',
        'static' => 'phpDocumentor\Reflection\Types\Static_'
    );

    /**
     * Analyzes the given type and returns the FQCN variant.
     *
     * When a type is provided this method checks whether it is not a keyword or
     * Fully Qualified Class Name. If so it will use the given namespace and
     * aliases to expand the type to a FQCN representation.
     *
     * This method only works as expected if the namespace and aliases are set;
     * no dynamic reflection is being performed here.
     *
     * @param string $type The relative or absolute type.
     *
     * @uses Context::getNamespace()        to determine with what to prefix the type name.
     * @uses Context::getNamespaceAliases() to check whether the first part of the relative type name should not be
     *     replaced with another namespace.
     *
     * @return Type|null
     */
    public function resolve($type, Context $context)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'Attempted to resolve type but it appeared not to be a string, received: ' . var_export($type, true)
            );
        }

        $type = trim($type);
        if (!$type) {
            throw new \InvalidArgumentException('Attempted to resolve "' . $type . '" but it appears to be empty');
        }

        switch (true) {
            case $this->isKeyword($type):
                return $this->resolveKeyword($type);
            case ($this->isCompoundType($type)):
                return $this->resolveCompoundType($type, $context);
            case $this->isFqsen($type):
                return $this->resolveFqsen($type);
            case $this->isTypedArray($type):
                return $this->resolveTypedArray($type, $context);
            case $this->isPartialStructuralElementName($type):
                return $this->resolvePartialStructuralElementName($type, $context);
            // @codeCoverageIgnoreStart
            default:
                // I haven't got the foggiest how the logic would come here but added this as a defense.
                throw new \RuntimeException(
                    'Unable to resolve type "' . $type . '", there is no known method to resolve it'
                );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Adds a keyword to the list of Keywords and associates it with a specific Value Object.
     *
     * @param string $keyword
     * @param string $typeClassName
     *
     * @return void
     */
    public function addKeyword($keyword, $typeClassName)
    {
        if (!class_exists($typeClassName)) {
            throw new \InvalidArgumentException(
                'The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class'
                . ' but we could not find the class ' . $typeClassName
            );
        }

        if (!in_array(Type::class, class_implements($typeClassName))) {
            throw new \InvalidArgumentException(
                'The class "' . $typeClassName . '" must implement the interface "phpDocumentor\Reflection\Type"'
            );
        }

        $this->keywords[$keyword] = $typeClassName;
    }

    /**
     * Detects whether the given type represents an array.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isTypedArray($type)
    {
        return substr($type, -2) === self::OPERATOR_ARRAY;
    }

    /**
     * Detects whether the given type represents a PHPDoc keyword.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isKeyword($type)
    {
        return in_array(strtolower($type), array_keys($this->keywords), true);
    }

    /**
     * Detects whether the given type represents a relative structural element name.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isPartialStructuralElementName($type)
    {
        return ($type[0] !== self::OPERATOR_NAMESPACE) && !$this->isKeyword($type);
    }

    /**
     * Tests whether the given type is a Fully Qualified Structural Element Name.
     *
     * @param string $type
     *
     * @return bool
     */
    private function isFqsen($type)
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }

    /**
     * Tests whether the given type is a compound type (i.e. `string|int`).
     *
     * @param string $type
     *
     * @return bool
     */
    private function isCompoundType($type)
    {
        return strpos($type, '|') !== false;
    }

    /**
     * Resolves the given typed array string (i.e. `string[]`) into an Array object with the right types set.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Array_
     */
    private function resolveTypedArray($type, Context $context)
    {
        return new Array_($this->resolve(substr($type, 0, -2), $context));
    }

    /**
     * Resolves the given keyword (such as `string`) into a Type object representing that keyword.
     *
     * @param string $type
     *
     * @return Type
     */
    private function resolveKeyword($type)
    {
        $className = $this->keywords[strtolower($type)];

        return new $className();
    }

    /**
     * Resolves the given FQSEN string into an FQSEN object.
     *
     * @param string $type
     *
     * @return Object_
     */
    private function resolveFqsen($type)
    {
        return new Object_(new Fqsen($type));
    }

    /**
     * Resolves a partial Structural Element Name (i.e. `Reflection\DocBlock`) to its FQSEN representation
     * (i.e. `\phpDocumentor\Reflection\DocBlock`) based on the Namespace and aliases mentioned in the Context.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Object_
     */
    private function resolvePartialStructuralElementName($type, Context $context)
    {
        $typeParts = explode(self::OPERATOR_NAMESPACE, $type, 2);

        $namespaceAliases = $context->getNamespaceAliases();

        // if the first segment is not an alias; prepend namespace name and return
        if (!isset($namespaceAliases[$typeParts[0]])) {
            $namespace = $context->getNamespace();
            if ('' !== $namespace) {
                $namespace .= self::OPERATOR_NAMESPACE;
            }

            return new Object_(new Fqsen(self::OPERATOR_NAMESPACE . $namespace . $type));
        }

        $typeParts[0] = $namespaceAliases[$typeParts[0]];

        return new Object_(new Fqsen(self::OPERATOR_NAMESPACE . implode(self::OPERATOR_NAMESPACE, $typeParts)));
    }

    /**
     * Resolves a compound type (i.e. `string|int`) into the appropriate Type objects or FQSEN.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Compound
     */
    private function resolveCompoundType($type, Context $context)
    {
        $types = [];

        foreach (explode('|', $type) as $part) {
            $types[] = $this->resolve($part, $context);
        }

        return new Compound($types);
    }
}
