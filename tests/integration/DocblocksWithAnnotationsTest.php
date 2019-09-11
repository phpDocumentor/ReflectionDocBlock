<?php
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @copyright 2010-2017 Mike van Riel<mike@phpdoc.org>
 *  @license   http://www.opensource.org/licenses/mit-license.php MIT
 *  @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class DocblocksWithAnnotationsTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testDocblockWithAnnotations()
    {
        $docComment = <<<DOCCOMMENT
            /**
     * @var \DateTime[]
     * @Groups({"a", "b"})
     * @ORM\Entity
     */
DOCCOMMENT;

        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        $this->assertCount(3, $docblock->getTags());
    }

    public function testDocblockWithAnnotationHavingZeroValue()
    {
        $docComment = <<<DOCCOMMENT
            /**
     * @my-tag 0
     */
DOCCOMMENT;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        $this->assertSame(0, printf('%i', $docblock->getTagsByName('my-tag')));
    }
}
