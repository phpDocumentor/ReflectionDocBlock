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
use phpDocumentor\Reflection\DocBlock\Description;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class DocblocksWithIncompleteAnnotationsTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function testDocblockWithIncompleteAnnotations(): void
    {
        $docComment = <<<DOCCOMMENT
            /**
     * Hello just a test
     *
     * @var
     */
DOCCOMMENT;

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);

        $this::assertCount(1, $docblock->getTags());

        foreach ($docblock->getTags() as $index => $tag) {
            $this::assertNull($tag->getDescription());
        }
    }
}
