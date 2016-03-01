<?php

include 'bootstrap.php';


Assert::equal(['true', ", ...]"], parseValue("true, ...]"));
Assert::equal(['1', "]"], parseValue("1]"));
Assert::equal(['"abc"', ", ...]"], parseValue("abc, ...]"));
Assert::equal(['[]', ", ...]"], parseValue("[], ...]"));
Assert::equal(['[] + []', ", ...]"], parseValue("[] + [], ...]"));
Assert::equal(['["x"=>123]', ", ...]"], parseValue("[x=>123], ...]"));
Assert::equal(['["a","b",["c"]]', ", ...]"], parseValue("[a,b,[c]], ...]"));
Assert::equal(['new ConstMacro\Lazy("foo")', ", ...]"], parseValue("foo(), ...]"));
Assert::equal(['new ConstMacro\Lazy("foo", 1, 2)', ", ...]"], parseValue("foo(1, 2), ...]"));
Assert::equal(['new ConstMacro\Lazy([$me, "foo"], 1, 2)', ", ...]"], parseValue("\$me->foo(1, 2), ...]"));
Assert::equal(['[new ConstMacro\Lazy("foo", "x", 123, new ConstMacro\Lazy("boo", [1]))]', ", ...]"], parseValue("[foo(x, 123, boo([1]))], ...]"));
