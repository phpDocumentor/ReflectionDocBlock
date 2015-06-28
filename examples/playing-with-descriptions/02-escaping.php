<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * You can escape the @-sign by surrounding it with braces, for example: {@}. And escape a closing brace within an
 * inline tag by adding an opening brace in front of it like this: {}.
 *
 * Here are example texts where you can see how they could be used in a real life situation:
 *
 *     This is a text with an {@internal inline tag where a closing brace ({}) is shown}.
 *     Or an {@internal inline tag with a literal {{@}link{} in it}.
 *
 * Do note that an {@internal inline tag that has an opening brace ({) does not break out}.
 */
DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

// Escaping is automatic so this happens in the DescriptionFactory.
$description = $docblock->getDescription();

// This is the rendition that we will receive of the Description.
$receivedDocComment = <<<DOCCOMMENT
/**
 * This is an example of a summary.
 *
 * You can escape the @-sign by surrounding it with braces, for example: {@}. And escape a closing brace within an
 * inline tag by adding an opening brace in front of it like this: {}.
 *
 * Here are example texts where you can see how they could be used in a real life situation:
 *
 *     This is a text with an {@internal inline tag where a closing brace ({}) is shown}.
 *     Or an {@internal inline tag with a literal {{@}link{} in it}.
 *
 * Do note that an {@internal inline tag that has an opening brace ({) does not break out}.
 */
DOCCOMMENT;

// Render it using the default PassthroughFormatter
$foundDescription = $description->render();
