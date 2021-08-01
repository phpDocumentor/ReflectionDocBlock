<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class DocblockSeeTagResolvingTest extends TestCase
{
    public function testResolvesSeeFQSENOfInlineTags()
    {
        $context = new Context('\Project\Sub\Level', ['Issue2425B' => '\Project\Other\Level\Issue2425B', 'Aliased' => 'Project\Other\Level\Issue2425C']);
        $docblockString = <<<DOCBLOCK
/**
 * Class summary.
 *
 * A description containing an inline {@see Issue2425B::bar()} tag
 * to a class inside of the project referenced via a use statement.
 *
 * And here is another inline {@see Aliased::bar()} tag to a class
 * aliased via a use statement.
 */
DOCBLOCK;



        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($docblockString, $context);

        /** @var See $see1 */
        $see1 = $docblock->getDescription()->getTags()[0];

        $this->assertSame('\Project\Other\Level\Issue2425B::bar()', (string)$see1->getReference());
    }
}
