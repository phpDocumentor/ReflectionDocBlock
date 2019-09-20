<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;
use function preg_split;

/**
 * Reflection class for a {@}return tag in a Docblock.
 */
final class Return_ extends BaseTag implements Factory\StaticMethod
{
    /** @var string */
    protected $name = 'return';

    /** @var Type */
    private $type;

    public function __construct(Type $type, ?Description $description = null)
    {
        $this->type        = $type;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(
        string $body,
        ?TypeResolver $typeResolver = null,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ) : self {
        Assert::notNull($typeResolver);
        Assert::notNull($descriptionFactory);

        $parts = preg_split('/\s+/Su', $body, 2);
        Assert::isArray($parts);

        $type        = $typeResolver->resolve($parts[0] ?? '', $context);
        $description = $descriptionFactory->create($parts[1] ?? '', $context);

        return new static($type, $description);
    }

    /**
     * Returns the type section of the variable.
     */
    public function getType() : Type
    {
        return $this->type;
    }

    public function __toString() : string
    {
        return $this->type . ' ' . $this->description;
    }
}
