<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Covers;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\AbstractPHPStanFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ExtendsFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ImplementsFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\MixinFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyWriteFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ReturnFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\TemplateCovariantFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\TemplateFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ThrowsFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory;
use phpDocumentor\Reflection\DocBlock\Tags\ExpectedFormat;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\DocBlock\Tags\Link as LinkTag;
use phpDocumentor\Reflection\DocBlock\Tags\See as SeeTag;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use phpDocumentor\Reflection\DocBlock\Tags\Source;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
use phpDocumentor\Reflection\DocBlock\Tags\Version;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_merge;
use function array_slice;
use function call_user_func_array;
use function get_class;
use function is_object;
use function is_string;
use function is_subclass_of;
use function preg_match;
use function sprintf;
use function strpos;
use function trim;

/**
 * Creates a Tag object given the contents of a tag.
 *
 * This Factory is capable of determining the appropriate class for a tag and instantiate it using its `create`
 * factory method. The `create` factory method of a Tag can have a variable number of arguments; this way you can
 * pass the dependencies that you need to construct a tag object.
 *
 * > Important: each parameter in addition to the body variable for the `create` method must default to null, otherwise
 * > it violates the constraint with the interface; it is recommended to use the {@see Assert::notNull()} method to
 * > verify that a dependency is actually passed.
 *
 * This Factory also features a Service Locator component that is used to pass the right dependencies to the
 * `create` method of a tag; each dependency should be registered as a service or as a parameter.
 *
 * When you want to use a Tag of your own with custom handling you need to call the `registerTagHandler` method, pass
 * the name of the tag and a Fully Qualified Class Name pointing to a class that implements the Tag interface.
 */
final class StandardTagFactory implements TagFactory
{
    /** PCRE regular expression matching a tag name. */
    public const REGEX_TAGNAME = '[\w\-\_\\\\:]+';

    /**
     * @var array<string, class-string<Tag>|Tag|Factory> An array with a tag as a key, and an
     *                               FQCN to a class that handles it as an array value.
     */
    private array $tagHandlerMappings = [
        'author'             => Author::class,
        'covers'             => Covers::class,
        'deprecated'         => Deprecated::class,
        'link'               => LinkTag::class,
        'see'                => SeeTag::class,
        'since'              => Since::class,
        'source'             => Source::class,
        'uses'               => Uses::class,
        'version'            => Version::class,
    ];

    /**
     * @var array<class-string<Tag>> An array with an annotation as a key, and an
     *      FQCN to a class that handles it as an array value.
     */
    private array $annotationMappings = [];

    /**
     * @var ReflectionParameter[][] a lazy-loading cache containing parameters
     *      for each tagHandler that has been used.
     */
    private array $tagHandlerParameterCache = [];

    private FqsenResolver $fqsenResolver;

    /**
     * @var mixed[] an array representing a simple Service Locator where we can store parameters and
     *     services that can be inserted into the Factory Methods of Tag Handlers.
     */
    private array $serviceLocator = [];

    private function __construct(FqsenResolver $fqsenResolver)
    {
        $this->fqsenResolver = $fqsenResolver;

        $this->addService($fqsenResolver, FqsenResolver::class);
    }

