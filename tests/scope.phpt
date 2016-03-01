<?php

include 'bootstrap.php';


$props = [0,1,2];
$a = 10;
$b = 3;
$parser = ConstMacro\Parser::parse("[a,b,c] = \$props");

Assert::equal(10, $a);
Assert::equal(3, $b);
Assert::false(isset($c));

$compacted = call_user_func_array('compact', $parser->compact) + array_fill_keys($parser->compact, NULL);

eval($parser->expr);

Assert::equal(0, $a);
Assert::equal(1, $b);
Assert::true(isset($c));
Assert::equal(2, $c);

extract($compacted);

Assert::equal(10, $a);
Assert::equal(3, $b);
Assert::false(isset($c));
