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
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\See;

/**
 * @coversNothing
 */
class UsingTagsTest extends \PHPUnit_Framework_TestCase
{
    public function testAddingYourOwnTagUsingAStaticMethodAsFactory()
    {
        /**
         * @var object[] $customTagObjects
         * @var string   $docComment
         * @var string   $reconstitutedDocComment
         */
        include(__DIR__ . '/../../examples/04-adding-your-own-tag.php');

        $this->assertInstanceOf(\MyTag::class, $customTagObjects[0]);
        $this->assertSame('my-tag', $customTagObjects[0]->getName());
        $this->assertSame('I have a description', (string)$customTagObjects[0]->getDescription());
        $this->assertSame($docComment, $reconstitutedDocComment);
    }
}
