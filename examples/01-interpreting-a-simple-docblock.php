<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * This is a Description. A Summary and Description are separated by either
 * two subsequent newlines (thus a whiteline in between as can be seen in this
 * example), or when the Summary ends with a dot (`.`) and some form of
 * whitespace.
 */
DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

// Should contain the first line of the DocBlock
$summary = $docblock->getSummary();

// Contains an object of type Description; you can either cast it to string or use
// the render method to get a string representation of the Description.
//
// In subsequent examples we will be fiddling a bit more with the Description.
$description = $docblock->getDescription();
