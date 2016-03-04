const-macro
============

Extends Latte templating engine with `{const ?}` macro for destructuring of objects and arrays inspired by ES2015

### Installation

Use composer:

```bash
$ composer require wavelo/const-macro
```

Install in configuration file given Latte\Engine:

```php
$latte = new Latte\Engine;
ConstMacro::install($latte);
```

When using Nette Dependency Injection, you can install in latte config.neon section:

```yaml
latte:
  macros:
    - ConstMacro::install
```

### Basic example

Given variable `$props = ["title"=>"Hello World", "price"=>123.0]` you can take advantage of following syntax:

```latte
{const [title:, price:, sale:=false] = $props /}
<h1>{$title}</h1>
<div>Price: {$price}</div>
<div n:if="$sale">Sale: YES</div>
```

Renders as follows:
```html
<h1>Hello World</h1>
<div>Price: 123</div>
```

### Features

 - {const ?} macros can be nested
 - const macro applies block scope for used variable
 - can be used inside {foreach ?} instead of \$value (or both destructuring and \$value)
 - every element can have arbitrary default value (even nested arrays or containing function call)
 - supports nested destructuring
 - supports ...rest operator
 - supports destructuring of both arrays and objects

For more examples see tests. Short demonstration:

```latte

{foreach $data as $key => [title:, image:[src, width, height], ...rest]}
  {* scope of variables $title, $src, etc. is bounded by foreach block! *}
{/foreach}

{foreach $data as $key => $item, [title:]}
  {* variable $item contains full object/array *}
{/foreach}

{const [title:] = $props}
  {* scope of variable $title is bounded by this block const! *}
{/const}

{const [title:] = $props /} {* does not apply block scope *}

```

### Caveats

##### Syntax
 - latte macros can't contain curly brackets, object syntax is achieved via colon character.

##### Syntax examples:

| destructuring | \$props | \$title equals |
| --- | --- | --- |
| `[title:]` | `NULL` | `RuntimeException` |
| `[title:]` | `abc` | `RuntimeException` |
| `[title:]` | `[]` | `NULL` |
| `[title:=Hi]` | `[]` | `"Hi"` |
| `[title:]` | `["title" => "Hello"]` | `"Hello"` |
| `[title=Hi]` | `[]` | `"Hi"` |
| `[title]` | `["title" => "Hello"]` | `NULL` |
| `[title]` | `["Hello"]` |`"Hello"` |
| `[title:]` | `["Hello"]` |`NULL` |


##### Priority when retrieving values of object

  1. ArrayAccess interface
  2. object properties
  3. values from Iterator or IteratorAggregate (only available when appropriate section uses rest operator)

##### Rest operator

  - rest operator is only possible as last token (i.e. [title:, image:, ...item] = $item)
  - when rest operator is used, value must be array or Traversable object
  - type of rest value corresponds to type of original value (stdClass when Traversable object, array otherwise)
  - keys are preserved when associative array is given or at least one value is accessed via key

##### Block scope
  - previously non-defined variable will have NULL value after end-of-scope (instead of calling unset function)
  - {foreach ?} has better performance then original Latte {foreach} + {const ?} inside loop
   ↳ {foreach ?} variable-scope is applied outside of foreach loop
  - unpaired {const ? /} macro does not impose variable scope

##### Default values
  - opposed to ES2016, default value can't reference previously declared tokens
  - oneword string tokens in default value do not need to be quoted (similar to Latte short-array syntax)
  - when default value contains function/method call, it is called only when required (provided value is NULL or key is not present)
  - this extension does not check full syntactic validity of default value
  - when default value with valid syntax is given, parser should handle it correctly

##### Others
  - evaluation starts from rightmost token (like PHP7 nested list)
  - requires at least PHP 5.5, fully supports PHP 7.0
  - when destructuring scalar or empty value, ConstMacro\RuntimeException is thrown (applied recursively)
  - thrown exception can be suppressed with default value (i.e. `[title:, image:[src, width, height]=[]] = $item`)

### License

MIT. See full [license](license.md).
