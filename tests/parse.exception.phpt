<?php

include 'bootstrap.php';


Assert::exception(function() { parse(''); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse(' = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[ = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a]] = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a[] = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a:, ...b, c] = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a:], b] = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a:=[, b] = $props'); }, 'ConstMacro\ParserException');
Assert::exception(function() { parse('[a:=foo(, b] = $props'); }, 'ConstMacro\ParserException');
