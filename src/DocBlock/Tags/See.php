<?php declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Reflection class for an {@}see tag in a Docblock.
 */
class See extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'see';

    /** @var Reference */
    protected $refers;

    /**
     * Initializes this tag.
     */
    public function __construct(Reference $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(
        string $body,
        ?FqsenResolver $resolver = null,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ): self {
        Assert::allNotNull([$resolver, $descriptionFactory]);

        $parts = preg_split('/\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;

        // https://tools.ietf.org/html/rfc2396#section-3
        if (preg_match('/\w:\/\/\w/i', $parts[0])) {
            return new static(new Url($parts[0]), $description);
        }

        return new static(new FqsenRef($resolver->resolve($parts[0], $context)), $description);
    }

    /**
     * Returns the ref of this tag.
     */
    public function getReference(): Reference
    {
        return $this->refers;
    }

    /**
     * Returns a string representation of this tag.
     */
    public function __toString(): string
    {
        return $this->refers . ($this->description ? ' ' . $this->description->render() : '');
    }
}
