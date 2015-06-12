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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use DocBlock\Types\Resolver;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for an {@}see tag in a Docblock.
 */
class See extends Tag
{
    /** @var Fqsen */
    protected $refers = null;

    /**
     * Initializes this tag.
     *
     * @param Fqsen $refers
     * @param Description $description
     */
    public function __construct(Fqsen $refers, Description $description)
    {
        $this->refers = $refers;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public static function create($content, Context $context)
    {
        $parts = preg_split('/\s+/Su', $content, 2);
        $resolver = new Resolver();

        return new static(
            $resolver->resolve($parts[0], $context),
            new Description(isset($parts[1]) ? $parts[1] : '', $context)
        );
    }

    /**
     * Returns the structural element this tag refers to.
     *
     * @return Fqsen
     */
    public function getReference()
    {
        return $this->refers;
    }

    /**
     * Returns a string representation of this tag.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->refers . ' ' . $this->description->render();
    }
}
