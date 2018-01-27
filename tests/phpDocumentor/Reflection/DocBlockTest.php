<?php
/**
 * phpDocumentor DocBlock Test
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Location;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;

/**
 * Test class for phpDocumentor\Reflection\DocBlock
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock
     * 
     * @return void
     */
    public function testConstruct()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description
 *
 * This is a long description
 *
 * @see \MyClass
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock(
            $fixture,
            new Context('\MyNamespace', array('PHPDoc' => '\phpDocumentor')),
            new Location(2)
        );
        $this->assertEquals(
            'This is a short description',
            $object->getShortDescription()
        );
        $this->assertEquals(
            'This is a long description',
            $object->getLongDescription()->getContents()
        );
        $this->assertCount(2, $object->getTags());
        $this->assertTrue($object->hasTag('see'));
        $this->assertTrue($object->hasTag('return'));
        $this->assertFalse($object->hasTag('category'));
        
        $this->assertSame('MyNamespace', $object->getContext()->getNamespace());
        $this->assertSame(
            array('PHPDoc' => '\phpDocumentor'),
            $object->getContext()->getNamespaceAliases()
        );
        $this->assertSame(2, $object->getLocation()->getLineNumber());
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::splitDocBlock
     *
     * @return void
     */
    public function testConstructWithTagsOnly()
    {
        $fixture = <<<DOCBLOCK
/**
 * @see \MyClass
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals('', $object->getShortDescription());
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertCount(2, $object->getTags());
        $this->assertTrue($object->hasTag('see'));
        $this->assertTrue($object->hasTag('return'));
        $this->assertFalse($object->hasTag('category'));
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::isTemplateStart
     */
    public function testIfStartOfTemplateIsDiscovered()
    {
        $fixture = <<<DOCBLOCK
/**#@+
 * @see \MyClass
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals('', $object->getShortDescription());
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertCount(2, $object->getTags());
        $this->assertTrue($object->hasTag('see'));
        $this->assertTrue($object->hasTag('return'));
        $this->assertFalse($object->hasTag('category'));
        $this->assertTrue($object->isTemplateStart());
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::isTemplateEnd
     */
    public function testIfEndOfTemplateIsDiscovered()
    {
        $fixture = <<<DOCBLOCK
/**#@-*/
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals('', $object->getShortDescription());
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertTrue($object->isTemplateEnd());
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::cleanInput
     * 
     * @return void
     */
    public function testConstructOneLiner()
    {
        $fixture = '/** Short description and nothing more. */';
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'Short description and nothing more.',
            $object->getShortDescription()
        );
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertCount(0, $object->getTags());
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::__construct
     * 
     * @return void
     */
    public function testConstructFromReflector()
    {
        $object = new DocBlock(new \ReflectionClass($this));
        $this->assertEquals(
            'Test class for phpDocumentor\Reflection\DocBlock',
            $object->getShortDescription()
        );
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertCount(4, $object->getTags());
        $this->assertTrue($object->hasTag('author'));
        $this->assertTrue($object->hasTag('copyright'));
        $this->assertTrue($object->hasTag('license'));
        $this->assertTrue($object->hasTag('link'));
        $this->assertFalse($object->hasTag('category'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testExceptionOnInvalidObject()
    {
        new DocBlock($this);
    }

    public function testDotSeparation()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description.
 * This is a long description.
 * This is a continuation of the long description.
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.',
            $object->getShortDescription()
        );
        $this->assertEquals(
            "This is a long description.\nThis is a continuation of the long "
            ."description.",
            $object->getLongDescription()->getContents()
        );
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock::parseTags
     * @expectedException \LogicException
     * 
     * @return void
     */
    public function testInvalidTagBlock()
    {
        if (0 == ini_get('allow_url_include')) {
            $this->markTestSkipped('"data" URIs for includes are required.');
        }

        /** @noinspection PhpIncludeInspection */
        include 'data:text/plain;base64,'. base64_encode(
            <<<DOCBLOCK_EXTENSION
<?php
class MyReflectionDocBlock extends \phpDocumentor\Reflection\DocBlock {
    protected function splitDocBlock(\$comment) {
        return array('', '', '', 'Invalid tag block');
    }
}
DOCBLOCK_EXTENSION
        );
        /** @noinspection PhpUndefinedClassInspection */
        new \MyReflectionDocBlock('');
        
    }

    public function testTagCaseSensitivity()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description.
 *
 * This is a long description.
 *
 * @method null something()
 * @Method({"GET", "POST"})
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.',
            $object->getShortDescription()
        );
        $this->assertEquals(
            'This is a long description.',
            $object->getLongDescription()->getContents()
        );
        $tags = $object->getTags();
        $this->assertCount(2, $tags);
        $this->assertTrue($object->hasTag('method'));
        $this->assertTrue($object->hasTag('Method'));
        $this->assertInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag\MethodTag',
            $tags[0]
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag',
            $tags[1]
        );
        $this->assertNotInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag\MethodTag',
            $tags[1]
        );
    }

    /**
     * @depends testConstructFromReflector
     * @covers \phpDocumentor\Reflection\DocBlock::getTagsByName
     * 
     * @return void
     */
    public function testGetTagsByNameZeroAndOneMatch()
    {
        $object = new DocBlock(new \ReflectionClass($this));
        $this->assertEmpty($object->getTagsByName('category'));
        $this->assertCount(1, $object->getTagsByName('author'));
    }

    /**
     * @depends testConstructWithTagsOnly
     * @covers \phpDocumentor\Reflection\DocBlock::parseTags
     * 
     * @return void
     */
    public function testParseMultilineTag()
    {
        $fixture = <<<DOCBLOCK
/**
 * @return void Content on
 *     multiple lines.
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertCount(1, $object->getTags());
    }

    /**
     * @depends testConstructWithTagsOnly
     * @covers \phpDocumentor\Reflection\DocBlock::parseTags
     * 
     * @return void
     */
    public function testParseMultilineTagWithLineBreaks()
    {
        $fixture = <<<DOCBLOCK
/**
 * @return void Content on
 *     multiple lines.
 *
 *     One more, after the break.
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertCount(1, $tags = $object->getTags());
        /** @var ReturnTag $tag */
        $tag = reset($tags);
        $this->assertEquals("Content on\n    multiple lines.\n\n    One more, after the break.", $tag->getDescription());
    }

    /**
     * @depends testConstructWithTagsOnly
     * @covers \phpDocumentor\Reflection\DocBlock::getTagsByName
     * 
     * @return void
     */
    public function testGetTagsByNameMultipleMatch()
    {
        $fixture = <<<DOCBLOCK
/**
 * @param string
 * @param int
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEmpty($object->getTagsByName('category'));
        $this->assertCount(1, $object->getTagsByName('return'));
        $this->assertCount(2, $object->getTagsByName('param'));
    }

    /**
     * @param \Reflector $reflector
     * @param array $aliases
     * @param array $expectations
     * @dataProvider dataForNormalizeReturnTags
     */
    public function testNormalizeReturnTags(\Reflector $reflector, array $aliases = array(), array $expectations = array())
    {
        if (method_exists($reflector, 'getDeclaringClass')) {
            $ns = $reflector->getDeclaringClass()->getNamespaceName();
        } elseif (method_exists($reflector, 'getNamespaceName')) {
            $ns = $reflector->getNamespaceName();
        } else {
            $ns = '';
        }

        $phpDoc = new DocBlock($reflector, new Context($ns, $aliases));

        foreach ($phpDoc->getTags() as $tag) {
            if ($tag instanceof ReturnTag) {

                $type = preg_replace('/(^|\|)Closure(\||$)/', '\Closure', $tag->getType(false));
                $tag->setType($type);

                $tag->setType($tag->getType());
            }
        }

        $result = (new Serializer)->getDocComment($phpDoc);

        foreach ($expectations as $expected) {
            $this->assertContains($expected, $result);
        }
    }

    public function dataForNormalizeReturnTags()
    {
        return array(
            array(
                new \ReflectionProperty(DocBlock::class, 'long_description'),
                array(),
                array("\n * " . '@var \phpDocumentor\Reflection\DocBlock\Description ')
            ),
            array(
                new \ReflectionProperty(DocBlock::class, 'tags'),
                array('Tag' => 'phpDocumentor\Reflection\DocBlock\Tag'),
                array("\n * " . '@var \phpDocumentor\Reflection\DocBlock\Tag[] ')
            ),
            array(
                (new \ReflectionClass(DocBlock::class))->getConstructor(),
                array('Context' => 'phpDocumentor\Reflection\DocBlock\Context'),
                array(
                    "\n * " . '@param \Reflector|string ',
                    "\n * " . '@param \phpDocumentor\Reflection\DocBlock\Context ',
                    "\n * " . '@param \phpDocumentor\Reflection\Location ',
                    "\n * " . '@throws \InvalidArgumentException ',
                )
            ),
            array(
                new \ReflectionMethod(DocBlock::class, 'splitDocBlock'),
                array(),
                array("\n * " . '@return string[] ')
            ),
            array(
                new \ReflectionMethod(DocBlock::class, 'setText'),
                array(),
                array("\n * " . '@param string ', "\n * " . '@return $this ')
            ),
            array(
                new \ReflectionMethod(DocBlock::class, 'getLongDescription'),
                array(),
                array("\n * " . '@return \phpDocumentor\Reflection\DocBlock\Description ')
            ),
            //
            array(
                new \ReflectionProperty(Serializer::class, 'lineLength'),
                array(),
                array("\n * " . '@var int|null ')
            ),
            array(
                new \ReflectionMethod(Serializer::class, 'getDocComment'),
                array('DocBlock' => 'phpDocumentor\Reflection\DocBlock'),
                array("\n * " . '@param \phpDocumentor\Reflection\DocBlock ', "\n * " . '@return string ')
            ),
            array(
                new \ReflectionMethod(DocBlock\Description::class, 'getParsedContents'),
                array(),
                array("\n * " . '@return array|string[]|\phpDocumentor\Reflection\DocBlock\Tag[] ')
            ),
        );
    }

}
