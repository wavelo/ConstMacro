<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .
if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

// configure environment
Tester\Environment::setup();
class_alias('Tester\Assert', 'Assert');
date_default_timezone_set('Europe/Prague');


$now = microtime(TRUE);
$times = 0;
function now() {
	global $now, $times;
	++$times;
	return $now;
}


function nowTimes() {
	global $times;
	$ret = $times;
	$times = 0;
	return $ret;
}


function parse($str) {
	return ConstMacro\Parser::parse($str);
}


function parseValue($str) {
	$parser = ConstMacro\ParserValue::parse($str);
	return [$parser->value, $parser->input];
}


function evaluate($__expr, $__props, array $__defined=[]) {
	extract($__defined);
	eval(ConstMacro\Parser::parse("$__expr = \$__props")->expr);

	foreach (array_keys($__defined) as $__key) unset($$__key);
	unset($__defined, $__key);
	unset($__expr, $__props);
	return get_defined_vars();
}


function assertTemplate($name, array $props=[])
{
	$latte = new Latte\Engine;
	ConstMacro::install($latte->getCompiler());

	$file = __DIR__ . "/templates/$name.expected.html";
	$actual = $latte->renderToString(__DIR__ . "/templates/$name.latte", ['props' => $props]);

	$pattern = @file_get_contents($file);

	if ($pattern === FALSE) {
		throw new \Exception("Unable to read file '$file'.");
	}

	Tester\Assert::match(
		trim(preg_replace("#^[\t\s]+#m", "", $pattern)),
		trim(preg_replace("#^[\t\s]+#m", "", $actual))
	);
}
