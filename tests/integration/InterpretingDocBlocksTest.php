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

namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use phpDocumentor\Reflection\PseudoTypes\ConstExpression;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class InterpretingDocBlocksTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function testInterpretingSummaryWithEllipsis(): void
    {
        $docblock = <<<DOCBLOCK
/**
 * This is a short (...) description.
 *
 * This is a long description.
 *
 * @return void
 */
DOCBLOCK;

        $factory = DocBlockFactory::createInstance();
        $phpdoc = $factory->create($docblock);

        $summary = 'This is a short (...) description.';
        $description = 'This is a long description.';

        $this->assertInstanceOf(DocBlock::class, $phpdoc);
        $this->assertSame($summary, $phpdoc->getSummary());
        $this->assertSame($description, $phpdoc->getDescription()->render());
        $this->assertCount(1, $phpdoc->getTags());
        $this->assertTrue($phpdoc->hasTag('return'));
    }

    public function testInterpretingASimpleDocBlock(): void
    {
        /** @var DocBlock $docblock */
        $docblock;
        /** @var string $summary */
        $summary;
        /** @var Description $description */
        $description;

        include(__DIR__ . '/../../examples/01-interpreting-a-simple-docblock.php');

        $descriptionText = <<<DESCRIPTION
This is a Description. A Summary and Description are separated by either
two subsequent newlines (thus a whiteline in between as can be seen in this
example), or when the Summary ends with a dot (`.`) and some form of
whitespace.
DESCRIPTION;

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is an example of a summary.', $summary);
        $this->assertInstanceOf(Description::class, $description);
        $this->assertSame(
            str_replace(
                PHP_EOL,
                "\n",
                $descriptionText
            ),
            $description->render()
        );
        $this->assertEmpty($docblock->getTags());
    }

    public function testInterpretingTags(): void
    {
        /** @var DocBlock $docblock */
        $docblock;
        /** @var boolean $hasSeeTag */
        $hasSeeTag;
        /** @var Tag[] $tags */
        $tags;
        /** @var See[] $seeTags */
        $seeTags;

        include(__DIR__ . '/../../examples/02-interpreting-tags.php');

        $this->assertTrue($hasSeeTag);
        $this->assertCount(1, $tags);
        $this->assertCount(1, $seeTags);

        $this->assertInstanceOf(See::class, $tags[0]);
        $this->assertInstanceOf(See::class, $seeTags[0]);

        $seeTag = $seeTags[0];
        $this->assertSame('\\' . StandardTagFactory::class, (string)$seeTag->getReference());
        $this->assertSame('', (string)$seeTag->getDescription());
    }

    public function testDescriptionsCanEscapeAtSignsAndClosingBraces(): void
    {
        /** @var string $docComment */
        $docComment;
        /** @var DocBlock $docblock */
        $docblock;
        /** @var Description $description */
        $description;
        /** @var string $receivedDocComment */
        $receivedDocComment;
        /** @var string $foundDescription */
        $foundDescription;

        include(__DIR__ . '/../../examples/playing-with-descriptions/02-escaping.php');

        $this->assertSame(
            str_replace(
                PHP_EOL,
                "\n",
                <<<'DESCRIPTION'
You can escape the @-sign by surrounding it with braces, for example: @. And escape a closing brace within an
inline tag by adding an opening brace in front of it like this: }.

Here are example texts where you can see how they could be used in a real life situation:

    This is a text with an {@internal inline tag where a closing brace (}) is shown}.
    Or an {@internal inline tag with a literal {@link} in it}.

Do note that an {@internal inline tag that has an opening brace ({) does not break out}.
DESCRIPTION
            ),
            $foundDescription
        );
    }

    public function testMultilineTags(): void
    {
        $docCommment = <<<DOC
/**
 * This is an example of a summary.
 *
 * @param array<
 *   int,
 *   string     
 * > \$store
 */
DOC;
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docCommment);

        self::assertEquals(
            [
                new Param(
                    'store',
                    new Array_(
                        new String_(),
                        new Integer()
                    ),
                    false,
                    new Description(''),
                ),
            ],
            $docblock->getTags()
        );

    }

    public function testMethodRegression(): void
    {
        $docCommment = <<<DOC
/**
 * This is an example of a summary.
 *
 * @method void setInteger(integer \$integer)
 */
DOC;
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docCommment);

        self::assertEquals(
            [
                new Method(
                    'setInteger',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [
                        new MethodParameter('integer', new Integer())
                    ]
                ),
            ],
            $docblock->getTags()
        );
    }

    public function testInvalidTypeParamResultsInInvalidTag(): void
    {
        $docCommment = '
/**
 * This is an example of a summary.
 *
 * @param array\Foo> $test
 */
';
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docCommment);

        self::assertEquals(
            [
                InvalidTag::create(
                    'array\Foo> $test',
                    'param',
                )->withError(
                    new \InvalidArgumentException(
                        'Could not find type in array\Foo> $test, please check for malformed notations')
                ),
            ],
            $docblock->getTags()
        );
    }

    public function testConstantReferenceTypes(): void
    {
        $docCommment = <<<DOC
    /**
     * @return self::STATUS_*
     */
DOC;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docCommment);

        self::assertEquals(
            [
                new Return_(new ConstExpression(new Self_(), 'STATUS_*'), new Description('')),
            ],
            $docblock->getTags()
        );
    }

    public function testRegressionWordpressDocblocks(): void
    {
        $docCommment = <<<DOC
    /**
     * Install a package.
     *
     * Copies the contents of a package from a source directory, and installs them in
     * a destination directory. Optionally removes the source. It can also optionally
     * clear out the destination folder if it already exists.
     *
     * @since 2.8.0
     * @since 6.2.0 Use move_dir() instead of copy_dir() when possible.
     *
     * @global WP_Filesystem_Base \$wp_filesystem        WordPress filesystem subclass.
     * @global array              \$wp_theme_directories
     *
     * @param array|string \$args {
     *     Optional. Array or string of arguments for installing a package. Default empty array.
     *
     *     @type string \$source                      Required path to the package source. Default empty.
     *     @type string \$destination                 Required path to a folder to install the package in.
     *                                               Default empty.
     *     @type bool   \$clear_destination           Whether to delete any files already in the destination
     *                                               folder. Default false.
     *     @type bool   \$clear_working               Whether to delete the files from the working directory
     *                                               after copying them to the destination. Default false.
     *     @type bool   \$abort_if_destination_exists Whether to abort the installation if
     *                                               the destination folder already exists. Default true.
     *     @type array  \$hook_extra                  Extra arguments to pass to the filter hooks called by
     *                                               WP_Upgrader::install_package(). Default empty array.
     * }
     *
     * @return array|WP_Error The result (also stored in `WP_Upgrader::\$result`), or a WP_Error on failure.
     */
DOC;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docCommment);

        self::assertEquals(
            new DocBlock(
                'Install a package.',
                new Description(
                    'Copies the contents of a package from a source directory, and installs them in' . PHP_EOL .
                    'a destination directory. Optionally removes the source. It can also optionally' . PHP_EOL .
                    'clear out the destination folder if it already exists.'
                ),
                [
                    new Since('2.8.0', new Description('')),
                    new Since('6.2.0', new Description('Use move_dir() instead of copy_dir() when possible.')),
                    new Generic(
                        'global',
                        new Description('WP_Filesystem_Base $wp_filesystem        WordPress filesystem subclass.')
                    ),
                    new Generic(
                        'global',
                        new Description('array              $wp_theme_directories')
                    ),
                    new Param(
                        'args',
                        new Compound([new Array_(new Mixed_()), new String_()]),
                        false,
                        new Description(
                            '{' . "\n" .
                            "\n" .
                            '    Optional. Array or string of arguments for installing a package. Default empty array.' . "\n" .
                            "\n" .
                            '    @type string $source                      Required path to the package source. Default empty.' . "\n" .
                            '    @type string $destination                 Required path to a folder to install the package in.' . "\n" .
                            '                                              Default empty.' . "\n" .
                            '    @type bool   $clear_destination           Whether to delete any files already in the destination' . "\n" .
                            '                                              folder. Default false.' . "\n" .
                            '    @type bool   $clear_working               Whether to delete the files from the working directory' . "\n" .
                            '                                              after copying them to the destination. Default false.' . "\n" .
                            '    @type bool   $abort_if_destination_exists Whether to abort the installation if' . "\n" .
                            '                                              the destination folder already exists. Default true.' . "\n" .
                            '    @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by' . "\n" .
                            '                                              WP_Upgrader::install_package(). Default empty array.' . "\n" .
                            '}'
                        )
                    ),
                    new Return_(
                        new Compound([new Array_(new Mixed_()), new Object_(new Fqsen('\WP_Error'))]),
                        new Description('The result (also stored in `WP_Upgrader::$result`), or a WP_Error on failure.')
                    ),
                ],
                new Context('\\')
            ),
            $docblock
        );
    }

    public function testIndentationIsKept(): void
    {
        $docComment = <<<DOC
	/**
	 * Registers the script module if no script module with that script module
	 * identifier has already been registered.
	 *
	 * @since 6.5.0
	 *
	 * @param array             \$deps     {
	 *                                        Optional. List of dependencies.
	 *
	 *                                        @type string|array ...$0 {
	 *                                            An array of script module identifiers of the dependencies of this script
	 *                                            module. The dependencies can be strings or arrays. If they are arrays,
	 *                                            they need an `id` key with the script module identifier, and can contain
	 *                                            an `import` key with either `static` or `dynamic`. By default,
	 *                                            dependencies that don't contain an `import` key are considered static.
	 *
	 *                                            @type string \$id     The script module identifier.
	 *                                            @type string \$import Optional. Import type. May be either `static` or
	 *                                                                 `dynamic`. Defaults to `static`.
	 *                                        }
	 *                                    }
	 */
DOC;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        self::assertEquals(
            new DocBlock(
                'Registers the script module if no script module with that script module
identifier has already been registered.',
                new Description(
                    ''
                ),
                [
                    new Since('6.5.0', new Description('')),
                    new Param(
                        'deps',
                        new Array_(new Mixed_()),
                        false,
                        new Description("{

    Optional. List of dependencies.

    @type string|array ...$0 {
        An array of script module identifiers of the dependencies of this script
        module. The dependencies can be strings or arrays. If they are arrays,
        they need an `id` key with the script module identifier, and can contain
        an `import` key with either `static` or `dynamic`. By default,
        dependencies that don't contain an `import` key are considered static.

        @type string \$id     The script module identifier.
        @type string \$import Optional. Import type. May be either `static` or
                             `dynamic`. Defaults to `static`.
    }
}"
                        )
                    ),
                ],
                new Context('\\')
            ),
            $docblock
        );
    }
}
