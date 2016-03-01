<?php

include 'bootstrap.php';


Assert::exception(function() { evaluate('[a]', NULL); }, 'ConstMacro\RuntimeException');
Assert::exception(function() { evaluate('[a:[b]]', ['a'=>1]); }, 'ConstMacro\RuntimeException');
Assert::exception(function() { evaluate('[...b]', new \DateTime); }, 'ConstMacro\RuntimeException');
