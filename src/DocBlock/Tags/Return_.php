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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Type\Collection;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;

/**
 * Reflection class for a @return tag in a Docblock.
 */
final class Return_ extends BaseTag
{
    protected $name = 'return';

    /** @var Type */
    private $type;

    public function __construct(Type $type, Description $description = null)
    {
        $this->description = $description;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        Context $context = null
    )
    {
        $parts = preg_split('/\s+/Su', $body, 2);

        $type = $typeResolver->resolve(isset($parts[0]) ? $parts[0] : '', $context);
        $description = $descriptionFactory->create(isset($parts[1]) ? $parts[1] : '');

        return new static($type, $description);
    }

    public function __toString()
    {
        return $this->type . ' ' . $this->description;
    }

    /**
     * Returns the type section of the variable.
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }
}
