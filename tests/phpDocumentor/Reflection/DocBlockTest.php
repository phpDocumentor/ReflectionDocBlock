<?php
/**
 * phpDocumentor DocBlock Test
 *
 * PHP Version 5
 *
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection;

require_once __DIR__.'/../../../src/phpDocumentor/Reflection/DocBlock.php';
require_once __DIR__.'/../../../src/phpDocumentor/Reflection/DocBlock/LongDescription.php';

/**
 * Test class for phpDocumentor_Reflection_DocBlock
 *
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description.
 *
 * This is a long description.
 *
 * @see \MyClass
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.', $object->getShortDescription()
        );
        $this->assertEquals(
            'This is a long description.', $object->getLongDescription()->getContents()
        );
        $this->assertEquals(2, count($object->getTags()));
        $this->assertTrue($object->hasTag('see'));
        $this->assertTrue($object->hasTag('return'));
    }

    public function testDotSeperation()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description. This is a long description.
 * This is a continuation of the long description.
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.', $object->getShortDescription()
        );
        $this->assertEquals(
            "This is a long description.\nThis is a continuation of the long description.", $object->getLongDescription()->getContents()
	        );
    }
}

