<?php

include 'bootstrap.php';

list($a, $a) = [0, 0];

Assert::equal(["a"=>NULL], evaluate("[a]", []));
Assert::equal(["a"=>true], evaluate("[a=true]", []));
Assert::equal(["a"=>false], evaluate("[a=true]", [false]));

Assert::equal(["a"=>NULL], evaluate("[a:]", []));
Assert::equal(["a"=>true], evaluate("[a:=true]", []));
Assert::equal(["a"=>true], evaluate("[a:=true]", [false]));
Assert::equal(["a"=>false], evaluate("[a:=true]", ["a"=>false]));
Assert::equal(["b"=>false], evaluate("[a:b=true]", ["a"=>false]));

Assert::equal(["a"=>0, "b"=>1, "c"=>2], evaluate("[a,b,c]", [0,1,2]));
Assert::equal(["a"=>NULL, "b"=>1, "c"=>2], evaluate("[[a,b]=[],b,c]", [NULL,1,2]));
