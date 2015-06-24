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

use phpDocumentor\Reflection\DocBlock\Description;

/**
 * @coversNothing
 */
class InterpretingDocBlocksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @link ../../examples/interpreting-a-simple-docblock.php
     */
    public function testInterpretingASimpleDocBlock()
    {
        /**
         * @var DocBlock    $docblock
         * @var string      $summary
         * @var Description $description
         */
        include(__DIR__ . '/../../examples/interpreting-a-simple-docblock.php');

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
}
