<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Type;

/**
 * Collection
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class Collection extends \ArrayObject
{
    /** @var string Definition of the OR operator for types */
    const OPERATOR_OR = '|';

    /** @var string Definition of the ARRAY operator for types */
    const OPERATOR_ARRAY = '[]';

    /** @var string Definition of the NAMESPACE operator in PHP */
    const OPERATOR_NAMESPACE = '\\';

    /** @var string[] List of recognized keywords */
    protected $keywords = array(
        'string', 'int', 'integer', 'bool', 'boolean', 'float', 'double',
        'object', 'mixed', 'array', 'resource', 'void', 'null',
        'callback', 'callable', 'false', 'true', 'self', '$this', 'static'
    );

    /**
     * Current namespace of the invoking location.
     *
     * This string is used to prepend to type with a relative location.
     * May also be 'default' or 'global', in which case they are ignored.
     *
     * @var string
     */
    protected $namespace = '\\';

    /**
     * Associative array of alias => namespace pairs.
     *
     * @var string[]
     */
    protected $namespace_aliases = array();

    /**
     * Registers the namespace and aliases; uses that to add and expand the
     * given types.
     *
     * @param string[]    $types             Array containing a list of types
     *     to add to this container.
     * @param string|null $namespace         The namespace where the types in
     *     this container are relative to; used to expand any relative
     *     namespaces.
     * @param string[]    $namespace_aliases An array containing alias => FQNN
     *     pairs that are used in the resolving process.
     */
    public function __construct(
        array $types = array(),
        $namespace = null,
        array $namespace_aliases = array()
    ) {
        // only set the namespace if overridden
        if (is_string($namespace)) {
            $this->setNamespace($namespace);
        }
        $this->namespace_aliases = $namespace_aliases;

        foreach ($types as $type) {
            $this->add($type);
        }
    }

    /**
     * Returns the namespace name used to expand relative namespaces with.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the namespace which is used to resolve types with relative
     * namespaces.
     *
     * Unless the name of the current namespace if default or global we make
     * sure the namespace is succeeded with a '\'. This makes it clear for
     * the processing functions that a leading slash is present.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        if ($namespace != 'default' && $namespace != 'global') {
            $namespace = self::OPERATOR_NAMESPACE
                . trim($namespace, self::OPERATOR_NAMESPACE)
                . self::OPERATOR_NAMESPACE;
        } else {
            $namespace = '\\';
        }

        $this->namespace = $namespace;
    }

    /**
     * Returns the list of namespace aliases used to expand the typed with.
     *
     * @return string[] An associative array of Alias => Fully Qualified
     *     Namespace Names.
     */
    public function getNamespaceAliases()
    {
        return $this->namespace_aliases;
    }

    /**
     * Sets the namespace aliases to expand the added types.
     *
     * @param string[] $namespace_aliases An associative array of Alias => Fully
     *     Qualified Namespace Names.
     *
     * @return void
     */
    public function setNamespaceAliases($namespace_aliases)
    {
        $this->namespace_aliases = $namespace_aliases;
    }

    /**
     * Adds a new type to the collection and expands it if it contains a
     * relative namespace.
     *
     * If a class in the type contains a relative namespace than this collection
     * will try to expand that into a FQCN.
     *
     * @param string $type A 'Type' as defined in the phpDocumentor
     *     documentation.
     *
     * @throws \InvalidArgumentException if a non-string argument is passed.
     *
     * @see http://phpdoc.org/docs/latest/for-users/types.html for the
     *     definition of a type.
     *
     * @return void
     */
    public function add($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'A type should be represented by a string, received: '
                .var_export($type, true)
            );
        }

        // separate the type by the OR operator
        $type_parts = explode(self::OPERATOR_OR, $type);
        foreach ($type_parts as $part) {
            $expanded_type = $this->expand($part);
            if ($expanded_type) {
                $this[] = $expanded_type;
            }
        }
    }

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
     * @uses getNamespace to determine with what to prefix the type name.
     * @uses getNamespaceAliases to check whether the first part of the relative
     *     type name should not be replaced with another namespace.
     *
     * @return string
     */
    protected function expand($type)
    {
        $type = trim($type);
        if (!$type) {
            return '';
        }

        if ($this->isTypeAnArray($type)) {
            return $this->expand(substr($type, 0, -2)).self::OPERATOR_ARRAY;
        }

        if ($this->isRelativeType($type) && !$this->isTypeAKeyword($type)) {
            $type_parts = explode(self::OPERATOR_NAMESPACE, $type);

            // if the first segment is not an alias; prepend namespace name and
            // return
            if (!isset($this->namespace_aliases[$type_parts[0]])) {
                return $this->getNamespace() . $type;
            }

            $type_parts[0] = $this->namespace_aliases[$type_parts[0]];
            $type = implode(self::OPERATOR_NAMESPACE, $type_parts);
        }

        return $type;
    }

    /**
     * Detects whether the given type represents an array.
     *
     * @param string $type A relative or absolute type as defined in the
     *     phpDocumentor documentation.
     *
     * @return bool
     */
    protected function isTypeAnArray($type)
    {
        return (substr($type, -2) == self::OPERATOR_ARRAY);
    }

    /**
     * Detects whether the given type represents a PHPDoc keyword.
     *
     * @param string $type A relative or absolute type as defined in the
     *     phpDocumentor documentation.
     *
     * @return bool
     */
    protected function isTypeAKeyword($type)
    {
        return in_array(strtolower($type), $this->keywords);
    }

    /**
     * Detects whether the given type represents a relative or absolute path.
     *
     * This method will detect keywords as being absolute; even though they are
     * not preceeded by a namespace separator.
     *
     * @param string $type A relative or absolute type as defined in the
     *     phpDocumentor documentation.
     *
     * @return bool
     */
    protected function isRelativeType($type)
    {
        return ($type[0] !== self::OPERATOR_NAMESPACE)
            || $this->isTypeAKeyword($type);
    }
}
