<?php
/**
 * In this example we demonstrate how you can add your own Tag using a Static Factory method in your Tag class.
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Types\Context;
use Webmozart\Assert\Assert;

/**
 * An example of a custom tag called `my-tag` with an optional description.
 *
 * A Custom Tag is a class that can consist of two parts:
 *
 * 1. a method `create` that is a static factory for this class.
 * 2. methods and properties that have this object act as an immutable Value Object representing a Tag instance.
 *
 * The static factory `create` is used to convert a tag line (without the tag name) into an instance of the
 * same tag object with the right constructor parameters set. This method has a dynamic list of parameters so that you
 * can inject various dependencies, see the method's DocBlock for more information.
 *
 * An object of this class, and its methods and properties, represent a single instance of that tag in your
 * documentation in the form of a Value Object whose properties should not be changed after instantiation (it should be
 * immutable).
 *
 * > Important: Tag classes that act as Factories using the `create` method should implement the Tag interface.
 * > Instead, you could extend the abstract class BaseTag that already implements the Tag interface
 */
final class MyTag extends BaseTag
{
    /**
     * A required property that is used by Formatters to reconstitute the complete tag line.
     *
     * @see Formatter
     *
     * @var string
     */
    protected $name = 'my-tag';

    /**
     * The constructor for this Tag; this should contain all properties for this object.
     *
     * @param Description $description An example of how to add a Description to the tag; the Description is often
     *                                 an optional variable so passing null is allowed in this instance (though you can
     *                                 also construct an empty description object).
     *
     * @see BaseTag for the declaration of the description property and getDescription method.
     */
    public function __construct(Description $description = null)
    {
        $this->description = $description;
    }

    /**
     * A static Factory that creates a new instance of the current Tag.
     *
     * In this example the MyTag tag can be created by passing a description text as $body. Because we have added
     * a $descriptionFactory that is type-hinted as DescriptionFactory we can now construct a new Description object
     * and pass that to the constructor.
     *
     * > You could directly instantiate a Description object here but that won't be parsed for inline tags and Types
     * > won't be resolved. The DescriptionFactory will take care of those actions.
     *
     * The `create` method's interface states that this method only features a single parameter (`$body`) but the
     * {@see TagFactory} will read the signature of this method and if it has more parameters then it will try
     * to find declarations for it in the ServiceLocator of the TagFactory (see {@see TagFactory::$serviceLocator}).
     *
     * > Important: all properties following the `$body` should default to `null`, otherwise PHP will error because
     * > it no longer matches the interface. This is why you often see the default tags check that an optional argument
     * > is not null nonetheless.
     *
     * @param string             $body
     * @param DescriptionFactory $descriptionFactory
     * @param Context|null       $context The Context is used to resolve Types and FQSENs, although optional
     *                                    it is highly recommended to pass it. If you omit it then it is assumed that
     *                                    the DocBlock is in the global namespace and has no `use` statements.
     *
     * @see Tag for the interface declaration of the `create` method.
     * @see Tag::create() for more information on this method's workings.
     */
    public static function create(string $body, DescriptionFactory $descriptionFactory = null, Context $context = null): self
    {
        Assert::notNull($descriptionFactory);

        return new static($descriptionFactory->create($body, $context));
    }

    /**
     * Returns a rendition of the original tag line.
     *
     * This method is used to reconstitute a DocBlock into its original form by the {@see Serializer}. It should
     * feature all parts of the tag so that the serializer can put it back together.
     */
    public function __toString(): string
    {
        return (string)$this->description;
    }
}

$docComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * @my-tag I have a description
 */
DOCCOMMENT;

// Make a mapping between the tag name `my-tag` and the Tag class containing the Factory Method `create`.
$customTags = ['my-tag' => MyTag::class];

// Do pass the list of custom tags to the Factory for the DocBlockFactory.
$factory = DocBlockFactory::createInstance($customTags);
// You can also add Tags later using `$factory->registerTagHandler()` with a tag name and Tag class name.

// Create the DocBlock
$docblock = $factory->create($docComment);

// Take a look: the $customTagObjects now contain an array with your newly added tag
$customTagObjects = $docblock->getTagsByName('my-tag');

// As an experiment: let's reconstitute the DocBlock and observe that because we added a __toString() method
// to the tag class that we can now also see it.
$serializer              = new Serializer(0, '',true, null, null, PHP_EOL);
$reconstitutedDocComment = $serializer->getDocComment($docblock);
