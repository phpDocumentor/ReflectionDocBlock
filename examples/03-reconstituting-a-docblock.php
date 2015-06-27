<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * And here is an example of the description
 * of a DocBlock that can span multiple lines.
 *
 * @see \phpDocumentor\Reflection\DocBlock\StandardTagFactory
 */
DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

$serializer              = new Serializer();
$reconstitutedDocComment = $serializer->getDocComment($docblock);

