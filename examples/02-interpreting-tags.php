<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * @see \phpDocumentor\Reflection\DocBlock\StandardTagFactory
 */
DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

// You can check if a DocBlock has one or more see tags
$hasSeeTag = $docblock->hasTag('see');

// Or we can get a complete list of all tags
$tags = $docblock->getTags();

// But we can also grab all tags of a specific type, such as `see`
$seeTags = $docblock->getTagsByName('see');