    /**
     * Initialize this tag factory with the means to resolve an FQSEN.
     *
     * @see self::registerTagHandler() to add a new tag handler to the existing default list.
     */
    public static function createInstance(FqsenResolver $fqsenResolver): self
    {
        $tagFactory = new self($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);

        $typeResolver = new TypeResolver($fqsenResolver);

        $phpstanTagFactory = new AbstractPHPStanFactory(
            new ParamFactory($typeResolver, $descriptionFactory),
            new VarFactory($typeResolver, $descriptionFactory),
            new ReturnFactory($typeResolver, $descriptionFactory),
            new PropertyFactory($typeResolver, $descriptionFactory),
            new PropertyReadFactory($typeResolver, $descriptionFactory),
            new PropertyWriteFactory($typeResolver, $descriptionFactory),
            new MethodFactory($typeResolver, $descriptionFactory),
            new MixinFactory($typeResolver, $descriptionFactory),
            new ImplementsFactory($typeResolver, $descriptionFactory),
            new ExtendsFactory($typeResolver, $descriptionFactory),
            new TemplateFactory($typeResolver, $descriptionFactory),
            new TemplateCovariantFactory($typeResolver, $descriptionFactory),
            new ThrowsFactory($typeResolver, $descriptionFactory),
        );

        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService($typeResolver);
        $tagFactory->registerTagHandler('param', $phpstanTagFactory);
        $tagFactory->registerTagHandler('var', $phpstanTagFactory);
        $tagFactory->registerTagHandler('return', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property-read', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property-write', $phpstanTagFactory);
        $tagFactory->registerTagHandler('method', $phpstanTagFactory);
        $tagFactory->registerTagHandler('mixin', $phpstanTagFactory);
        $tagFactory->registerTagHandler('extends', $phpstanTagFactory);
        $tagFactory->registerTagHandler('implements', $phpstanTagFactory);
        $tagFactory->registerTagHandler('template', $phpstanTagFactory);
        $tagFactory->registerTagHandler('template-covariant', $phpstanTagFactory);
        $tagFactory->registerTagHandler('template-extends', $phpstanTagFactory);
        $tagFactory->registerTagHandler('template-implements', $phpstanTagFactory);
        $tagFactory->registerTagHandler('throws', $phpstanTagFactory);

        return $tagFactory;
    }

    public function create(string $tagLine, ?TypeContext $context = null): Tag
    {
        if (!$context) {
            $context = new TypeContext('');
        }

        [$tagName, $tagBody] = $this->extractTagParts($tagLine);

        return $this->createTag(trim($tagBody), $tagName, $context);
    }

    /**
     * @param mixed $value
     */
    public function addParameter(string $name, $value): void
    {
        $this->serviceLocator[$name] = $value;
    }

    public function addService(object $service, ?string $alias = null): void
    {
        $this->serviceLocator[$alias ?? get_class($service)] = $service;
    }

    /** {@inheritDoc} */
    public function registerTagHandler(string $tagName, $handler): void
    {
        Assert::stringNotEmpty($tagName);
        if (strpos($tagName, '\\') !== false && $tagName[0] !== '\\') {
            throw new InvalidArgumentException(
                'A namespaced tag must have a leading backslash as it must be fully qualified'
            );
        }

        if (is_object($handler)) {
            Assert::isInstanceOf($handler, Factory::class);
            $this->tagHandlerMappings[$tagName] = $handler;

            return;
        }

        Assert::classExists($handler);
        Assert::implementsInterface($handler, Tag::class);
        $this->tagHandlerMappings[$tagName] = $handler;
    }

    /**
     * Extracts all components for a tag.
     *
     * @return string[]
     */
    private function extractTagParts(string $tagLine): array
    {
        $matches = [];
        if (!preg_match('/^@(' . self::REGEX_TAGNAME . ')((?:[\s\(\{])\s*([^\s].*)|$)/us', $tagLine, $matches)) {
            throw new InvalidArgumentException(
                'The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors'
            );
        }

        return array_slice($matches, 1);
    }

    /**
     * Creates a new tag object with the given name and body or returns null if the tag name was recognized but the
     * body was invalid.
     */
    private function createTag(string $body, string $name, TypeContext $context): Tag
    {
        $handlerClassName = $this->findHandlerClassName($name, $context);
        $arguments        = $this->getArgumentsForParametersFromWiring(
            $this->fetchParametersForHandlerFactoryMethod($handlerClassName),
            $this->getServiceLocatorWithDynamicParameters($context, $name, $body)
        );

        if (array_key_exists('tagLine', $arguments)) {
            $arguments['tagLine'] = sprintf('@%s %s', $name, $body);
        }

        try {
            $callable = [$handlerClassName, 'create'];
            Assert::isCallable($callable);
            /** @phpstan-var callable(string): ?Tag $callable */
            $tag = call_user_func_array($callable, $arguments);

            return $tag ?? $this->createInvalidTag($handlerClassName, $body, $name);
        } catch (InvalidArgumentException $e) {
            return $this->createInvalidTag($handlerClassName, $body, $name)->withError($e);
        }
    }

