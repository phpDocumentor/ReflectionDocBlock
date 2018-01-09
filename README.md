[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Travis Status](https://travis-ci.org/phpDocumentor/ReflectionDocBlock.svg?branch=master)](https://travis-ci.org/phpDocumentor/ReflectionDocBlock)
[![Appveyor Status](https://ci.appveyor.com/api/projects/status/03a9euxrcse7orgu/branch/master?svg=true)](https://ci.appveyor.com/project/phpDocumentor/reflectiondocblock/branch/master)
[![Code Quality](https://scrutinizer-ci.com/g/phpDocumentor/ReflectionDocBlock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpDocumentor/ReflectionDocBlock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/phpDocumentor/ReflectionDocBlock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpDocumentor/ReflectionDocBlock/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/phpDocumentor/ReflectionDocBlock/badge.svg?branch=master)](https://coveralls.io/github/phpDocumentor/ReflectionDocBlock?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)


The ReflectionDocBlock Component
================================

Introduction
------------

The ReflectionDocBlock component of phpDocumentor provides a DocBlock parser
that is 100% compatible with the [PHPDoc standard](http://phpdoc.org/docs/latest).

With this component, a library can provide support for annotations via DocBlocks
or otherwise retrieve information that is embedded in a DocBlock.

Installation
------------

```bash
composer require phpdocumentor/reflection-docblock
```

Usage
-----

In order to parse the DocBlock one needs a DocBlockFactory that can be
instantiated using its `createInstance` factory method like this:

```php
$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
```

Then we can use the `create` method of the factory to interpret the DocBlock.
Please note that it is also possible to provide a class that has the
`getDocComment()` method, such as an object of type `ReflectionClass`, the
create method will read that if it exists.

```php
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

$docblock = $factory->create($docComment);
```

The `create` method will yield an object of type `\phpDocumentor\Reflection\DocBlock`
whose methods can be queried:

```php
// Contains the summary for this DocBlock
$summary = $docblock->getSummary();

// Contains \phpDocumentor\Reflection\DocBlock\Description object
$description = $docblock->getDescription();

// You can either cast it to string
$description = (string) $docblock->getDescription();

// Or use the render method to get a string representation of the Description.
$description = $docblock->getDescription()->render();
```

> For more examples it would be best to review the scripts in the [`/examples` folder](/examples).
