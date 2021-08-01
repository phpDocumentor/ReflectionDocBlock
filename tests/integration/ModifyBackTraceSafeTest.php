<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;


use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ModifyBackTraceSafeTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testBackTraceModificationDoesNotImpactFunctionArguments()
    {
        $traverser = new Traverser();
        $node1 = new Node();
        $node1->children[] = new Node();
        $node1->children[] = new Node();

        $traverser->traverse([new Node(), $node1]);
    }
}


class Node {
    public $children = [];
}

class Traverser
{
    public function traverse(array $nodes)
    {
        $this->traverseArray($nodes);
    }

    public function traverseArray(array $nodes): array
    {
        $doNodes = [];

        foreach ($nodes as &$node) {
            $node = $this->callback($node);
            $node = $this->traverseNode($node);

            $doNodes[] = $node;
        }

        return $doNodes;
    }

    public function callback(Node $class) : Node
    {
        $docblock = <<<DOCBLOCK
 /**
  * @see sql.php
  */
DOCBLOCK;

        $factor = DocBlockFactory::createInstance();

        $factor->create($docblock);

        return $class;
    }

    private function traverseNode(Node $node) : Node
    {
        if ($node->children) {
            $this->traverseArray($node->children);
        }

        return $node;
    }
}