    /**
     * @param class-string<Tag>|Tag|Factory $handlerClassName
     */
    private function createInvalidTag($handlerClassName, string $body, string $name): InvalidTag
    {
        $invalidTag = InvalidTag::create($body, $name);

        if (is_string($handlerClassName) && is_subclass_of($handlerClassName, ExpectedFormat::class)) {
            $invalidTag = $invalidTag->withFormatHint(
                $handlerClassName::getExpectedFormat(),
                $handlerClassName::getDocumentationUrl()
            );
        }

        return $invalidTag;
    }

    /**
     * Determines the Fully Qualified Class Name of the Factory or Tag (containing a Factory Method `create`).
     *
     * @return class-string<Tag>|Tag|Factory
     */
    private function findHandlerClassName(string $tagName, TypeContext $context)
    {
        $handlerClassName = Generic::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handlerClassName = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            // TODO: Annotation support is planned for a later stage and as such is disabled for now
            $tagName = (string) $this->fqsenResolver->resolve($tagName, $context);
            if (isset($this->annotationMappings[$tagName])) {
                $handlerClassName = $this->annotationMappings[$tagName];
            }
        }

        return $handlerClassName;
    }

    /**
     * Retrieves the arguments that need to be passed to the Factory Method with the given Parameters.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $locator
     *
     * @return mixed[] A series of values that can be passed to the Factory Method of the tag whose parameters
     *     is provided with this method.
     */
    private function getArgumentsForParametersFromWiring(array $parameters, array $locator): array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $type     = $parameter->getType();
            $typeHint = null;
            if ($type instanceof ReflectionNamedType) {
                $typeHint = $type->getName();
                if ($typeHint === 'self') {
                    $declaringClass = $parameter->getDeclaringClass();
                    if ($declaringClass !== null) {
                        $typeHint = $declaringClass->getName();
                    }
                }
            }

            $parameterName = $parameter->getName();
            if (isset($locator[$typeHint ?? ''])) {
                $arguments[$parameterName] = $locator[$typeHint ?? ''];
                continue;
            }

            if (isset($locator[$parameterName])) {
                $arguments[$parameterName] = $locator[$parameterName];
                continue;
            }

            $arguments[$parameterName] = null;
        }

        return $arguments;
    }

    /**
     * Retrieves a series of ReflectionParameter objects for the static 'create' method of the given
     * tag handler class name.
     *
     * @param class-string<Tag>|Tag|Factory $handler
     *
     * @return ReflectionParameter[]
     */
    private function fetchParametersForHandlerFactoryMethod($handler): array
    {
        $handlerClassName = is_object($handler) ? get_class($handler) : $handler;

        if (!isset($this->tagHandlerParameterCache[$handlerClassName])) {
            $methodReflection                                  = new ReflectionMethod($handlerClassName, 'create');
            $this->tagHandlerParameterCache[$handlerClassName] = $methodReflection->getParameters();
        }

        return $this->tagHandlerParameterCache[$handlerClassName];
    }

    /**
     * Returns a copy of this class' Service Locator with added dynamic parameters,
     * such as the tag's name, body and Context.
     *
     * @param TypeContext $context The Context (namespace and aliases) that may be
     *  passed and is used to resolve FQSENs.
     * @param string      $tagName The name of the tag that may be
     *  passed onto the factory method of the Tag class.
     * @param string      $tagBody The body of the tag that may be
     *  passed onto the factory method of the Tag class.
     *
     * @return mixed[]
     */
    private function getServiceLocatorWithDynamicParameters(
        TypeContext $context,
        string $tagName,
        string $tagBody
    ): array {
        return array_merge(
            $this->serviceLocator,
            [
                'name' => $tagName,
                'body' => $tagBody,
                TypeContext::class => $context,
            ]
        );
    }

    /**
     * Returns whether the given tag belongs to an annotation.
     *
     * @todo this method should be populated once we implement Annotation notation support.
     */
    private function isAnnotation(string $tagContent): bool
    {
        // 1. Contains a namespace separator
        // 2. Contains parenthesis
        // 3. Is present in a list of known annotations (make the algorithm smart by first checking is the last part
        //    of the annotation class name matches the found tag name

        return false;
    }
}
