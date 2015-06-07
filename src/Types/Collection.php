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

use phpDocumentor\Reflection\Types\Resolver;
use phpDocumentor\Reflection\DocBlock\Context;

/**
 * Collection
 */
class Collection extends \ArrayObject
{
    /** @var string Definition of the OR operator for types */
    const OPERATOR_OR = '|';

    /**
     * Current invoking location.
     *
     * This is used to prepend to type with a relative location.
     * May also be 'default' or 'global', in which case they are ignored.
     *
     * @var Context
     */
    protected $context = null;
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * Registers the namespace and aliases; uses that to add and expand the given types.
     *
     * @param string[] $types Array containing a list of types to add to this container.
     * @param Context $context
     * @param Resolver $resolver
     */
    public function __construct(array $types = array(), Context $context = null, Resolver $resolver = null)
    {
        $this->context = $context ?: new Context('');
        $this->resolver = $resolver ?: new Resolver();

        foreach ($types as $type) {
            $this->add($type);
        }
    }

    /**
     * Returns the current invoking location.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
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
                . var_export($type, true)
            );
        }

        // separate the type by the OR operator
        $type_parts = explode(self::OPERATOR_OR, $type);
        foreach ($type_parts as $part) {
            $expanded_type = $this->resolver->resolve($part, $this->context);
            if ($expanded_type) {
                $this[] = $expanded_type;
            }
        }
    }

    /**
     * Returns a string representation of the collection.
     *
     * @return string The resolved types across the collection, separated with
     *     {@link self::OPERATOR_OR}.
     */
    public function __toString()
    {
        return implode(self::OPERATOR_OR, $this->getArrayCopy());
    }
}
