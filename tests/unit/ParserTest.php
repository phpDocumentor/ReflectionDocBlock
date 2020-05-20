<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use \phpDocumentor\Reflection\AST;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @dataProvider docblockProvider
     */
    public function testParsesDocblock(string $docblock, AST\Docblock $expectedAst) : void
    {
        $parser = new Parser();

        $ast = $parser->parse($docblock);

        $this->assertEquals($expectedAst, $ast);
    }

    public function docblockProvider()
    {
        return [
            'empty singleLine docblock' => [
                "/** */",
                new AST\Docblock(),
            ],
            'empty docblock' => [
                <<<DOCBLOCK
/**
 */
DOCBLOCK
                ,
                new AST\Docblock(),
            ],
            'simple tag' => [
                <<<DOCBLOCK
/**
 * @var
 */
DOCBLOCK
                ,
                new AST\Docblock(null, new AST\Tag('var')),
            ],
            [
                <<<DOCBLOCK
/**
 * @var:unittest
 */
DOCBLOCK
                ,
                new AST\Docblock(null, new AST\Tag('var', 'unittest')),
            ],
            [
                <<<DOCBLOCK
/**
 * This is a docblock
 */
DOCBLOCK
                ,
                new AST\Docblock('This is a docblock'),
            ],
        ];
    }
}
