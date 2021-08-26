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
use phpDocumentor\Reflection\DocBlock\Tags\See;
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
        $this->assertSame('\\' . StandardTagFactory::class, (string) $seeTag->getReference());
        $this->assertSame('', (string) $seeTag->getDescription());
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
}
