<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
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
    public function tearDown(): void
    {
        m::close();
    }

    public function testDocblockWithAnnotations(): void
    {
        $docComment = <<<DOCCOMMENT
            /**
     * @var \DateTime[]
     * @Groups({"a", "b"})
     * @ORM\Entity
     */
DOCCOMMENT;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        $this->assertCount(3, $docblock->getTags());
    }

    public function testDocblockWithAnnotationHavingZeroValue(): void
    {
        $docComment = <<<DOCCOMMENT
            /**
     * @my-tag 0
     */
DOCCOMMENT;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        $this->assertSame(0, (int) $docblock->getTagsByName('my-tag')[0]->render());
    }
}
