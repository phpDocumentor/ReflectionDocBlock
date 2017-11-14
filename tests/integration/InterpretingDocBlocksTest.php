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
    public function tearDown()
    {
        m::close();
    }

    public function testInterpretingASimpleDocBlock()
    {
        /**
         * @var DocBlock    $docblock
         * @var string      $summary
         * @var Description $description
         */
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
        $this->assertSame($descriptionText, $description->render());
        $this->assertEmpty($docblock->getTags());
    }

    public function testInterpretingTags()
    {
        /**
         * @var DocBlock $docblock
         * @var boolean  $hasSeeTag
         * @var Tag[]    $tags
         * @var See[]    $seeTags
         */
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

    public function testDescriptionsCanEscapeAtSignsAndClosingBraces()
    {
        /**
         * @var string      $docComment
         * @var DocBlock    $docblock
         * @var Description $description
         * @var string      $receivedDocComment
         * @var string      $foundDescription
         */

        include(__DIR__ . '/../../examples/playing-with-descriptions/02-escaping.php');
        $this->assertSame(
            <<<'DESCRIPTION'
You can escape the @-sign by surrounding it with braces, for example: @. And escape a closing brace within an
inline tag by adding an opening brace in front of it like this: }.

Here are example texts where you can see how they could be used in a real life situation:

    This is a text with an {@internal inline tag where a closing brace (}) is shown}.
    Or an {@internal inline tag with a literal {@link} in it}.

Do note that an {@internal inline tag that has an opening brace ({) does not break out}.
DESCRIPTION
            ,
            $foundDescription
        );
    }
}
