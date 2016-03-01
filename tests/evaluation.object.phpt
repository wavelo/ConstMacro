<?php

include 'bootstrap.php';


$props = (object) [];
Assert::equal(["a"=>NULL], evaluate("[a:]", $props));
Assert::equal(["a"=>TRUE], evaluate("[a:=true]", $props));

$props = (object) ["a"=>1];
Assert::equal(["a"=>1], evaluate("[a:]", $props));

$props = (object) ["a"=>1, "b"=>2, "c"=>3];
Assert::equal(["a"=>1, "b"=>(object)["b"=>2, "c"=>3]], evaluate("[a:, ...b]", $props));
